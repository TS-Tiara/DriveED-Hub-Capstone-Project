@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Browse Courses')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;

    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#8b5cf6';

    // Calculate RGB values for transparency effects
    $primaryRgb = sscanf($primaryColor, "#%02x%02x%02x");
@endphp

<style>
    .courses-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .courses-header {
        margin-bottom: 20px;
        border-bottom: 4px solid {{ $primaryColor }};
        padding-bottom: 15px;
    }
    
    .courses-header h1 {
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
        color: #1a202c;
    }
    
    .courses-header p {
        color: #6b7280;
        margin: 8px 0 0 0;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid #17a2b8;
    }
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }
    
    .alert-close:hover {
        opacity: 1;
    }

    .alert-close:focus-visible,
    .btn-close:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
        border-radius: 6px;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    
    .course-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s;
    }
    
    .course-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .course-banner {
        height: 160px;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .course-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-banner-icon {
        color: rgba(255,255,255,0.9);
    }
    
    .featured-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .badge-type {
        padding: 4px 10px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-vehicle {
        padding: 4px 10px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 10px 0;
    }
    
    .course-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
    }
    
    .course-features {
        list-style: none;
        padding: 0;
        margin: 0 0 15px 0;
    }
    
    .course-features li {
        padding: 5px 0 5px 22px;
        position: relative;
        color: #374151;
        font-size: 0.85rem;
    }
    
    .course-features li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        background: {{ $primaryColor }};
        border-radius: 50%;
    }

    .feature-more-primary {
        color: {{ $primaryColor }};
        font-weight: 600;
    }
    
    .course-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }
    
    .packages-section {
        background: #fafafa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #e5e7eb;
    }
    
    .packages-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .package-item {
        background: white;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .package-item:last-child {
        margin-bottom: 0;
    }
    
    .package-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .package-tag {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .tag-manual {
        background: #fef3c7;
        color: #92400e;
    }
    
    .tag-automatic {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .tag-popular {
        background: #fef3c7;
        color: #b45309;
    }
    
    .package-details {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .package-price {
        font-weight: 700;
        color: {{ $primaryColor }};
        font-size: 1rem;
    }
    
    .more-packages {
        text-align: center;
        color: {{ $primaryColor }};
        font-weight: 600;
        font-size: 0.8rem;
        padding-top: 6px;
    }
    
    .btn-enroll {
        width: 100%;
        padding: 12px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: opacity 0.2s;
        margin-top: auto;
    }
    
    .btn-enroll:hover {
        opacity: 0.9;
    }
    
    .btn-enroll:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
    
    .btn-enrolled {
        background: #10b981;
    }
    
    .btn-pending {
        background: #f59e0b;
    }
    
    .enrollment-status {
        text-align: center;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .empty-state svg {
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .empty-state p {
        margin: 5px 0;
    }

    .empty-state-title { font-size: 1.1rem; }
    .empty-state-subtitle { font-size: 0.9rem; }
    .license-required-note {
        text-align: center;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #fef3c7;
        color: #92400e;
        margin-bottom: 8px;
    }
    .alert-warning-compact { font-size: 0.9rem; }
    .credential-section-hidden { display: none; }
    
    @media (max-width: 768px) {
        .courses-container {
            padding: 15px;
        }
        
        .courses-header h1 {
            font-size: 1.5rem;
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .course-banner {
            height: 140px;
        }
        
        .course-body {
            padding: 15px;
        }
        
        .package-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .package-price {
            align-self: flex-end;
        }
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .modal-dialog {
        max-width: 600px;
        width: 90%;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        padding: 20px 25px;
        border-bottom: 2px solid #e5e7eb;
        background: {{ $primaryColor }};
        color: white;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }
    
    .btn-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.75rem;
        line-height: 1;
        cursor: pointer;
        opacity: 0.8;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }
    
    .btn-close:hover {
        opacity: 1;
        background: rgba(255,255,255,0.1);
    }
    
    .modal-body {
        padding: 25px;
        overflow-y: auto;
        flex: 1;
    }
    
    .modal-footer {
        padding: 15px 25px;
        border-top: 2px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        border-radius: 0 0 12px 12px;
    }
    
    /* Form Styles */
    .mb-3 {
        margin-bottom: 1.5rem;
    }
    
    .mb-0 {
        margin-bottom: 0;
    }
    
    .me-1 {
        margin-right: 0.25rem;
    }
    
    .me-2 {
        margin-right: 0.5rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 0.95rem;
    }
    
    .form-label strong {
        font-weight: 600;
    }
    
    .form-select,
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.2s;
        background: white;
    }
    
    .form-select:focus,
    .form-control:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px rgba({{ implode(',', $primaryRgb) }}, 0.1);
    }
    
    .form-control[type="file"] {
        padding: 8px 14px;
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    
    .text-muted {
        color: #6b7280;
        font-size: 0.85rem;
        display: block;
        margin-top: 5px;
    }
    
    .text-danger {
        color: #ef4444;
    }
    
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 5px;
        display: block;
    }
    
    .is-invalid {
        border-color: #ef4444 !important;
    }
    
    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .bg-info {
        background: #3b82f6;
        color: white;
    }
    
    .bg-primary {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .bg-secondary {
        background: #6b7280;
        color: white;
    }
    
    /* Alert Styles */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }
    
    /* Button Styles */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-primary {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba({{ implode(',', $primaryRgb) }}, 0.3);
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
</style>

<div class="courses-container">
    <div class="courses-header">
        <h1>Available Courses</h1>
        <p>Browse our courses and submit an enrollment request to get started</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <span>✓ {{ session('success') }}</span>
            <button type="button" class="alert-close" aria-label="Dismiss success message" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            <span>✕ {{ session('error') }}</span>
            <button type="button" class="alert-close" aria-label="Dismiss error message" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
    
    @if(session('warning'))
        <div class="alert alert-warning">
            <span>⚠ {{ session('warning') }}</span>
            <button type="button" class="alert-close" aria-label="Dismiss warning message" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            <span>ℹ {{ session('info') }}</span>
            <button type="button" class="alert-close" aria-label="Dismiss information message" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    <div class="courses-grid">
        
        @forelse($courses as $course)
        <div class="course-card">
            <div class="course-banner">
                @if($course->hasBannerImage)
                    <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}">
                @else
                    <svg class="course-banner-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679c.033.161.049.325.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.807.807 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6ZM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17 1.247 0 3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z"/>
                    </svg>
                @endif
                
                @if($course->is_featured)
                    <span class="featured-badge">⭐ Featured</span>
                @endif
            </div>
            
            <div class="course-body">
                <div class="course-badges">
                    <span class="badge-type">{{ ucfirst($course->type ?? 'Standard') }}</span>
                    @if($course->vehicle_type)
                        <span class="badge-vehicle">{{ $course->vehicle_type }}</span>
                    @endif
                </div>
                
                <h3 class="course-title">{{ $course->title }}</h3>
                
                @if($course->description)
                    <p class="course-description">{{ Str::limit($course->description, 120) }}</p>
                @endif
                
                @php $features = $course->features; @endphp
                @if($features && is_array($features) && count($features) > 0)
                    <ul class="course-features">
                        @foreach(array_slice($features, 0, 3) as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                        @if(count($features) > 3)
                            <li class="feature-more-primary">+{{ count($features) - 3 }} more</li>
                        @endif
                    </ul>
                @endif
                
                @if($course->packages && $course->packages->count() > 0)
                    <div class="packages-section">
                        <div class="packages-title">Available Packages</div>
                        @foreach($course->packages->take(2) as $package)
                            <div class="package-item">
                                <div>
                                    <div class="package-name">
                                        {{ $package->name }}
                                        @if($package->transmission_type)
                                            <span class="package-tag tag-{{ $package->transmission_type }}">{{ strtoupper($package->transmission_type) }}</span>
                                        @endif
                                        @if($package->is_popular)
                                            <span class="package-tag tag-popular">POPULAR</span>
                                        @endif
                                    </div>
                                    <div class="package-details">
                                        @if($package->training_hours){{ $package->training_hours }} hours @endif
                                    </div>
                                </div>
                                <span class="package-price">P{{ number_format($package->price, 2) }}</span>
                            </div>
                        @endforeach
                        @if($course->packages->count() > 2)
                            <div class="more-packages">+{{ $course->packages->count() - 2 }} more packages</div>
                        @endif
                    </div>
                @else
                    <div class="course-info">
                        @if($course->duration_hours)
                        <div class="info-row">
                            <span class="info-label">Duration</span>
                            <span class="info-value">{{ $course->duration_hours }} hours</span>
                        </div>
                        @endif
                        @if($course->price > 0)
                        <div class="info-row">
                            <span class="info-label">Price</span>
                            <span class="info-value">P{{ number_format($course->price, 2) }}</span>
                        </div>
                        @endif
                    </div>
                @endif

                @if(in_array($course->id, $enrolledCourseIds))
                    @php $status = $enrollmentStatuses[$course->id] ?? 'pending'; @endphp
                    <div class="enrollment-status status-{{ $status }}">
                        @if($status === 'approved')
                            Enrollment Approved
                        @else
                            Enrollment Request Pending
                        @endif
                    </div>
                    <button class="btn-enroll btn-{{ $status === 'approved' ? 'enrolled' : 'pending' }}" disabled>
                        {{ $status === 'approved' ? 'Already Enrolled' : 'Request Pending' }}
                    </button>
                @else
                    @if($course->course_type === 'practical' && (!$guest || !$guest->hasVerifiedLicense()))
                        <div class="license-required-note">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Student Driver's License required for PDC
                        </div>
                        <button class="btn-enroll" disabled title="You need a verified student driver's license to enroll in practical driving courses (PDC)">
                            License Required
                        </button>
                    @else
                        <button type="button" class="btn-enroll" onclick="openEnrollModal({{ $course->id }})">
                            Enroll
                        </button>
                    @endif
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/>
            </svg>
            <p class="empty-state-title">No courses available at the moment</p>
            <p class="empty-state-subtitle">Please check back later for new courses.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Enrollment Modals -->
@foreach($courses as $course)
<div class="modal fade" id="enrollModal{{ $course->id }}" tabindex="-1" aria-labelledby="enrollModalLabel{{ $course->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('schools.guest.enroll', ['school' => $school, 'course' => $course->id]) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollModalLabel{{ $course->id }}">Enroll in {{ $course->title }}</h5>
                    <button type="button" class="btn-close" onclick="closeEnrollModal({{ $course->id }})" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <!-- Course Type Badge -->
                    <div class="mb-3">
                        <span class="badge {{ $course->course_type == 'theoretical' ? 'bg-info' : 'bg-primary' }} me-2">
                            {{ ucfirst($course->course_type) }} Course ({{ $course->course_type == 'theoretical' ? 'TDC' : 'PDC' }})
                        </span>
                        <span class="badge bg-secondary">
                            {{ ucfirst(str_replace('_', ' ', $course->license_type)) }}
                        </span>
                    </div>

                    @if($course->course_type === 'practical')
                    <!-- PDC License Requirement Notice -->
                    <div class="alert alert-warning alert-warning-compact mb-3">
                        <i class="fas fa-id-card me-1"></i>
                        <strong>PDC Requirement:</strong> Practical Driving Courses require a verified Student Driver's License. Only experienced drivers with valid credentials can enroll.
                    </div>
                    @endif

                    <!-- Branch Selection -->
                    @if($enableBranches && $branches->count() > 0)
                    <div class="mb-3">
                        <label for="branch_id{{ $course->id }}" class="form-label">
                            <strong>Preferred Branch</strong> <span class="text-danger">*</span>
                        </label>
                        <select name="branch_id" id="branch_id{{ $course->id }}" class="form-select @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select Branch...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}{{ $branch->address ? ' — ' . $branch->address : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <!-- Experience Level Selection -->
                    <div class="mb-3">
                        <label for="experience_level{{ $course->id }}" class="form-label">
                            <strong>Driver Experience Level</strong> <span class="text-danger">*</span>
                        </label>
                        <select name="experience_level" id="experience_level{{ $course->id }}" class="form-select @error('experience_level') is-invalid @enderror" required onchange="handleExperienceChange{{ $course->id }}()">
                            <option value="">Select your experience...</option>
                            @if($course->course_type === 'theoretical')
                                <option value="new_driver" {{ old('experience_level') == 'new_driver' ? 'selected' : '' }}>
                                    New Driver (No license, learning from scratch)
                                </option>
                                <option value="experienced_driver" {{ old('experience_level') == 'experienced_driver' ? 'selected' : '' }}>
                                    Experienced Driver (Have license or experience)
                                </option>
                            @else
                                {{-- PDC: Only experienced drivers allowed --}}
                                <option value="experienced_driver" {{ old('experience_level') == 'experienced_driver' ? 'selected' : '' }}>
                                    Experienced Driver (Have Student Driver's License)
                                </option>
                            @endif
                        </select>
                        @error('experience_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($course->course_type === 'theoretical')
                            <small class="text-muted">TDC is open to all drivers — this helps us customize your learning path</small>
                        @else
                            <small class="text-muted">PDC requires a Student Driver's License and prior theoretical completion</small>
                        @endif
                    </div>

                    <!-- Credential Upload (shown for experienced drivers on practical courses) -->
                    <div class="mb-3 credential-section-hidden" id="credentialSection{{ $course->id }}">
                        <label for="credential_file{{ $course->id }}" class="form-label">
                            <strong>Student Driver's License / Credential</strong>
                            @if($course->course_type === 'practical')
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="file" name="credential_file" id="credential_file{{ $course->id }}" class="form-control @error('credential_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" {{ $course->course_type === 'practical' ? 'required' : '' }}>
                        @error('credential_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            @if($course->course_type === 'practical')
                                Upload your Student Driver's License or TDC completion certificate (required for PDC, PDF/Image, max 5MB)
                            @else
                                Upload a copy of your existing driver's license or credential (optional, PDF/Image, max 5MB)
                            @endif
                        </small>
                    </div>

                    <!-- Package Selection (if available) -->
                    @if($course->packages && $course->packages->count() > 0)
                        <div class="mb-3">
                            <label for="package_id{{ $course->id }}" class="form-label">
                                <strong>Select Package</strong> <span class="text-danger">*</span>
                            </label>
                            <select name="package_id" id="package_id{{ $course->id }}" class="form-select @error('package_id') is-invalid @enderror" required>
                                <option value="">Choose a package...</option>
                                @foreach($course->packages as $package)
                                    <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} 
                                        @if($package->transmission_type)
                                            ({{ ucfirst($package->transmission_type) }})
                                        @endif
                                        - P{{ number_format($package->price, 2) }}
                                        @if($package->training_hours)
                                            ({{ $package->training_hours }} hrs)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <!-- Additional Notes -->
                    <div class="mb-3">
                        <label for="notes{{ $course->id }}" class="form-label">Additional Notes (Optional)</label>
                        <textarea name="notes" id="notes{{ $course->id }}" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any special requests or information we should know...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Your enrollment request will be reviewed by an administrator. You'll be notified once approved.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEnrollModal({{ $course->id }})">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Enrollment Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleExperienceChange{{ $course->id }}() {
    const select = document.getElementById('experience_level{{ $course->id }}');
    const credentialSection = document.getElementById('credentialSection{{ $course->id }}');
    const courseType = '{{ $course->course_type }}';
    
    if (select.value === 'experienced_driver') {
        credentialSection.style.display = 'block';
    } else {
        credentialSection.style.display = 'none';
        // Clear file input when hiding
        document.getElementById('credential_file{{ $course->id }}').value = '';
    }
}

// Initialize on page load if there's an old value
document.addEventListener('DOMContentLoaded', function() {
    handleExperienceChange{{ $course->id }}();
});
</script>
@endforeach

<script>
// Modal control functions
function openEnrollModal(courseId) {
    const modal = document.getElementById('enrollModal' + courseId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeEnrollModal(courseId) {
    const modal = document.getElementById('enrollModal' + courseId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('.modal').forEach(function(modal) {
        if (modal.style.display === 'flex') {
            modal.style.display = 'none';
        }
    });
});
</script>

@endsection

