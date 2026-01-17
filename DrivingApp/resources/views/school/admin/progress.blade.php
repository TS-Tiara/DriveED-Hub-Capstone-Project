@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Progress')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
.progress-container {
    padding: 20px;
    margin: 20px auto;
    max-width: 1600px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 3px solid {{ $primaryColor }};
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    font-size: 0.9rem;
    margin-top: 5px;
}

/* Table Styles */
.progress-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.progress-table table {
    width: 100%;
    border-collapse: collapse;
}

.progress-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.progress-table th {
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress-row {
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background 0.2s;
}

.progress-row:hover {
    background: #f9fafb;
}

.progress-row td {
    padding: 18px 20px;
    font-size: 0.95rem;
    color: #374151;
}

.student-name {
    font-weight: 600;
    color: #111827;
}

.session-info {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    background: #dbeafe;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e40af;
}

.instructor-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #f3e8ff;
    border-radius: 16px;
    font-size: 0.85rem;
    color: #6b21a8;
}

.schedule-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #fef3c7;
    border-radius: 16px;
    font-size: 0.85rem;
    color: #92400e;
}

.expand-icon {
    display: inline-block;
    transition: transform 0.3s;
    color: #667eea;
    font-weight: bold;
    font-size: 1.2rem;
}

.progress-row.expanded .expand-icon {
    transform: rotate(90deg);
}

/* Detailed View (Expandable) */
.detail-row {
    display: none;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.detail-row.show {
    display: table-row;
}

.detail-content {
    padding: 30px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.detail-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.detail-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #667eea;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed #e5e7eb;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 600;
}

.detail-value {
    font-size: 0.9rem;
    color: #111827;
    font-weight: 500;
}

.progress-bar-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    grid-column: 1 / -1;
}

.progress-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
}

.progress-bar-container {
    flex: 1;
    height: 35px;
    background: #e5e7eb;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
}

.progress-percentage {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    min-width: 70px;
    text-align: right;
}

.badge {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-completed { background: #d1fae5; color: #065f46; }
.badge-in-progress { background: #dbeafe; color: #1e40af; }
.badge-not-started { background: #f3f4f6; color: #4b5563; }
.badge-confirmed { background: #d1fae5; color: #065f46; }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }

.notes-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    border-left: 4px solid #f59e0b;
    grid-column: 1 / -1;
}

.notes-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 10px;
}

.notes-text {
    color: #78350f;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .progress-table { overflow-x: auto; }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

<div class="progress-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Student Progress</h1>
            <p class="page-subtitle">Track student training progress and session attendance for {{ $schoolName }}</p>
        </div>
    </div>

    @forelse($progresses as $progress)
        @php
            // Get student's bookings for this course
            $bookings = \App\Models\Booking::where('student_id', $progress->student_id)
                ->where('course_id', $progress->course_id)
                ->where('school_id', $school->id)
                ->with(['instructor', 'course'])
                ->orderBy('scheduled_at', 'desc')
                ->get();
            
            $completedSessions = $bookings->where('status', 'confirmed')->count();
            $totalSessions = $progress->course->packages()->first()->training_hours ?? $progress->course->duration_hours;
            $currentBooking = $bookings->where('status', 'confirmed')->sortByDesc('scheduled_at')->first() ?? $bookings->first();
            $nextBooking = $bookings->where('status', 'pending')->where('scheduled_at', '>', now())->sortBy('scheduled_at')->first();
        @endphp
    @empty
    @endforelse

    @if($progresses->count() > 0)
    <div class="progress-table">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Student Name</th>
                    <th>Current Session</th>
                    <th>Current Instructor</th>
                    <th>Next Schedule</th>
                </tr>
            </thead>
            <tbody>
                @foreach($progresses as $index => $progress)
                    @php
                        $bookings = \App\Models\Booking::where('student_id', $progress->student_id)
                            ->where('course_id', $progress->course_id)
                            ->where('school_id', $school->id)
                            ->with(['instructor', 'course'])
                            ->orderBy('scheduled_at', 'desc')
                            ->get();
                        
                        $completedSessions = $bookings->where('status', 'confirmed')->count();
                        $totalSessions = ceil($progress->course->duration_hours ?? 10);
                        $currentBooking = $bookings->where('status', 'confirmed')->sortByDesc('scheduled_at')->first() ?? $bookings->first();
                        $nextBooking = $bookings->where('status', 'pending')->where('scheduled_at', '>', now())->sortBy('scheduled_at')->first();
                    @endphp
                    
                    <tr class="progress-row" onclick="toggleDetails({{ $index }})">
                        <td><span class="expand-icon" id="icon-{{ $index }}">▶</span></td>
                        <td><span class="student-name">{{ $progress->student->name }}</span></td>
                        <td>
                            <span class="session-info">
                                {{ $completedSessions }} / {{ $totalSessions }} sessions
                            </span>
                        </td>
                        <td>
                            @if($currentBooking && $currentBooking->instructor)
                                <span class="instructor-badge">
                                    {{ $currentBooking->instructor->name }}
                                </span>
                            @else
                                <span style="color: #9ca3af; font-style: italic;">Not assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($nextBooking)
                                <span class="schedule-badge">
                                    {{ $nextBooking->scheduled_at->format('M d, Y h:i A') }}
                                </span>
                            @else
                                <span style="color: #9ca3af; font-style: italic;">No upcoming schedule</span>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Detailed View (Hidden by default) -->
                    <tr class="detail-row" id="detail-{{ $index }}">
                        <td colspan="5">
                            <div class="detail-content">
                                <div class="detail-grid">
                                    <!-- Student Information -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Student Information</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Full Name</span>
                                            <span class="detail-value">{{ $progress->student->name }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Email</span>
                                            <span class="detail-value">{{ $progress->student->email }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Contact</span>
                                            <span class="detail-value">{{ $progress->student->contact ?? 'N/A' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Status</span>
                                            <span class="detail-value">
                                                <span class="badge badge-{{ strtolower($progress->student->status) }}">
                                                    {{ ucfirst($progress->student->status) }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Course Information -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Course Information</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Course Title</span>
                                            <span class="detail-value">{{ $progress->course->title }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Course Type</span>
                                            <span class="detail-value">{{ ucfirst($progress->course->type) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Duration</span>
                                            <span class="detail-value">{{ $progress->course->duration_hours }} hours</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Vehicle Type</span>
                                            <span class="detail-value">{{ $progress->course->vehicle_type ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Session Progress -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Session Progress</h4>
                                        <div class="detail-item">
                                            <span class="detail-label">Completed Sessions</span>
                                            <span class="detail-value">{{ $completedSessions }} / {{ $totalSessions }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Remaining Sessions</span>
                                            <span class="detail-value">{{ max(0, $totalSessions - $completedSessions) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Bookings</span>
                                            <span class="detail-value">{{ $bookings->count() }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Last Session</span>
                                            <span class="detail-value">
                                                {{ $currentBooking ? $currentBooking->scheduled_at->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Instructor Details -->
                                    <div class="detail-section">
                                        <h4 class="detail-section-title">Instructor Details</h4>
                                        @if($currentBooking && $currentBooking->instructor)
                                            <div class="detail-item">
                                                <span class="detail-label">Current Instructor</span>
                                                <span class="detail-value">{{ $currentBooking->instructor->name }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Email</span>
                                                <span class="detail-value">{{ $currentBooking->instructor->email }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Contact</span>
                                                <span class="detail-value">{{ $currentBooking->instructor->contact ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                            <div style="text-align: center; padding: 20px; color: #9ca3af;">
                                                <p>No instructor assigned yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Overall Progress Bar -->
                                <div class="progress-bar-section">
                                    <h4 class="detail-section-title">Overall Completion Progress</h4>
                                    <div class="progress-bar-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" style="width: {{ $progress->completion_percent }}%;">
                                                @if($progress->completion_percent > 10)
                                                    {{ number_format($progress->completion_percent, 0) }}%
                                                @endif
                                            </div>
                                        </div>
                                        <div class="progress-percentage">{{ number_format($progress->completion_percent, 0) }}%</div>
                                    </div>
                                    <div style="margin-top: 15px; display: flex; justify-content: space-between; font-size: 0.85rem; color: #6b7280;">
                                        <span>Last Updated: {{ $progress->last_updated ? $progress->last_updated->format('M d, Y h:i A') : 'Never' }}</span>
                                        <span>
                                            @if($progress->completion_percent == 100)
                                                <span class="badge badge-completed">✓ Completed</span>
                                            @elseif($progress->completion_percent > 0)
                                                <span class="badge badge-in-progress">In Progress</span>
                                            @else
                                                <span class="badge badge-not-started">Not Started</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <!-- Instructor Notes -->
                                @if($progress->notes)
                                <div class="notes-section">
                                    <h4 class="notes-section-title">Instructor Notes</h4>
                                    <p class="notes-text">{{ $progress->notes }}</p>
                                </div>
                                @endif

                                <!-- Recent Bookings -->
                                @if($bookings->count() > 0)
                                <div class="detail-section" style="grid-column: 1 / -1; margin-top: 10px;">
                                    <h4 class="detail-section-title">📅 Recent Bookings</h4>
                                    <table style="width: 100%; margin-top: 10px;">
                                        <thead style="background: #f3f4f6;">
                                            <tr>
                                                <th style="padding: 10px; text-align: left; font-size: 0.85rem;">Date & Time</th>
                                                <th style="padding: 10px; text-align: left; font-size: 0.85rem;">Instructor</th>
                                                <th style="padding: 10px; text-align: left; font-size: 0.85rem;">Status</th>
                                                <th style="padding: 10px; text-align: left; font-size: 0.85rem;">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bookings->take(5) as $booking)
                                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                                <td style="padding: 10px; font-size: 0.85rem;">{{ $booking->scheduled_at->format('M d, Y h:i A') }}</td>
                                                <td style="padding: 10px; font-size: 0.85rem;">{{ $booking->instructor->name ?? 'N/A' }}</td>
                                                <td style="padding: 10px; font-size: 0.85rem;">
                                                    <span class="badge badge-{{ strtolower($booking->status) }}">{{ ucfirst($booking->status) }}</span>
                                                </td>
                                                <td style="padding: 10px; font-size: 0.85rem;">{{ Str::limit($booking->notes ?? 'No notes', 50) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <p style="font-size: 1.2rem; color: #9ca3af;">No progress records found</p>
            <p style="color: #6b7280; margin-top: 10px;">Student progress will appear here once training begins</p>
        </div>
    @endif
</div>

<script>
function toggleDetails(index) {
    const detailRow = document.getElementById('detail-' + index);
    const progressRow = detailRow.previousElementSibling;
    const icon = document.getElementById('icon-' + index);
    
    if (detailRow.classList.contains('show')) {
        detailRow.classList.remove('show');
        progressRow.classList.remove('expanded');
    } else {
        detailRow.classList.add('show');
        progressRow.classList.add('expanded');
    }
}
</script>

@endsection

