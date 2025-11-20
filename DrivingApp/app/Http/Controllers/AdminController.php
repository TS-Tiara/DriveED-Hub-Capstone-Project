<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\InstructorRemovalRequest;
use App\Models\Log;
use App\Models\RegistrationRequest;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ==========================
    // DASHBOARD
    // ==========================
    public function dashboard(School $school)
    {
        try {
            // Get counts and statistics
            $totalStudents = Student::where('school_id', $school->id)->count();
            $activeStudents = Student::where('school_id', $school->id)->where('status', 'active')->count();
            $inactiveStudents = $totalStudents - $activeStudents;
            
            $totalInstructors = Instructor::where('school_id', $school->id)->count();
            $activeInstructors = Instructor::where('school_id', $school->id)->where('status', 'active')->count();
            $availableInstructors = Instructor::where('school_id', $school->id)
                ->where('status', 'active')
                ->where('availability', 'available')
                ->count();
            
            // Get recent activities (last 5)
            $recentStudents = Student::where('school_id', $school->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
            $recentInstructors = Instructor::where('school_id', $school->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Get today's date for filtering
            $today = Carbon::today();
            
            // Calculate enrollment trend (last 30 days)
            $enrollmentData = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $count = Student::where('school_id', $school->id)
                    ->whereDate('created_at', $date)
                    ->count();
                $enrollmentData[] = [
                    'date' => $date->format('M d'),
                    'count' => $count
                ];
            }
            
            return view($school->resolveView('admin.dashboard'), [
                'school' => $school,
                'totalStudents' => $totalStudents,
                'activeStudents' => $activeStudents,
                'inactiveStudents' => $inactiveStudents,
                'totalInstructors' => $totalInstructors,
                'activeInstructors' => $activeInstructors,
                'availableInstructors' => $availableInstructors,
                'recentStudents' => $recentStudents,
                'recentInstructors' => $recentInstructors,
                'enrollmentData' => $enrollmentData,
            ]);
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load admin dashboard',
                'database',
                $e,
                ['school_id' => $school->id],
                $school->id,
                'view_dashboard'
            );
            
            return back()->with('error', 'Unable to load dashboard. The system administrator has been notified.');
        }
    }

    // ==========================
    // USER MANAGEMENT
    // ==========================
    public function userManagement(School $school)
    {
        try {
            $students = Student::where('school_id', $school->id)->orderBy('name')->get();
            $instructors = Instructor::where('school_id', $school->id)->orderBy('name')->get();

            $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

            return view($school->resolveView('admin.user-management'), [
                'school' => $school,
                'students' => $students,
                'instructors' => $instructors,
                'isAjax' => $isAjax,
            ]);
        } catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load user management page',
                'database',
                $e,
                ['school_id' => $school->id],
                $school->id,
                'view_user_management'
            );
            
            return back()->with('error', 'Unable to load user management. The system administrator has been notified.');
        }
    }

    // ==========================
    // CREATE ACCOUNT
    // ==========================
    public function storeAccount(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->where('school_id', $school->id),
                Rule::unique('instructors', 'email')->where('school_id', $school->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'password' => 'required|string|min:6',
            'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'role' => 'required|in:student,instructor',
        ]);

        $data = [
            'school_id' => $school->id,
            'name' => trim($request->name),
            'email' => trim($request->email),
            'contact' => trim((string) $request->contact),
            'password' => Hash::make($request->password),
        ];

        if ($request->role === 'student') {
            Student::create(array_merge($data, [
                'address' => $request->address ?? null,
                'status' => 'active',
            ]));
            $successMessage = 'Student created successfully!';
        } else {
            Instructor::create(array_merge($data, [
                'license_number' => $request->license_number ?? null,
                'status' => 'active',
                'availability' => 'available',
            ]));
            $successMessage = 'Instructor created successfully!';
        }

        // Redirect back to the referring page or default to create account
        $referrer = request()->headers->get('referer');
        if ($referrer && str_contains($referrer, 'user-management')) {
            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', $successMessage);
        }

        return redirect()
            ->route('schools.admin.createAccount', $school)
            ->with('success', $successMessage);
    }

    // ==========================
    // STUDENTS MANAGEMENT
    // ==========================
    public function editStudent(School $school, $id)
    {
        $student = Student::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        return view($school->resolveView('admin.edit-student'), [
            'school' => $school,
            'student' => $student,
        ]);
    }

    public function updateStudent(Request $request, School $school, $id)
    {
        try {
            $student = Student::where('school_id', $school->id)
                ->where('id', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('students', 'email')
                        ->where('school_id', $school->id)
                        ->ignore($student->id),
                    'regex:/@(gmail\.com|yahoo\.com)$/i',
                ],
                'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
                'address' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:6',
            ]);

            DB::beginTransaction();

            $data = $request->only('name', 'email', 'contact', 'address');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $student->update($data);

            DB::commit();

            SystemLog::logInfo(
                "Student profile updated: {$student->name}",
                'database',
                ['student_id' => $student->id, 'updated_fields' => array_keys($data)],
                $school->id,
                'update_student'
            );

            // Redirect back to the referring page or default to students
            $referrer = request()->headers->get('referer');
            if ($referrer && str_contains($referrer, 'user-management')) {
                return redirect()->route('schools.admin.userManagement', $school)
                    ->with('success', 'Student updated successfully!');
            }
            
            return redirect()->route('schools.admin.students', $school)
                ->with('success', 'Student updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            
            SystemLog::logError(
                'Failed to update student profile',
                'database',
                $e,
                ['student_id' => $id, 'school_id' => $school->id],
                $school->id,
                'update_student'
            );

            return back()->withInput()->with('error', 'Failed to update student. The system administrator has been notified.');
        }
    }

    public function toggleStudentStatus(School $school, $id)
    {
        $student = Student::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $student->status = $student->status === 'active' ? 'inactive' : 'active';
        $student->save();

        // Redirect back to the referring page or default to students
        $referrer = request()->headers->get('referer');
        if ($referrer && str_contains($referrer, 'user-management')) {
            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Student status updated successfully!');
        }

        return redirect()->route('schools.admin.students', $school)
            ->with('success', 'Student status updated successfully!');
    }

    // ==========================
    // INSTRUCTORS MANAGEMENT
    // ==========================
    public function editInstructor(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        return view($school->resolveView('admin.edit-instructor'), [
            'school' => $school,
            'instructor' => $instructor,
        ]);
    }

    public function updateInstructor(Request $request, School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('instructors', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($instructor->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'license_number' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'email', 'contact', 'license_number');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $instructor->update($data);

        // Redirect back to the referring page or default to instructors
        $referrer = request()->headers->get('referer');
        if ($referrer && str_contains($referrer, 'user-management')) {
            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Instructor updated successfully!');
        }

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor updated successfully!');
    }

    public function toggleInstructorStatus(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $instructor->status = $instructor->status === 'active' ? 'inactive' : 'active';
        $instructor->save();

        // Redirect back to the referring page or default to instructors
        $referrer = request()->headers->get('referer');
        if ($referrer && str_contains($referrer, 'user-management')) {
            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Instructor status updated successfully!');
        }

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor status updated successfully!');
    }

    public function toggleAvailability(School $school, $id)
    {
        $instructor = Instructor::where('school_id', $school->id)
            ->where('id', $id)
            ->firstOrFail();

        $instructor->availability = $instructor->availability === 'available' ? 'unavailable' : 'available';
        $instructor->save();

        // Redirect back to the referring page or default to instructors
        $referrer = request()->headers->get('referer');
        if ($referrer && str_contains($referrer, 'user-management')) {
            return redirect()->route('schools.admin.userManagement', $school)
                ->with('success', 'Instructor availability updated successfully!');
        }

        return redirect()->route('schools.admin.instructors', $school)
            ->with('success', 'Instructor availability updated successfully!');
    }

    // ==========================
    // REPORTS & PROFILE
    // ==========================
    public function studentReports(School $school)
    {
        return view($school->resolveView('admin.reports.students'), [
            'school' => $school,
        ]);
    }

    public function instructorReports(School $school)
    {
        return view($school->resolveView('admin.reports.instructors'), [
            'school' => $school,
        ]);
    }

    public function logs(School $school)
    {
        return view($school->resolveView('admin.reports.logs'), [
            'school' => $school,
        ]);
    }

    public function profile(School $school)
    {
        $admin = Auth::guard('admin')->user();
        
        return view($school->resolveView('admin.profile'), [
            'school' => $school,
            'admin' => $admin,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($admin->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'current_password' => 'nullable|string|min:6',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'contact']);

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }
        //False positive
        $admin->update($data);

        return redirect()
            ->route('schools.admin.profile', $school)
            ->with('success', 'Profile updated successfully!');
    }

    public function updateProfilePicture(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'profile_picture' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // Delete old profile picture if exists
        if ($admin->profile_picture) {
            Storage::disk('public')->delete($admin->profile_picture);
        }

        // Store new profile picture
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $admin->update([
            'profile_picture' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'path' => $path,
        ]);
    }

    // ==========================
    // INSTRUCTOR REMOVAL REQUESTS
    // ==========================
    
    public function removalRequests(School $school)
    {
        $admin = Auth::guard('admin')->user();
        
        abort_unless($admin && $admin->school_id === $school->id, 403);

        // Get all removal requests for this school
        $pendingRequests = InstructorRemovalRequest::with(['instructor', 'timeSlot'])
            ->where('school_id', $school->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $processedRequests = InstructorRemovalRequest::with(['instructor', 'timeSlot', 'processedBy'])
            ->where('school_id', $school->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('processed_at', 'desc')
            ->limit(20)
            ->get();

        return view($school->resolveView('admin.removal-requests'), [
            'school' => $school,
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests,
        ]);
    }

    public function approveRemovalRequest(Request $request, School $school, $id)
    {
        $admin = Auth::guard('admin')->user();
        
        abort_unless($admin && $admin->school_id === $school->id, 403);

        $removalRequest = InstructorRemovalRequest::where('school_id', $school->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        // Start transaction
        DB::beginTransaction();
        try {
            // Update the removal request FIRST (before deleting the schedule_instructors record)
            $removalRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            // Then remove instructor from the time slot
            DB::table('schedule_instructors')
                ->where('id', $removalRequest->schedule_instructor_id)
                ->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Removal request approved. Instructor has been removed from the time slot.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve removal request: ' . $e->getMessage());
        }
    }

    public function rejectRemovalRequest(Request $request, School $school, $id)
    {
        $admin = Auth::guard('admin')->user();
        
        abort_unless($admin && $admin->school_id === $school->id, 403);

        $removalRequest = InstructorRemovalRequest::where('school_id', $school->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        // Start transaction
        DB::beginTransaction();
        try {
            // Update the schedule_instructors to remove the pending flag
            DB::table('schedule_instructors')
                ->where('id', $removalRequest->schedule_instructor_id)
                ->update(['has_pending_removal_request' => false]);

            // Update the removal request
            $removalRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Removal request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reject removal request: ' . $e->getMessage());
        }
    }

    // ==========================
    // SCHOOL SETTINGS
    // ==========================
    public function settings(School $school)
    {
        $admin = Auth::guard('admin')->user();
        
        abort_unless($admin && $admin->school_id === $school->id, 403);

        return view($school->resolveView('admin.settings'), [
            'school' => $school,
        ]);
    }

    public function updateSettings(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            abort_unless($admin && $admin->school_id === $school->id, 403);

            $request->validate([
                'instructor_removal_notice_days' => 'required|integer|min:0|max:30',
                'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'sidebar_bg_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'sidebar_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'sidebar_hover_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'use_gradient_header' => 'nullable|boolean',
                'button_primary_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_primary_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_secondary_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_secondary_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_success_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_success_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_danger_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'button_danger_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'border_radius' => 'nullable|integer|min:0|max:30',
                'button_border_radius' => 'nullable|integer|min:0|max:30',
                'modal_header_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'modal_header_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'modal_border_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'card_border_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'card_header_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'page_header_border' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_pending_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_pending_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_approved_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_approved_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_cancelled_bg' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
                'badge_cancelled_text' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            ], [
                'instructor_removal_notice_days.required' => 'Minimum notice period is required.',
                'instructor_removal_notice_days.integer' => 'Notice period must be a number.',
                'instructor_removal_notice_days.min' => 'Notice period cannot be negative.',
                'instructor_removal_notice_days.max' => 'Notice period cannot exceed 30 days.',
                '*.regex' => 'Invalid color format. Please use hex color codes (e.g., #667eea).',
                'border_radius.min' => 'Border radius must be at least 0.',
                'border_radius.max' => 'Border radius cannot exceed 30 pixels.',
                'button_border_radius.min' => 'Button border radius must be at least 0.',
                'button_border_radius.max' => 'Button border radius cannot exceed 30 pixels.',
            ]);

            DB::beginTransaction();

            // Update school settings
            $school->update([
                'instructor_removal_notice_days' => $request->instructor_removal_notice_days,
            ]);

            // Update or create color settings
            $schoolSetting = $school->schoolSetting;
            
            if (!$schoolSetting) {
                $schoolSetting = new SchoolSetting(['school_id' => $school->id]);
            }

            $schoolSetting->fill([
                'primary_color' => $request->primary_color ?? '#2563eb',
                'secondary_color' => $request->secondary_color ?? '#fbbf24',
                'accent_color' => $request->accent_color ?? '#1e40af',
                'sidebar_bg_color' => $request->sidebar_bg_color ?? '#ffffff',
                'sidebar_text_color' => $request->sidebar_text_color ?? '#333333',
                'sidebar_hover_color' => $request->sidebar_hover_color ?? '#f5f5f5',
                'use_gradient_header' => $request->has('use_gradient_header'),
                'button_primary_bg' => $request->button_primary_bg ?? '#667eea',
                'button_primary_text' => $request->button_primary_text ?? '#ffffff',
                'button_secondary_bg' => $request->button_secondary_bg ?? '#6c757d',
                'button_secondary_text' => $request->button_secondary_text ?? '#ffffff',
                'button_success_bg' => $request->button_success_bg ?? '#28a745',
                'button_success_text' => $request->button_success_text ?? '#ffffff',
                'button_danger_bg' => $request->button_danger_bg ?? '#dc3545',
                'button_danger_text' => $request->button_danger_text ?? '#ffffff',
                'border_radius' => $request->border_radius ?? 8,
                'button_border_radius' => $request->button_border_radius ?? 8,
                'modal_header_bg' => $request->modal_header_bg ?? '#667eea',
                'modal_header_text' => $request->modal_header_text ?? '#ffffff',
                'modal_border_color' => $request->modal_border_color ?? '#667eea',
                'card_border_color' => $request->card_border_color ?? '#e5e7eb',
                'card_header_bg' => $request->card_header_bg ?? '#f9fafb',
                'page_header_border' => $request->page_header_border ?? '#667eea',
                'badge_pending_bg' => $request->badge_pending_bg ?? '#fbbf24',
                'badge_pending_text' => $request->badge_pending_text ?? '#78350f',
                'badge_approved_bg' => $request->badge_approved_bg ?? '#10b981',
                'badge_approved_text' => $request->badge_approved_text ?? '#065f46',
                'badge_cancelled_bg' => $request->badge_cancelled_bg ?? '#ef4444',
                'badge_cancelled_text' => $request->badge_cancelled_text ?? '#7f1d1d',
            ]);

            $schoolSetting->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Settings updated successfully. Refresh the page to see changes.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Settings update failed: ' . $e->getMessage(), [
                'school_id' => $school->id,
                'admin_id' => Auth::guard('admin')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings. Please try again or contact support if the problem persists.');
        }
    }

    // ==========================
    // TIMESLOT MANAGEMENT
    // ==========================

    /**
     * Show schedule management page
     */
    public function schedules(Request $request, School $school)
    {
        $timeslots = TimeSlot::with(['instructors', 'course'])
            ->where('school_id', $school->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('date');
        
        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->get();
        
        $courses = \App\Models\Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('title')
            ->get();
        
        return view($school->resolveView('admin.schedules'), compact('school', 'timeslots', 'instructors', 'courses'));
    }

    /**
     * Create schedule with optional instructor pre-assignment
     * Admin can create schedule and optionally assign instructors immediately
     * Remaining spots will be available for instructor self-selection
     */
    public function createSchedule(Request $request, School $school)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_instructors' => 'required|integer|min:1|max:10',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Verify course belongs to this school
        $course = \App\Models\Course::where('id', $validated['course_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();
        
        // Create the timeslot
        $timeslot = TimeSlot::create([
            'school_id' => $school->id,
            'course_id' => $validated['course_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'open',
            'max_instructors' => $validated['max_instructors'],
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // If admin selected instructors to assign
        if (!empty($validated['instructor_ids'])) {
            // Verify all instructors belong to this school
            $instructors = Instructor::whereIn('id', $validated['instructor_ids'])
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->get();
            
            if ($instructors->count() !== count($validated['instructor_ids'])) {
                $timeslot->delete();
                return redirect()->back()->with('error', 'Some instructors are invalid or inactive.');
            }
            
            // Check if not exceeding max capacity
            if ($instructors->count() > $validated['max_instructors']) {
                $timeslot->delete();
                return redirect()->back()->with('error', 'Cannot assign more instructors than max capacity.');
            }
            
            // Assign instructors with admin_assigned type
            foreach ($instructors as $instructor) {
                $timeslot->instructors()->attach($instructor->id, [
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }
            
            $availableSpots = $validated['max_instructors'] - $instructors->count();
            $message = 'Schedule created with ' . $instructors->count() . ' instructor(s) assigned.';
            
            if ($availableSpots > 0) {
                $message .= ' ' . $availableSpots . ' spot(s) available for self-selection.';
            }
            
            return redirect()->back()->with('success', $message);
        }
        
        // No instructors assigned - all spots available
        return redirect()->back()->with('success', 'Schedule created! ' . $validated['max_instructors'] . ' spot(s) available for instructor self-selection.');
    }

    /**
     * Update schedule
     */
    public function updateSchedule(Request $request, School $school, $id)
    {
        $timeslot = TimeSlot::where('school_id', $school->id)->findOrFail($id);
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'instructor_ids' => 'nullable|array',
            'instructor_ids.*' => 'exists:instructors,id',
        ]);
        
        // Update notes
        $timeslot->update([
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Update admin-assigned instructors only (preserve self-selected ones)
        if (isset($validated['instructor_ids'])) {
            // Get current self-selected instructors
            $selfSelected = $timeslot->instructors()
                ->wherePivot('assignment_type', 'self_selected')
                ->pluck('instructors.id')
                ->toArray();
            
            // Validate new admin assignments don't exceed capacity
            $totalInstructors = count($selfSelected) + count($validated['instructor_ids']);
            if ($totalInstructors > $timeslot->max_instructors) {
                $selfSelectedCount = count($selfSelected);
                return redirect()->back()
                    ->withErrors(['instructor_ids' => "Cannot assign {$totalInstructors} instructors. Maximum capacity is {$timeslot->max_instructors}. Currently {$selfSelectedCount} instructors are self-selected."])
                    ->withInput();
            }
            
            // Validate instructors belong to school and are active
            $instructors = Instructor::where('school_id', $school->id)
                ->where('status', 'active')
                ->whereIn('id', $validated['instructor_ids'])
                ->get();
            
            if ($instructors->count() !== count($validated['instructor_ids'])) {
                return redirect()->back()
                    ->withErrors(['instructor_ids' => 'One or more selected instructors are invalid or inactive.'])
                    ->withInput();
            }
            
            // Remove all admin-assigned instructors
            $timeslot->instructors()
                ->wherePivot('assignment_type', 'admin_assigned')
                ->detach();
            
            // Add new admin-assigned instructors
            foreach ($instructors as $instructor) {
                $timeslot->instructors()->attach($instructor->id, [
                    'school_id' => $school->id,
                    'assignment_type' => 'admin_assigned',
                ]);
            }
        }
        
        $adminCount = $timeslot->getAdminAssignedCount();
        $selfCount = $timeslot->getSelfSelectedCount();
        
        return redirect()->back()->with('success', "Schedule updated successfully! Instructors: {$adminCount} admin-assigned, {$selfCount} self-selected.");
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule(School $school, $id)
    {
        $timeslot = TimeSlot::where('school_id', $school->id)->findOrFail($id);
        
        // Detach instructors and delete
        $timeslot->instructors()->detach();
        $timeslot->delete();
        
        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }

    // ==========================
    // COURSE MANAGEMENT
    // ==========================

    /**
     * Show courses management page
     */
    public function courses(School $school)
    {
        $courses = \App\Models\Course::with('packages')
            ->where('school_id', $school->id)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view($school->resolveView('admin.courses'), compact('school', 'courses'));
    }

    /**
     * Store a new course
     */
    public function storeCourse(Request $request, School $school)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|string',
            'vehicle_type' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
        ]);

        $validated['school_id'] = $school->id;
        $validated['is_featured'] = $request->has('is_featured');

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $image = $request->file('banner_image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/courses'), $filename);
            $validated['banner_image'] = 'images/courses/' . $filename;
        }

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features']);
        }

        $course = \App\Models\Course::create($validated);

        return redirect()->back()->with('success', 'Course created successfully!');
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Request $request, School $school, $id)
    {
        $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|string',
            'vehicle_type' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
        ]);

        $validated['is_featured'] = $request->has('is_featured');

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image if exists
            if ($course->banner_image && file_exists(public_path($course->banner_image))) {
                unlink(public_path($course->banner_image));
            }

            $image = $request->file('banner_image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/courses'), $filename);
            $validated['banner_image'] = 'images/courses/' . $filename;
        }

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features']);
        }

        $course->update($validated);

        return redirect()->back()->with('success', 'Course updated successfully!');
    }

    /**
     * Delete a course
     */
    public function deleteCourse(School $school, $id)
    {
        $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($id);

        // Delete banner image if exists
        if ($course->banner_image && file_exists(public_path($course->banner_image))) {
            unlink(public_path($course->banner_image));
        }

        $course->delete();

        return redirect()->back()->with('success', 'Course deleted successfully!');
    }

    /**
     * Store a course package
     */
    public function storePackage(Request $request, School $school, $courseId)
    {
        $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'transmission_type' => 'required|in:manual,automatic',
            'price' => 'required|numeric|min:0',
            'training_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
        ]);

        $validated['course_id'] = $course->id;
        $validated['is_popular'] = $request->has('is_popular');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features']);
        }

        \App\Models\CoursePackage::create($validated);

        return redirect()->back()->with('success', 'Package added successfully!');
    }

    /**
     * Update a course package
     */
    public function updatePackage(Request $request, School $school, $courseId, $packageId)
    {
        $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);
        $package = \App\Models\CoursePackage::where('course_id', $course->id)->findOrFail($packageId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'transmission_type' => 'required|in:manual,automatic',
            'price' => 'required|numeric|min:0',
            'training_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string',
        ]);

        $validated['is_popular'] = $request->has('is_popular');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_filter($validated['features']);
        }

        $package->update($validated);

        return redirect()->back()->with('success', 'Package updated successfully!');
    }

    /**
     * Delete a course package
     */
    public function deletePackage(School $school, $courseId, $packageId)
    {
        $course = \App\Models\Course::where('school_id', $school->id)->findOrFail($courseId);
        $package = \App\Models\CoursePackage::where('course_id', $course->id)->findOrFail($packageId);

        $package->delete();

        return redirect()->back()->with('success', 'Package deleted successfully!');
    }

    // ============================================
    // SYSTEM ADMIN ONLY METHODS
    // ============================================

    /**
     * System logs - track all admin actions
     * Accessible only by system_admin role
     */
    public function systemLogs(School $school)
    {
        // TODO: Implement comprehensive logging system
        // This will track: logins, logouts, account deactivations, 
        // activations, creations, deletions, etc.
        
        $logs = \App\Models\Log::where('school_id', $school->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view($school->resolveView('admin.system-logs'), compact('school', 'logs'));
    }

    /**
     * System monitoring - uptime, performance metrics
     * Accessible only by system_admin role
     */
    public function systemMonitoring(School $school)
    {
        // TODO: Implement system monitoring
        // Track: uptime, server status, database performance,
        // active users, system health checks
        
        $metrics = [
            'uptime' => 'Operational',
            'active_users' => \App\Models\Admin::where('school_id', $school->id)->count() +
                            \App\Models\Instructor::where('school_id', $school->id)->where('status', 'active')->count() +
                            \App\Models\Student::where('school_id', $school->id)->where('status', 'active')->count(),
            'database_size' => 'N/A',
            'last_backup' => 'N/A',
        ];

        return view($school->resolveView('admin.system-monitoring'), compact('school', 'metrics'));
    }

    /**
     * Permanently delete a student (System Admin only)
     * School admins can only deactivate
     */
    public function deleteStudent(School $school, $id)
    {
        $student = \App\Models\Student::where('school_id', $school->id)->findOrFail($id);
        
        // Log the deletion
        \App\Models\Log::create([
            'school_id' => $school->id,
            'admin_id' => auth()->guard('admin')->id(),
            'action' => 'deleted_student',
            'description' => "Permanently deleted student: {$student->name} (ID: {$student->id})",
            'ip_address' => request()->ip(),
        ]);

        $student->delete();

        return redirect()->back()->with('success', 'Student permanently deleted.');
    }

    /**
     * Permanently delete an instructor (System Admin only)
     * School admins can only deactivate
     */
    public function deleteInstructor(School $school, $id)
    {
        $instructor = \App\Models\Instructor::where('school_id', $school->id)->findOrFail($id);
        
        // Log the deletion
        \App\Models\Log::create([
            'school_id' => $school->id,
            'admin_id' => auth()->guard('admin')->id(),
            'action' => 'deleted_instructor',
            'description' => "Permanently deleted instructor: {$instructor->name} (ID: {$instructor->id})",
            'ip_address' => request()->ip(),
        ]);

        $instructor->delete();

        return redirect()->back()->with('success', 'Instructor permanently deleted.');
    }
}