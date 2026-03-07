@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $student = $student ?? Auth::guard('student')->user();
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

<style>
    .profile-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 4px solid {{ $primaryColor }};
    }

    .page-title {
        font-size: 1.75rem;
        color: #1f2937;
        margin: 0;
        font-weight: 600;
    }

    .profile-card {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: relative;
    }

    .status-badge-top {
        display: none;
    }

    .profile-card-header {
        display: none;
    }

    .profile-avatar-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: #000;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }

    .profile-avatar-circle:hover .avatar-upload-overlay,
    .profile-avatar:hover .avatar-upload-overlay {
        opacity: 1;
    }

    .avatar-upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
        z-index: 10;
    }

    .avatar-upload-overlay span {
        color: white;
        font-size: 16px;
        font-weight: 500;
    }

    .profile-avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-letter {
        font-size: 80px;
        font-weight: 700;
        color: white;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 600;
        color: #000;
    }
    
    .profile-card-body {
        padding: 0 30px 30px;
    }

    .profile-field {
        display: grid;
        grid-template-columns: 140px 1fr;
        padding: 15px 0;
        border-bottom: 1px solid #e0e0e0;
        gap: 20px;
    }

    .profile-field:last-child {
        border-bottom: none;
    }

    .profile-field-label {
        font-weight: 600;
        color: #000;
        font-size: 15px;
    }

    .profile-field-value {
        color: #666;
        font-size: 15px;
    }

    .profile-actions {
        text-align: center;
        padding: 20px 30px 30px;
    }

    .btn-edit-profile {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-edit-profile:hover {
        opacity: 0.9;
    }

    .error-list-compact {
        margin: 0;
        padding-left: 20px;
    }

    .avatar-container-rel {
        position: relative;
    }

    .hidden-file-input {
        display: none;
    }

    .password-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .password-section-title {
        margin: 0 0 15px 0;
        font-size: 0.95rem;
        color: #374151;
        font-weight: 600;
    }

    .password-section-title-note {
        font-weight: 400;
        color: #9ca3af;
        font-size: 0.8rem;
    }

    .password-error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin-bottom: 12px;
    }

    .password-error-text {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 4px;
        display: block;
    }

    .edit-form {
        display: none;
        padding: 30px;
    }

    .form-field {
        margin-bottom: 20px;
    }

    .form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #000;
        font-size: 14px;
    }

    .form-field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
    }

    .form-field input:focus {
        outline: none;
        border-color: {{ $primaryColor }};
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-save {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-save:hover {
        opacity: 0.9;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: #10b981;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .profile-header {
        text-align: center;
        padding: 40px 30px 30px;
    }

    .profile-avatar {
        width: 180px;
        height: 180px;
        background: #000;
        border-radius: 50%;
        margin: 0 auto 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        border: 4px solid #f3f4f6;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-icon {
        color: white;
        font-size: 80px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .profile-info {
        padding: 0 40px 30px;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        padding: 16px 0;
        border-bottom: 1px solid #e5e7eb;
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #111827;
        font-size: 15px;
    }
    
    .info-value {
        color: #4b5563;
        font-size: 15px;
    }

    .profile-buttons {
        text-align: center;
        padding: 0 40px 40px;
    }
    
    .btn {
        padding: 12px 32px;
        margin: 0;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .btn-edit {
        background: {{ $primaryColor }};
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-edit:hover {
        background: {{ $secondaryColor }};
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }
    
    .back-button {
        background: white;
        color: {{ $primaryColor }};
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        margin-bottom: 20px;
        display: inline-block;
        border: 2px solid {{ $primaryColor }};
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .back-button:hover {
        background: {{ $primaryColor }};
        color: white;
    }

    .edit-form {
        display: none;
        padding: 40px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #111827;
        font-size: 14px;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        box-sizing: border-box;
        transition: all 0.2s;
    }

    .form-group input:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-buttons {
        text-align: center;
        margin-top: 30px;
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    
    .btn-save {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .btn-save:hover {
        background: {{ $secondaryColor }};
        transform: translateY(-1px);
    }
    
    .btn-cancel {
        background: #6b7280;
        color: white;
    }
    
    .btn-cancel:hover {
        background: #4b5563;
        transform: translateY(-1px);
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }
    
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .profile-page {
            padding: 20px 15px;
        }
        
        .profile-page-title {
            font-size: 24px;
        }
        
        .profile-page-title::after {
            left: -15px;
            width: calc(100% + 60px);
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
        }
        
        .profile-avatar-letter {
            font-size: 50px;
        }
        
        .profile-name {
            font-size: 20px;
        }
        
        .profile-header {
            padding: 30px 20px 20px;
        }
        
        .profile-card-body {
            padding: 0 20px 20px;
        }
        
        .profile-field {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 12px 0;
        }
        
        .profile-field-label {
            font-size: 14px;
            font-weight: 700;
        }
        
        .profile-field-value {
            font-size: 14px;
        }
        
        .profile-actions {
            padding: 15px 20px 20px;
        }
        
        .btn-edit-profile {
            padding: 10px 30px;
            font-size: 14px;
        }
        
        .edit-form {
            padding: 20px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-save,
        .btn-cancel {
            width: 100%;
            padding: 12px 20px;
        }
        
        .status-badge {
            top: 15px;
            left: 15px;
            font-size: 11px;
            padding: 5px 12px;
        }
    }
    
    @media (max-width: 480px) {
        .profile-page {
            padding: 15px 10px;
        }
        
        .profile-page-title {
            font-size: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border: 3px solid #f3f4f6;
        }
        
        .profile-avatar-letter {
            font-size: 42px;
        }
        
        .profile-name {
            font-size: 18px;
        }
        
        .profile-header {
            padding: 20px 15px 15px;
        }
        
        .profile-card-body {
            padding: 0 15px 15px;
        }
        
        .profile-field {
            padding: 10px 0;
        }
        
        .alert {
            padding: 12px;
            font-size: 14px;
        }
    }
</style>

<div class="profile-container">
    <div class="page-header">
        <h1 class="page-title">Profile</h1>
    </div>
    
    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="error-message">
            <ul class="error-list-compact">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-card">
        <div class="status-badge">{{ ucfirst($student->status ?? 'Active') }}</div>
        
        <div class="profile-header">
            <div class="profile-avatar avatar-container-rel" id="avatarContainer">
                @if($student->profile_picture && file_exists(public_path('storage/' . $student->profile_picture)))
                    <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="{{ $student->name }}" id="avatarImage">
                @else
                    <span class="avatar-icon" id="avatarLetter">{{ strtoupper(substr($student->name ?? 'S', 0, 1)) }}</span>
                @endif
                <div class="avatar-upload-overlay" onclick="document.getElementById('profilePictureInput').click()">
                    <span>Change Photo</span>
                </div>
            </div>
            <input type="file" id="profilePictureInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden-file-input" onchange="uploadProfilePicture(this)">
            
            <h1 class="profile-title">{{ $student->name ?? 'Student\'s Name' }}</h1>
        </div>
        
        <!-- Profile Display -->
        <div id="profileDisplay">
            <div class="profile-info">
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $student->email ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Contact:</span>
                    <span class="info-value">{{ $student->contact ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $student->address ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('F d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Branch:</span>
                    <span class="info-value">{{ $student->branchRelation?->name ?? 'Not Assigned' }}</span>
                </div>
            </div>
            
            <div class="profile-buttons" id="profileButtons">
                <button onclick="showEditForm()" class="btn btn-edit">Edit Profile</button>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div id="editForm" class="edit-form">
            <form method="POST" action="{{ school_route('student.profile.update') }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $student->name) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $student->email) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="contact">Contact:</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $student->contact) }}">
                </div>
                
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $student->address) }}">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                </div>

                <div class="password-section">
                    <h4 class="password-section-title">Change Password <span class="password-section-title-note">(optional)</span></h4>
                    
                    @error('current_password')
                        <div class="password-error-box">{{ $message }}</div>
                    @enderror

                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Min 8 chars, uppercase, lowercase, number">
                        @error('new_password')
                            <span class="password-error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password:</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password">
                    </div>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-save">Save Changes</button>
                    <button type="button" onclick="cancelEdit()" class="btn btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showEditForm() {
        document.getElementById('profileDisplay').style.display = 'none';
        document.getElementById('profileButtons').style.display = 'none';
        document.getElementById('editForm').style.display = 'block';
    }
    
    function cancelEdit() {
        document.getElementById('profileDisplay').style.display = 'block';
        document.getElementById('profileButtons').style.display = 'block';
        document.getElementById('editForm').style.display = 'none';
    }

    function uploadProfilePicture(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please upload a valid image file (PNG, JPG, JPEG, or WebP).');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB.');
            input.value = '';
            return;
        }
        
        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Show loading state
        const overlay = document.querySelector('.avatar-upload-overlay span');
        const originalText = overlay.textContent;
        overlay.textContent = 'Uploading...';
        
        fetch('{{ school_route('student.profile.picture') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update avatar image
                const avatarContainer = document.getElementById('avatarContainer');
                const existingImg = document.getElementById('avatarImage');
                const existingLetter = document.getElementById('avatarLetter');
                
                if (existingImg) {
                    existingImg.src = '/storage/' + data.path + '?t=' + new Date().getTime();
                } else if (existingLetter) {
                    existingLetter.remove();
                    const newImg = document.createElement('img');
                    newImg.src = '/storage/' + data.path + '?t=' + new Date().getTime();
                    newImg.alt = '{{ $student->name }}';
                    newImg.id = 'avatarImage';
                    newImg.style.width = '100%';
                    newImg.style.height = '100%';
                    newImg.style.objectFit = 'cover';
                    avatarContainer.insertBefore(newImg, avatarContainer.firstChild);
                }
                
                overlay.textContent = originalText;
                alert(data.message);
            } else {
                overlay.textContent = originalText;
                alert(data.message || 'Failed to upload profile picture.');
            }
        })
        .catch(error => {
            overlay.textContent = originalText;
            console.error('Error:', error);
            alert('An error occurred while uploading the profile picture.');
        })
        .finally(() => {
            input.value = '';
        });
    }
</script>
@endsection
