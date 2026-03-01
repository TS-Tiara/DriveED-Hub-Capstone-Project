@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Session Details')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .session-detail-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 20px;
        transition: color 0.2s;
    }
    .back-link:hover { color: {{ $primaryColor }}; }

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .detail-header {
        padding: 24px;
        @if($settings?->use_gradient_header ?? true)
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $settings?->secondary_color ?? '#764ba2' }} 100%);
        @else
        background: {{ $primaryColor }};
        @endif
        color: white;
    }

    .detail-header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0 0 8px 0;
    }

    .detail-header .meta {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .detail-body {
        padding: 24px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
    }

    .info-group {
        padding: 16px;
        background: #f9fafb;
        border-radius: 12px;
    }

    .info-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #1f2937;
    }

    .info-value.highlight {
        font-size: 1.25rem;
        color: {{ $primaryColor }};
    }

    .person-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .person-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }

    .person-avatar.instructor { background: #f59e0b; }

    .type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .type-theoretical { background: #dbeafe; color: #1e40af; }
    .type-practical { background: #ede9fe; color: #5b21b6; }

    .notes-section {
        margin-top: 24px;
        padding: 20px;
        background: #fffbeb;
        border-radius: 12px;
        border-left: 4px solid #f59e0b;
    }

    .notes-section h3 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #92400e;
        margin: 0 0 8px 0;
    }

    .notes-section p {
        margin: 0;
        color: #78350f;
        line-height: 1.6;
    }

    .action-bar {
        margin-top: 24px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn-delete {
        padding: 10px 20px;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-delete:hover { background: #fecaca; }

    .icon-16 {
        width: 16px;
        height: 16px;
    }

    .icon-18 {
        width: 18px;
        height: 18px;
    }

    .icon-inline-start {
        display: inline;
        vertical-align: middle;
        margin-right: 6px;
    }
</style>

<div class="admin-container session-detail-container">
    <a href="{{ school_route('admin.sessions.index') }}" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Sessions
    </a>

    <div class="detail-card">
        <div class="detail-header">
            <h1>Session Details</h1>
            <div class="meta">
                Logged on {{ $sessionCompletion->created_at->format('M d, Y \a\t g:i A') }}
            </div>
        </div>

        <div class="detail-body">
            <div class="info-grid">
                <div class="info-group">
                    <div class="info-label">Student</div>
                    <div class="person-row">
                        <div class="person-avatar">
                            {{ strtoupper(substr($sessionCompletion->enrollment->student->name ?? 'N', 0, 1)) }}
                        </div>
                        <div class="info-value">{{ $sessionCompletion->enrollment->student->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Instructor</div>
                    <div class="person-row">
                        <div class="person-avatar instructor">
                            {{ strtoupper(substr($sessionCompletion->instructor->name ?? 'N', 0, 1)) }}
                        </div>
                        <div class="info-value">{{ $sessionCompletion->instructor->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Session Date</div>
                    <div class="info-value highlight">{{ $sessionCompletion->session_date->format('F d, Y') }}</div>
                </div>

                <div class="info-group">
                    <div class="info-label">Session Time</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($sessionCompletion->session_time)->format('g:i A') }}</div>
                </div>

                <div class="info-group">
                    <div class="info-label">Course</div>
                    <div class="info-value">{{ $sessionCompletion->enrollment->course->title ?? 'N/A' }}</div>
                </div>

                <div class="info-group">
                    <div class="info-label">Session Type</div>
                    <div class="info-value">
                        <span class="type-badge type-{{ $sessionCompletion->session_type }}">
                            {{ ucfirst($sessionCompletion->session_type) }}
                        </span>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">Hours Completed</div>
                    <div class="info-value highlight">{{ number_format($sessionCompletion->hours_completed, 1) }} hours</div>
                </div>

                <div class="info-group">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ ucfirst($sessionCompletion->status ?? 'completed') }}</div>
                </div>
            </div>

            @if($sessionCompletion->notes)
            <div class="notes-section">
                <h3>Session Notes</h3>
                <p>{{ $sessionCompletion->notes }}</p>
            </div>
            @endif

            <div class="action-bar">
                <form method="POST" action="{{ school_route('admin.sessions.destroy', ['sessionCompletion' => $sessionCompletion->id]) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this session?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon-16 icon-inline-start">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Session
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
