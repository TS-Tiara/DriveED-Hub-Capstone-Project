<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function profile(School $school)
    {
        return view($school->resolveView('student.profile'), [
            'school' => $school,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')
                    ->where('school_id', $school->id)
                    ->ignore($student->id),
                'regex:/@(gmail\.com|yahoo\.com)$/i',
            ],
            'contact' => ['nullable', 'string', 'max:20', 'regex:/^(09\d{9}|\+639\d{9})$/'],
            'address' => 'nullable|string|max:255',
            'current_password' => 'nullable|string|min:6',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'contact', 'address']);

        // Check current password if user wants to change password
        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }
        //False positive
        $student->update($data);

        return redirect()
            ->route('schools.student.profile', $school)
            ->with('success', 'Profile updated successfully!');
    }
}
