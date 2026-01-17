@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Welcome')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $guest = auth()->guard('student')->user();
    $settings = $school->schoolSetting;
    
    // Check enrollment status
    $hasEnrollment = $guest->enrollmentRequests()->whereIn('status', ['pending', 'approved'])->exists();
    $pendingRequest = $guest->enrollmentRequests()->where('status', 'pending')->first();
    $approvedEnrollment = $guest->enrollmentRequests()->where('status', 'approved')->first();
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .guest-dashboard {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    /* Welcome Banner */
    .welcome-banner {
        @if($settings->use_gradient_header)
            background: linear-gradient(135deg, {{ $settings->primary_color }} 0%, {{ $settings->secondary_color }} 100%);
        @else
            background: {{ $settings->primary_color }};
        @endif
        color: white;
        border-radius: 16px;
        padding: 40px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .welcome-banner h1 {
        margin: 0 0 12px 0;
        font-size: 2.5rem;
        color: white;
    }
    
    .welcome-banner p {
        font-size: 1.125rem;
        opacity: 0.95;
        margin: 0;
    }
    
    /* Enrollment Status */
    .enrollment-status {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .status-message {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-radius: 10px;
        font-size: 1.125rem;
    }
    
    .status-message.enrolled {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    .status-message.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }
    
    .status-message.not-enrolled {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        color: #3730a3;
        border-left: 4px solid #6366f1;
    }
    
    .status-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    /* Section */
    .section {
        background: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 16px;
    }
    
    .section-description {
        font-size: 1rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 24px;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        border-radius: {{ $settings->button_border_radius ?? '8px' }};
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .btn-primary {
        @if(($settings->button_style ?? 'solid') === 'gradient')
        background: linear-gradient(135deg, {{ $settings->primary_color }} 0%, {{ $settings->secondary_color }} 100%);
        @else
        background: {{ $settings->primary_color }};
        @endif
        color: white;
    }
    
    .btn-primary:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-outline {
        background: white;
        color: {{ $settings->primary_color }};
        border-color: {{ $settings->primary_color }};
    }
    
    .btn-outline:hover {
        background: {{ $settings->primary_color }};
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* About Grid */
    .about-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }
    
    .about-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    
    .about-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: linear-gradient(135deg, {{ $settings->primary_color }}20 0%, {{ $settings->primary_color }}10 100%);
        color: {{ $settings->primary_color }};
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .about-content h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }
    
    .about-content p {
        font-size: 0.9375rem;
        color: #6b7280;
        line-height: 1.6;
        margin: 0;
    }
</style>

<div class="guest-dashboard">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>Welcome, {{ $guest->name }}!</h1>
        <p>Discover driving courses and start your journey with {{ $school->name }}</p>
    </div>
    
    <!-- Enrollment Status -->
    <div class="enrollment-status">
        @if($approvedEnrollment)
            <div class="status-message enrolled">
                <div class="status-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 32px; height: 32px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <strong>You're enrolled!</strong> You are enrolled in <strong>{{ $approvedEnrollment->course->title }}</strong>.
                </div>
            </div>
        @elseif($pendingRequest)
            <div class="status-message pending">
                <div class="status-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 32px; height: 32px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <strong>Request pending.</strong> Your enrollment request for <strong>{{ $pendingRequest->course->title }}</strong> is under review.
                </div>
            </div>
        @else
            <div class="status-message not-enrolled">
                <div class="status-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 32px; height: 32px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <strong>Not enrolled yet.</strong> Browse our courses and submit an enrollment request to get started!
                </div>
            </div>
        @endif
    </div>
    
    <!-- Browse Courses Section -->
    <div class="section">
        <h2 class="section-title">Available Courses</h2>
        <p class="section-description">
            Explore our comprehensive driving courses designed for all skill levels. From beginner to advanced, 
            we offer programs tailored to help you become a confident and safe driver.
        </p>
        <div class="action-buttons">
            <a href="{{ route('schools.guest.courses', $school) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Browse All Courses
            </a>
            <a href="{{ route('schools.guest.enrollmentRequests', $school) }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                My Requests
            </a>
        </div>
    </div>
    
    <!-- About Section -->
    <div class="section">
        <h2 class="section-title">About {{ $school->name }}</h2>
        <p class="section-description">
            {{ $school->description ?? 'We are committed to providing quality driving education to help you become a safe and confident driver.' }}
        </p>
        
        <div class="about-grid">
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Certified Instructors</h3>
                    <p>Learn from experienced and certified driving instructors dedicated to your success.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Flexible Scheduling</h3>
                    <p>Choose lesson times that fit your schedule with our convenient booking system.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Track Your Progress</h3>
                    <p>Monitor your learning journey with detailed progress tracking and feedback.</p>
                </div>
            </div>
            
            <div class="about-item">
                <div class="about-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="about-content">
                    <h3>Affordable Rates</h3>
                    <p>Quality driving education at competitive prices with flexible payment options.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
