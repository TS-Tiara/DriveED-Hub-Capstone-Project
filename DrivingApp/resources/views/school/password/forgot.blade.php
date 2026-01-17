@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Forgot Password')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings->primary_color ?? '#667eea';
    $secondaryColor = $settings->secondary_color ?? '#764ba2';
@endphp

<style>
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .forgot-container {
        width: 100%;
        max-width: 450px;
        margin: 20px;
    }

    .forgot-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        padding: 40px;
    }

    .logo-section {
        text-align: center;
        margin-bottom: 30px;
    }

    .logo-section h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 15px 0 10px 0;
    }

    .logo-section p {
        color: #6b7280;
        font-size: 15px;
        margin: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}20;
    }

    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-select:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px {{ $primaryColor }}20;
    }

    .btn-primary {
        width: 100%;
        padding: 14px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .btn-primary:hover {
        background: {{ $secondaryColor }};
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    .back-link a {
        color: {{ $primaryColor }};
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .back-link a:hover {
        text-decoration: underline;
    }

    .info-box {
        background: #f3f4f6;
        border-left: 4px solid {{ $primaryColor }};
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
        color: #374151;
    }
</style>

<div class="forgot-container">
    <div class="forgot-card">
        <div class="logo-section">
            <h1>🔐 Forgot Password</h1>
            <p>{{ $school->name }}</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="info-box">
            Enter your email address and select your account type. We'll send you a link to reset your password.
        </div>

        <form method="POST" action="{{ route('schools.password.email', $school) }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       placeholder="your@email.com"
                       required 
                       autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="user_type">Account Type</label>
                <select class="form-select" id="user_type" name="user_type" required>
                    <option value="">Select your account type</option>
                    <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="instructor" {{ old('user_type') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                    <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                Send Password Reset Link
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('schools.login', $school) }}">
                ← Back to Login
            </a>
        </div>
    </div>
</div>
@endsection
