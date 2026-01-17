@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Instructor Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $instructor = Auth::guard('instructor')->user();
    $settings = $school?->schoolSetting;
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
        border-bottom: 3px solid {{ $settings->primary_color ?? '#667eea' }};
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

    .profile-card-header {
        text-align: center;
        padding: 40px 30px 30px;
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

    .profile-avatar-circle:hover .avatar-upload-overlay {
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
    }

    .avatar-upload-overlay span {
        color: white;
        font-size: 14px;
        font-weight: 600;
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
        background: #007bff;
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
        background: #0056b3;
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
        border-color: {{ $settings->primary_color ?? '#667eea' }};
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-save {
        background: {{ $settings->primary_color ?? '#667eea' }};
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

    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .profile-container {
            padding: 15px;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .profile-card {
            margin: 0 auto;
        }

        .profile-card-header {
            padding: 30px 20px 20px;
        }

        .profile-avatar-circle {
            width: 140px;
            height: 140px;
        }

        .profile-avatar-letter {
            font-size: 60px;
        }

        .profile-name {
            font-size: 20px;
        }

        .profile-card-body {
            padding: 0 20px 20px;
        }

        .profile-field {
            grid-template-columns: 1fr;
            gap: 5px;
            padding: 12px 0;
        }

        .profile-field-label {
            font-size: 13px;
            color: #666;
        }

        .profile-field-value {
            font-size: 14px;
        }

        .profile-actions {
            padding: 15px 20px 25px;
        }

        .btn-edit-profile {
            width: 100%;
            padding: 12px 20px;
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
        }

        .alert {
            margin-left: 0;
            margin-right: 0;
            max-width: 100%;
        }

        .status-badge-top {
            top: 15px;
            left: 15px;
            font-size: 11px;
            padding: 5px 10px;
        }
    }

    @media (max-width: 480px) {
        .profile-container {
            padding: 10px;
        }

        .page-title {
            font-size: 1.25rem;
        }

        .profile-card-header {
            padding: 25px 15px 15px;
        }

        .profile-avatar-circle {
            width: 110px;
            height: 110px;
        }

        .profile-avatar-letter {
            font-size: 48px;
        }

        .profile-name {
            font-size: 18px;
        }

        .profile-card-body {
            padding: 0 15px 15px;
        }

        .profile-field {
            padding: 10px 0;
        }

        .profile-field-label,
        .profile-field-value {
            font-size: 13px;
        }
    }
</style>

<div class="profile-container">
    <div class="page-header">
        <h1 class="page-title">Profile</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-card">
        <div class="status-badge-top">Active</div>
        
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
                <input type="file" id="profilePictureInput" accept="image/png,image/jpg,image/jpeg,image/webp" style="display: none;" onchange="uploadProfilePicture(this)">
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
                    <div class="profile-field-label">Specialization:</div>
                    <div class="profile-field-value">{{ $instructor->specialization ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Experience:</div>
                    <div class="profile-field-value">{{ $instructor->experience ?? 'N/A' }}</div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">License Number:</div>
                    <div class="profile-field-value">{{ $instructor->license_number ?? 'N/A' }}</div>
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
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $instructor->contact) }}">
                </div>

                <div class="form-field">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" value="{{ old('specialization', $instructor->specialization) }}">
                </div>

                <div class="form-field">
                    <label for="experience">Experience</label>
                    <input type="text" id="experience" name="experience" value="{{ old('experience', $instructor->experience) }}">
                </div>

                <div class="form-field">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" value="{{ old('license_number', $instructor->license_number) }}">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">Save Changes</button>
                    <button type="button" class="btn-cancel" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showEditForm() {
        document.getElementById('profileView').style.display = 'none';
        document.getElementById('editForm').style.display = 'block';
    }

    function hideEditForm() {
        document.getElementById('profileView').style.display = 'block';
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
