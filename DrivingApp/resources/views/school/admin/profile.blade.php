@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $admin = Auth::guard('admin')->user();
@endphp
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px auto;
            max-width: 600px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-color, #2563eb);
        }
        
        .profile-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-title {
            color: #333;
            margin: 0;
            font-size: 28px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: #f0f0f0;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 3px solid #ddd;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-icon {
            color: #999;
            font-size: 60px;
        }
        
        .avatar-upload-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
        }
        
        .profile-avatar:hover .avatar-upload-overlay {
            opacity: 1;
        }
        
        #profilePictureInput {
            display: none;
        }
        
        .profile-info {
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .profile-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
        
        .back-button {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
        
        .buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .edit-form {
            display: none;
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Profile</h1>
</div>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar" onclick="document.getElementById('profilePictureInput').click()">
            @if($admin->profile_picture)
                <img src="{{ asset('storage/' . $admin->profile_picture) }}" alt="Profile Picture">
            @else
                <div class="avatar-icon">👤</div>
            @endif
            <div class="avatar-upload-overlay">
                📷 Change Photo
            </div>
        </div>
        
        <input type="file" id="profilePictureInput" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="uploadProfilePicture(this)">
        
        <div style="font-size: 24px; font-weight: bold; margin-top: 10px;">{{ $admin->name }}</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="profile-info" id="profileDisplay">
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $admin->email }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Contact:</div>
                <div class="info-value">{{ $admin->contact ?? 'Not provided' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Role:</div>
                <div class="info-value">Administrator</div>
            </div>
        </div>

        <div class="edit-form" id="editForm">
            <form method="POST" action="{{ $schoolRoute('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact', $admin->contact) }}" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                </div>

                <div class="form-group">
                    <label for="current_password">Current Password (leave blank to keep current)</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password to change">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 6 characters)">
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirm new password">
                </div>

                <div class="buttons">
                    <button type="button" onclick="cancelEdit()" class="back-button">Cancel</button>
                    <button type="submit" class="btn btn-edit">Update Profile</button>
                </div>
            </form>
        </div>

        <div class="buttons" id="profileButtons">
            <a href="{{ $schoolRoute('admin.dashboard') }}" onclick="loadContent(this.href); return false;" class="back-button">← Back to Dashboard</a>
            <button onclick="showEditForm()" class="btn btn-edit">Edit Profile</button>
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
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('profile_picture', input.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'POST');
            
            fetch('{{ $schoolRoute("admin.profile.picture") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to show the new profile picture
                    location.reload();
                } else {
                    alert(data.message || 'Error uploading profile picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error uploading profile picture');
            });
        }
    }
</script>
@endsection