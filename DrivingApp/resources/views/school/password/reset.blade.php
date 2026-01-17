@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Reset Password')

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

    .reset-container {
        width: 100%;
        max-width: 450px;
        margin: 20px;
    }

    .reset-card {
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

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .password-strength {
        margin-top: 8px;
        font-size: 12px;
    }

    .strength-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        margin-top: 8px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
        background: #dc2626;
    }

    .strength-weak { width: 33%; background: #dc2626; }
    .strength-medium { width: 66%; background: #f59e0b; }
    .strength-strong { width: 100%; background: #10b981; }
</style>

<div class="reset-container">
    <div class="reset-card">
        <div class="logo-section">
            <h1>🔑 Reset Password</h1>
            <p>{{ $school->name }}</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('schools.password.update', $school) }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="user_type" value="{{ $type }}">

            <div class="form-group">
                <label class="form-label" for="email_display">Email Address</label>
                <input type="email" 
                       class="form-control" 
                       id="email_display" 
                       value="{{ $email }}"
                       disabled>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       placeholder="Enter new password (min. 8 characters)"
                       required
                       minlength="8">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthBar"></div>
                </div>
                <div class="password-strength" id="strengthText"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Re-enter your new password"
                       required
                       minlength="8">
            </div>

            <button type="submit" class="btn-primary">
                Reset Password
            </button>
        </form>
    </div>
</div>

<script>
    // Password strength checker
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;

        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        strengthBar.className = 'strength-fill';
        
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
            strengthText.textContent = 'Password strength: Weak';
            strengthText.style.color = '#dc2626';
        } else if (strength <= 3) {
            strengthBar.classList.add('strength-medium');
            strengthText.textContent = 'Password strength: Medium';
            strengthText.style.color = '#f59e0b';
        } else {
            strengthBar.classList.add('strength-strong');
            strengthText.textContent = 'Password strength: Strong';
            strengthText.style.color = '#10b981';
        }
    });
</script>
@endsection
