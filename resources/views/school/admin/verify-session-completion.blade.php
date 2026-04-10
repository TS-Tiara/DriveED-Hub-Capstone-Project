@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Verify Session Completion')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
        $settings = $school?->schoolSetting;
        $primaryColor = $settings?->primary_color ?? '#667eea';
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        /* Booking Cards - Using shared content-card styles */
        .bookings-list {
            display: grid;
            gap: 20px;
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .booking-info h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .booking-date {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .booking-time {
            font-weight: 600;
            color:
                {{ $primaryColor }}
            ;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }

        .bookings-container {
            padding: 20px;
            margin: 0 auto;
            max-width: 1200px;
        }

        .booking-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .status-select {
            padding: 8px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
            min-width: 160px;
        }

        .status-select:focus {
            outline: none;
            border-color:
                {{ $primaryColor }}
            ;
        }

        .paid-indicator {
            color: #059669;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .verify-controls {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin: -10px 0 20px;
        }

        .verify-controls label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .verify-sort-select {
            min-width: 280px;
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .verify-sort-select:focus {
            outline: none;
            border-color: {{ $primaryColor }};
        }

        .stat-card-clickable {
            cursor: pointer;
        }

        .stat-card-active {
            box-shadow: 0 0 0 2px rgba(31, 41, 55, 0.15), 0 8px 20px rgba(0, 0, 0, 0.12) !important;
            transform: translateY(-1px);
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .detail-value-amount {
            color: #059669;
            font-weight: 600;
        }

        .detail-item-full {
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: 1fr 1fr;
            }

            .booking-actions {
                flex-direction: column;
                width: 100%;
            }

            .booking-actions .btn,
            .booking-actions .status-select {
                width: 100%;
            }

            .verify-controls {
                justify-content: stretch;
                align-items: stretch;
                flex-direction: column;
            }

            .verify-sort-select {
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    <div class="admin-container">
        <!-- Flash Messages -->


        <!-- Page Header -->
        <div class="page-header"
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div class="page-header-left">
                <h1 class="page-title">Verify Session Completion</h1>
                <p class="page-subtitle">Verify instructor-logged sessions and manage training completion logs for
                    {{ $schoolName }}</p>
            </div>
            {{-- Unified Export Dropdown --}}
            <div class="export-dropdown" id="bookingExport">
                <button type="button" class="btn-export-trigger" onclick="this.parentElement.classList.toggle('open')">
                    <i class="bi bi-download"></i>
                    Export Report
                    <i class="bi bi-chevron-down chevron"></i>
                </button>
                <div class="export-dropdown-menu">
                    <div class="dropdown-header">Export Options</div>
                    <a
                        href="{{ route('schools.admin.exports.verify-session-completion.excel', ['school' => $school->slug]) }}">
                        <i class="bi bi-file-earmark-excel-fill" style="color: #10b981;"></i> Full Export (Excel)
                    </a>
                    <div class="dropdown-header" style="font-size: 0.65rem; color: #94a3b8;">Format help</div>
                    <div style="padding: 10px 16px; font-size: 0.8rem; color: #64748b; line-height: 1.4;">
                        Export contains all verified and pending completions for the current branch.
                    </div>
                </div>
            </div>
        </div>



        <!-- Statistics Cards (Consolidated 5-card View) -->
        <div class="stats-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <!-- All Sessions (Full View) -->
            <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'all' ? 'stat-card-active' : '' }}" id="card-all"
                style="border-left: 5px solid #6366f1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                onclick="filterBookings('all')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">All Sessions</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $allSessionsCount }}</div>
                        <div style="color: #6366f1; font-size: 0.8rem; margin-top: 5px;">Full activity log</div>
                    </div>
                    <div style="background: rgba(99, 102, 241, 0.1); color: #6366f1; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                </div>
            </div>
            <!-- Awaiting Verification (Priority Focus) -->
            <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'done' ? 'stat-card-active' : '' }}" id="card-done"
                style="border-left: 5px solid #f59e0b; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                onclick="filterBookings('done')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div
                            style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                            Awaiting Verification</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $awaitingVerificationCount }}
                        </div>
                        <div style="color: #f59e0b; font-size: 0.8rem; margin-top: 5px;">Marked as Done by Instructor</div>
                    </div>
                    <div
                        style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>

            <!-- Verified Sessions (Success State) -->
            <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'completed' ? 'stat-card-active' : '' }}"
                style="border-left: 5px solid #10b981; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                onclick="filterBookings('completed')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div
                            style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                            Verified Sessions</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $verifiedSessionsCount }}</div>
                        <div style="color: #10b981; font-size: 0.8rem; margin-top: 5px;">Successfully finalized logs</div>
                    </div>
                    <div
                        style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Flagged Issues (Error/Warning State) -->
            <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'flagged' ? 'stat-card-active' : '' }}"
                style="border-left: 5px solid #ef4444; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                onclick="filterBookings('flagged')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div
                            style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                            Flagged Issues</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $flaggedIssuesCount }}</div>
                        <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">Cancelled or no-show logs</div>
                    </div>
                    <div
                        style="background: rgba(239, 68, 68, 0.1); color: #ef4444; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <!-- Booking Requests (Initialization State) -->
            <div class="stat-card stat-card-clickable {{ ($activeFilter ?? 'all') === 'pending' ? 'stat-card-active' : '' }}"
                style="border-left: 5px solid #3b82f6; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); cursor: pointer;"
                onclick="filterBookings('pending')">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div
                            style="color: #6b7280; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">
                            Booking Requests</div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">{{ $pendingRequestsCount }}</div>
                        <div style="color: #3b82f6; font-size: 0.8rem; margin-top: 5px;">New schedule approvals pending
                        </div>
                    </div>
                    <div
                        style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-envelope-paper"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="verify-controls">
            <label for="verifySort">Sort</label>
            <select id="verifySort" class="verify-sort-select" onchange="applySort(this.value)">
                <option value="audit_priority" {{ ($activeSort ?? 'audit_priority') === 'audit_priority' ? 'selected' : '' }}>Audit Priority (Done -&gt; No-show -&gt; Scheduled)</option>
                <option value="session_newest" {{ ($activeSort ?? 'audit_priority') === 'session_newest' ? 'selected' : '' }}>Session Date: Newest First</option>
                <option value="session_oldest" {{ ($activeSort ?? 'audit_priority') === 'session_oldest' ? 'selected' : '' }}>Session Date: Oldest First</option>
                <option value="student_az" {{ ($activeSort ?? 'audit_priority') === 'student_az' ? 'selected' : '' }}>Student Name: A-Z</option>
                <option value="instructor_az" {{ ($activeSort ?? 'audit_priority') === 'instructor_az' ? 'selected' : '' }}>Instructor Name: A-Z</option>
                <option value="recently_updated" {{ ($activeSort ?? 'audit_priority') === 'recently_updated' ? 'selected' : '' }}>Recently Updated</option>
            </select>
        </div>

        <!-- Bookings List -->
        <div class="bookings-list" id="bookingsList">
            @forelse($bookings as $booking)
                @php
                    $sessionType = $booking->timeSlot->session_type
                        ?? (($booking->course && $booking->course->course_type === 'practical') ? 'practical' : 'theoretical');

                    $sessionHours = null;
                    if ($booking->timeSlot && $booking->timeSlot->start_time && $booking->timeSlot->end_time) {
                        $start = \Carbon\Carbon::parse($booking->timeSlot->start_time);
                        $end = \Carbon\Carbon::parse($booking->timeSlot->end_time);
                        $sessionHours = round($start->diffInMinutes($end) / 60, 2);
                    }

                    if (is_null($sessionHours) || $sessionHours <= 0) {
                        $sessionHours = $booking->package->training_hours ?? $booking->course->duration_hours ?? 1;
                    }
                @endphp
                <div class="booking-card" data-status="{{ $booking->status }}">
                    <div class="booking-header">
                        <div class="booking-info">
                            <h3>{{ $booking->course->title ?? 'N/A' }}</h3>
                            <span
                                class="badge badge-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'done' ? 'warning' : ($booking->status === 'cancelled' ? 'danger' : ($booking->status === 'pending' ? 'warning' : 'info'))) }}">
                                {{ $booking->status === 'done' ? 'Awaiting Verification' : ucfirst($booking->status) }}
                            </span>
                        </div>
                        <div class="booking-date">
                            @if($booking->timeSlot)
                                <span>{{ \Carbon\Carbon::parse($booking->timeSlot->date)->format('M d, Y') }}</span>
                                <span
                                    class="booking-time">{{ \Carbon\Carbon::parse($booking->timeSlot->start_time)->format('h:i A') }}
                                    - {{ \Carbon\Carbon::parse($booking->timeSlot->end_time)->format('h:i A') }}</span>
                            @elseif($booking->scheduled_at)
                                <span>{{ $booking->scheduled_at->format('M d, Y') }}</span>
                                <span class="booking-time">{{ $booking->scheduled_at->format('h:i A') }}</span>
                            @else
                                <span>{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : 'Not scheduled' }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="booking-details">
                        <div class="detail-item">
                            <span class="detail-label">Student</span>
                            <span class="detail-value">{{ $booking->student->name ?? 'N/A' }}</span>
                        </div>
                        @if($booking->instructor)
                            <div class="detail-item">
                                <span class="detail-label">Instructor</span>
                                <span class="detail-value">{{ $booking->instructor->name }}</span>
                            </div>
                        @endif
                        @if($booking->package)
                            <div class="detail-item">
                                <span class="detail-label">Package</span>
                                <span class="detail-value">{{ $booking->package->name }} -
                                    {{ $booking->package->transmission_type }}</span>
                            </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Session Type</span>
                            <span class="detail-value">{{ ucfirst($sessionType) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Hours</span>
                            <span class="detail-value">{{ number_format((float) $sessionHours, 1) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">{{ $booking->status === 'done' ? 'Awaiting Verification' : ucfirst(str_replace('-', ' ', $booking->status)) }}</span>
                        </div>
                        @if($booking->notes)
                            <div class="detail-item detail-item-full">
                                <span class="detail-label">Notes</span>
                                <span class="detail-value">{{ $booking->notes }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="booking-actions">
                        @php
                            $isFinalStatus = in_array($booking->status, ['completed', 'cancelled'], true);
                            $canAdminAudit = in_array($booking->status, ['scheduled', 'done', 'no-show'], true);
                        @endphp
                        <select class="status-select" onchange="updateStatus({{ $booking->id }}, this.value)" {{ !$canAdminAudit ? 'disabled' : '' }}>
                            <option value="">Admin Action</option>
                            @if($booking->status === 'done')
                                <option value="completed">Completed (Verify &amp; Log)</option>
                                <option value="cancelled">Cancelled</option>
                            @elseif($booking->status === 'no-show')
                                <option value="cancelled">Cancelled</option>
                            @elseif($booking->status === 'scheduled')
                                <option value="cancelled">Cancelled</option>
                            @elseif($booking->status === 'completed')
                                <option value="completed" selected>Completed</option>
                            @elseif($booking->status === 'cancelled')
                                <option value="cancelled" selected>Cancelled</option>
                            @endif
                        </select>

                        @if($booking->status === 'done' && !$isFinalStatus)
                            <button class="btn btn-success btn-sm" onclick="updateStatus({{ $booking->id }}, 'completed')">Verify & Log Session</button>
                        @elseif($booking->status === 'completed')
                            <span class="paid-indicator">✓ Session Logged</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="detail-label">Session cancelled</span>
                        @elseif($booking->status === 'no-show')
                            <span class="detail-label">No-show recorded by instructor</span>
                        @else
                            <span class="detail-label">Awaiting instructor completion</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="content-card">
                    <div class="content-card-body">
                        <div class="empty-state">
                            <div class="empty-state-title">No schedules found</div>
                            <div class="empty-state-text">Schedule records will appear here once students make reservations.
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="mt-4">
            <div class="admin-pagination-wrapper">
                {{ $bookings->links('vendor.pagination.drivingapp') }}
            </div>
        </div>
    </div>

    <script>
        const schoolSlug = '{{ $school->slug }}';
        const currentVerifyFilter = @json($activeFilter ?? 'all');
        const currentVerifySort = @json($activeSort ?? 'audit_priority');

        function buildVerifyUrl(status, sort) {
            const url = new URL(window.location.pathname, window.location.origin);

            if (status && status !== 'all') {
                url.searchParams.set('status', status);
            }

            if (sort && sort !== 'audit_priority') {
                url.searchParams.set('sort', sort);
            }

            return url;
        }

        function filterBookings(status) {
            const sortSelect = document.getElementById('verifySort');
            const selectedSort = sortSelect ? sortSelect.value : currentVerifySort;
            window.location.href = buildVerifyUrl(status, selectedSort).toString();
        }

        function applySort(sort) {
            window.location.href = buildVerifyUrl(currentVerifyFilter, sort).toString();
        }

        function updateStatus(bookingId, status) {
            if (!status) return;

            showConfirm({
                type: 'warning',
                title: 'Change Schedule Status',
                message: `Are you sure you want to change this schedule status to "${status}"?`,
                confirmText: 'Yes, Update Status',
                onConfirm: () => {
                    fetch(`/${schoolSlug}/admin/verify-session-completion/${bookingId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: status })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Toast.success('Schedule status has been updated successfully.', 'Status Updated!');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                Toast.error(data.message || 'Failed to update schedule status.', 'Update Failed');
                            }
                        })
                        .catch(error => {
                            Toast.error('An error occurred while updating the status.', 'Error');
                            console.error(error);
                        });
                }
            });
        }

    </script>
@endsection