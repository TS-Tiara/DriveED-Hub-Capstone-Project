@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Guest Dashboard')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
@endphp

<style>
    .container {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 10px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
        font-size: 1.1rem;
    }
    
    .welcome-card {
        @if($school->schoolSetting->use_gradient_header)
            background: linear-gradient(135deg, {{ $school->schoolSetting->primary_color }} 0%, {{ $school->schoolSetting->secondary_color }} 100%);
        @else
            background: {{ $school->schoolSetting->primary_color }};
        @endif
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .welcome-card h2 {
        margin: 0 0 10px 0;
        font-size: 1.8rem;
    }
    
    .welcome-card p {
        margin: 0;
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .info-card {
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .info-card h3 {
        color: #1e40af;
        margin: 0 0 10px 0;
        font-size: 1.3rem;
    }
    
    .info-card p {
        color: #1e3a8a;
        margin: 0;
        line-height: 1.6;
    }
    
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .action-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none;
        display: block;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .action-card-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 30px;
        text-align: center;
        font-size: 3rem;
    }
    
    .action-card-body {
        padding: 20px;
        text-align: center;
    }
    
    .action-card-title {
        color: #333;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .action-card-description {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .enrollment-status {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .enrollment-status h3 {
        color: #333;
        margin-bottom: 20px;
        font-size: 1.5rem;
    }
    
    .status-item {
        background: #f9fafb;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .status-item:last-child {
        margin-bottom: 0;
    }
    
    .status-label {
        font-weight: 500;
        color: #374151;
    }
    
    .status-badge {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-approved {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .no-enrollments {
        text-align: center;
        color: #9ca3af;
        padding: 40px;
        font-size: 1.1rem;
    }
    
    .btn-primary {
        display: inline-block;
        padding: 12px 30px;
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        text-decoration: none;
        border-radius: var(--button-border-radius);
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        color: var(--btn-primary-text);
    }
</style>

<div class="container">
    <h1>Welcome, {{ auth()->guard('student')->user()->name }}!</h1>
    <p class="subtitle">You're logged in as a Guest</p>
    
    <div class="welcome-card">
        <h2>Get Started with Your Driving Journey</h2>
        <p>Browse our available courses and submit an enrollment request to become a full student.</p>
    </div>
    
    <div class="info-card">
        <h3>ℹ️ Guest Account Information</h3>
        <p>
            As a guest, you have limited access to our system. You can browse available courses and submit enrollment requests. 
            Once an administrator approves your enrollment request, you'll be upgraded to a full student account with access to 
            bookings, schedules, progress tracking, and more!
        </p>
    </div>
    
    <div class="action-grid">
        <a href="{{ route('schools.guest.courses', $school) }}" class="action-card">
            <div class="action-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            </div>
            <div class="action-card-body">
                <div class="action-card-title">Browse Courses</div>
                <div class="action-card-description">
                    Explore our available driving courses and find the perfect one for you
                </div>
            </div>
        </a>
        
        <a href="{{ route('schools.guest.enrollmentRequests', $school) }}" class="action-card">
            <div class="action-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                
            </div>
            <div class="action-card-body">
                <div class="action-card-title">My Enrollment Requests</div>
                <div class="action-card-description">
                    View the status of your course enrollment requests
                </div>
            </div>
        </a>
    </div>
    
    <div class="enrollment-status">
        <h3>Recent Enrollment Requests</h3>
        
        @php
            $recentRequests = auth()->guard('student')->user()
                ->enrollmentRequests()
                ->with('course')
                ->latest()
                ->take(3)
                ->get();
        @endphp
        
        @if($recentRequests->count() > 0)
            @foreach($recentRequests as $request)
                <div class="status-item">
                    <div>
                        <div class="status-label">{{ $request->course->title }}</div>
                        <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 4px;">
                            Requested: {{ $request->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>
            @endforeach
            
            @if(auth()->guard('student')->user()->enrollmentRequests()->count() > 3)
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('schools.guest.enrollmentRequests', $school) }}" class="btn-primary">
                        View All Requests
                    </a>
                </div>
            @endif
        @else
            <div class="no-enrollments">
                <p>You haven't submitted any enrollment requests yet.</p>
                <a href="{{ route('schools.guest.courses', $school) }}" class="btn-primary" style="margin-top: 20px;">
                    Browse Courses
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
