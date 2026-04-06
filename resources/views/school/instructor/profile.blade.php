@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $instructor = Auth::guard('instructor')->user();
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    .profile-card {
        max-width: 580px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        position: relative;
        overflow: hidden;
    }

    .status-badge-top {
        position: absolute;
        top: 16px;
        left: 16px;
        background: #10b981;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .profile-card-header {
        text-align: center;
        padding: 40px 30px 24px;
    }

    .profile-avatar-circle {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: {{ $primaryColor }};
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .profile-avatar-circle:hover .avatar-upload-overlay { opacity: 1; }

    .avatar-upload-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        cursor: pointer;
    }

    .avatar-upload-overlay span { color: white; font-size: 0.82rem; font-weight: 600; }

    .profile-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    .profile-avatar-letter { font-size: 64px; font-weight: 700; color: white; }
    .profile-name { font-size: 1.35rem; font-weight: 600; color: #1f2937; }

    .profile-card-body { padding: 0 30px 24px; }

    .profile-field {
        display: grid;
        grid-template-columns: 130px 1fr;
        padding: 13px 0;
        border-bottom: 1px solid #f3f4f6;
        gap: 16px;
    }

    .profile-field:last-child { border-bottom: none; }
    .profile-field-label { font-weight: 600; color: #374151; font-size: 0.88rem; }
    .profile-field-value { color: #6b7280; font-size: 0.88rem; }

    .profile-actions { text-align: center; padding: 16px 30px 28px; }

    .btn-edit-profile {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 11px 36px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-edit-profile:hover { background: {{ $secondaryColor }}; transform: translateY(-1px); }

    .edit-form { display: none; padding: 28px; }

    .form-field { margin-bottom: 18px; }
    .form-field label { display: block; font-weight: 600; margin-bottom: 6px; color: #374151; font-size: 0.85rem; }

    .form-field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-field input:focus { border-color: {{ $primaryColor }}; }

    .form-actions { display: flex; gap: 10px; justify-content: center; margin-top: 24px; }

    .btn-save-profile {
        background: {{ $primaryColor }};
        color: white;
        border: none;
        padding: 11px 28px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-save-profile:hover { background: {{ $secondaryColor }}; }

    .btn-cancel-profile {
        background: #6b7280;
        color: white;
        border: none;
        padding: 11px 28px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel-profile:hover { background: #4b5563; }

    .alert {
        padding: 14px 16px;
        margin-bottom: 20px;
        border-radius: 10px;
        max-width: 580px;
        margin-left: auto;
        margin-right: auto;
        font-size: 0.88rem;
    }

    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .error-list-compact { margin: 0; padding-left: 20px; }
    .hidden-file-input { display: none; }
    .password-section { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    .password-section-title { margin: 0 0 15px 0; font-size: 0.95rem; color: #374151; font-weight: 600; }
    .password-section-note { font-weight: 400; color: #9ca3af; font-size: 0.8rem; }
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

    .btn-password-toggle {
        width: 100%;
        background: #f3f4f6;
        color: #1f2937;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.86rem;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
    }

    .btn-password-toggle:hover {
        background: #e5e7eb;
    }

    .password-fields.hidden {
        display: none;
    }

    @media (max-width: 768px) {
        .profile-avatar-circle { width: 120px; height: 120px; }
        .profile-avatar-letter { font-size: 50px; }
        .profile-name { font-size: 1.15rem; }
        .profile-card-body { padding: 0 20px 20px; }
        .profile-field { grid-template-columns: 1fr; gap: 4px; padding: 10px 0; }
        .profile-field-label { font-size: 0.78rem; color: #9ca3af; }
        .btn-edit-profile { width: 100%; }
        .form-actions { flex-direction: column; }
        .btn-save-profile, .btn-cancel-profile { width: 100%; }
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">Profile</h1>
            <p class="page-subtitle">View and manage your profile information</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul class="error-list-compact">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <div class="profile-card">
        <div class="status-badge-top">{{ ucfirst($instructor->status ?? 'Active') }}</div>
        
        <div id="profileView">
            <div class="profile-card-header">
                <div class="profile-avatar-circle" id="avatarContainer">
                    @if($instructor->profile_picture && file_exists(public_path('storage/' . $instructor->profile_picture)))
                        <img src="{{ asset('storage/' . $instructor->profile_picture) }}" alt="{{ $instructor->name }}" id="avatarImage">
                    @else
                        <span class="profile-avatar-letter" id="avatarLetter">{{ strtoupper(substr($instructor->name ?? 'I', 0, 1)) }}</span>
                    @endif
                    <div class="avatar-upload-overlay" onclick="document.getElementById('profilePictureInput').click()">
                        <span>Change Photo</span>
                    </div>
                </div>
                <input type="file" id="profilePictureInput" accept="image/png,image/jpg,image/jpeg,image/webp" class="hidden-file-input" onchange="uploadProfilePicture(this)">
                <div class="profile-name">{{ $instructor->name ?? "Instructor's Name" }}</div>
            </div>

            <div class="profile-card-body">
                <div class="profile-field">
                    <div class="profile-field-label">Email:</div>
                    <div class="profile-field-value">{{ $instructor->email ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Contact:</div>
                    <div class="profile-field-value">{{ $instructor->contact ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Availability:</div>
                    <div class="profile-field-value">{{ ucfirst(str_replace('_', ' ', $instructor->availability ?? 'Not Set')) }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Address:</div>
                    <div class="profile-field-value">{{ $instructor->address ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">License Number:</div>
                    <div class="profile-field-value">{{ $instructor->license_number ?? 'N/A' }}</div>
                </div>
                <div class="profile-field">
                    <div class="profile-field-label">Branch:</div>
                    <div class="profile-field-value">{{ $instructor->branch->name ?? 'Not Assigned' }}</div>
                </div>
            </div>

            <div class="profile-actions">
                <button type="button" class="btn-edit-profile" onclick="showEditForm()">Edit Profile</button>
            </div>
        </div>

        <div id="editForm" class="edit-form">
            <form method="POST" action="{{ $schoolRoute('instructor.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $instructor->name) }}" required>
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $instructor->email) }}" required>
                </div>

                <div class="form-field">
                    <label for="contact">Contact</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $instructor->contact) }}" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="15">
                </div>

                <div class="form-field">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $instructor->address) }}">
                </div>

                <div class="form-field">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" value="{{ old('license_number', $instructor->license_number) }}">
                </div>

                <div class="password-section">
                    <h4 class="password-section-title">Password <span class="password-section-note">(optional)</span></h4>

                    <div class="form-field">
                        <button type="button" class="btn-password-toggle" id="instructorPasswordToggleBtn" onclick="toggleInstructorPasswordFields()">
                            Change Password
                        </button>
                    </div>

                    <div class="password-fields hidden" id="instructorPasswordFields">
                        @error('current_password')
                            <div class="password-error-box">{{ $message }}</div>
                        @enderror

                        <div class="form-field">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password" autocomplete="current-password">
                        </div>

                        <div class="form-field">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Min 8 chars, uppercase, lowercase, number" autocomplete="new-password" oninput="handleInstructorNewPasswordInput()">
                            @error('new_password')
                                <span class="password-error-text">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field" id="instructorConfirmPasswordField" style="display: none;">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password" autocomplete="new-password">
                            @error('new_password_confirmation')
                                <span class="password-error-text">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save-profile">Save Changes</button>
                    <button type="button" class="btn-cancel-profile" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function enforceNumericOnly(input) {
        if (!input) return;
        const sanitize = function() {
            input.value = input.value.replace(/\D+/g, '');
        };
        input.addEventListener('input', sanitize);
        input.addEventListener('paste', function() { setTimeout(sanitize, 0); });
    }

    function showEditForm() {
        document.getElementById('profileView').style.display = 'none';
        document.getElementById('editForm').style.display = 'block';
    }

    function hideEditForm() {
        document.getElementById('profileView').style.display = 'block';
        document.getElementById('editForm').style.display = 'none';
        closeInstructorPasswordFields();
    }

    function toggleInstructorPasswordFields(forceOpen = null) {
        const fields = document.getElementById('instructorPasswordFields');
        const button = document.getElementById('instructorPasswordToggleBtn');
        if (!fields || !button) return;

        const shouldOpen = forceOpen === null ? fields.classList.contains('hidden') : forceOpen;

        if (shouldOpen) {
            fields.classList.remove('hidden');
            button.textContent = 'Hide Password Change';
        } else {
            closeInstructorPasswordFields();
        }

        handleInstructorNewPasswordInput();
    }

    function handleInstructorNewPasswordInput() {
        const newPassword = document.getElementById('new_password');
        const currentPassword = document.getElementById('current_password');
        const confirmField = document.getElementById('instructorConfirmPasswordField');
        const confirmInput = document.getElementById('new_password_confirmation');

        if (!newPassword || !currentPassword || !confirmField || !confirmInput) return;

        const hasNewPassword = newPassword.value.trim().length > 0;
        confirmField.style.display = hasNewPassword ? 'block' : 'none';
        currentPassword.required = hasNewPassword;
        confirmInput.required = hasNewPassword;
    }

    function closeInstructorPasswordFields() {
        const fields = document.getElementById('instructorPasswordFields');
        const button = document.getElementById('instructorPasswordToggleBtn');
        const currentPassword = document.getElementById('current_password');
        const newPassword = document.getElementById('new_password');
        const confirmInput = document.getElementById('new_password_confirmation');
        const confirmField = document.getElementById('instructorConfirmPasswordField');

        if (fields) fields.classList.add('hidden');
        if (button) button.textContent = 'Change Password';

        if (currentPassword) {
            currentPassword.value = '';
            currentPassword.required = false;
        }
        if (newPassword) {
            newPassword.value = '';
        }
        if (confirmInput) {
            confirmInput.value = '';
            confirmInput.required = false;
        }
        if (confirmField) {
            confirmField.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        enforceNumericOnly(document.getElementById('contact'));

        const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
        const hasPasswordErrors = {{ ($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation')) ? 'true' : 'false' }};

        if (hasErrors) {
            showEditForm();
        }

        if (hasPasswordErrors) {
            toggleInstructorPasswordFields(true);
            const confirmField = document.getElementById('instructorConfirmPasswordField');
            if (confirmField) confirmField.style.display = 'block';
        }

        handleInstructorNewPasswordInput();
    });

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
        
        fetch('{{ $schoolRoute('instructor.profile.picture') }}', {
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
                    newImg.alt = '{{ $instructor->name }}';
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
