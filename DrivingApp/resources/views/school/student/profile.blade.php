@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Profile')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $student = Auth::guard('student')->user();
@endphp

<style>
    .profile-container {
        max-width: 600px;
        margin: 0 auto;
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
        width: 100px;
        height: 100px;
        background-color: #007bff;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-icon {
        color: white;
        font-size: 40px;
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
        text-decoration: none;
        margin-bottom: 20px;
        display: inline-block;
    }
    
    .back-button:hover {
        background: #545b62;
    }
    
    .edit-form {
        display: none;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #555;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }
    
    .form-buttons {
        text-align: center;
        margin-top: 20px;
    }
    
    .btn-save {
        background: #28a745;
        color: white;
    }
    
    .btn-save:hover {
        background: #218838;
    }
    
    .btn-cancel {
        background: #6c757d;
        color: white;
    }
    
    .btn-cancel:hover {
        background: #545b62;
    }
    
    .success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }
    
    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
</style>

<div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
    <div class="profile-container">
        <a href="{{ $schoolRoute('student.dashboard') }}" onclick="loadContent(this.href); return false;" class="back-button">← Back to Dashboard</a>
        
        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="error-message">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="profile-header">
            <h1 class="profile-title">Student Profile</h1>
            
            <div class="profile-avatar">
                <span class="avatar-icon">👨‍🎓</span>
            </div>
        </div>
        
        <!-- Profile Display -->
        <div id="profileDisplay">
            <div class="profile-info">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $student->name ?? 'N/A' }}</span>
                </div>
                
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
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ ucfirst($student->status ?? 'active') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Enrollment Date:</span>
                    <span class="info-value">{{ $student->enrollment_date ? $student->enrollment_date->format('F d, Y') : 'N/A' }}</span>
                </div>
            </div>
            
            <div class="profile-buttons" id="profileButtons">
                <button onclick="showEditForm()" class="btn btn-edit">Edit Profile</button>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div id="editForm" class="edit-form">
            <form method="POST" action="{{ $schoolRoute('student.profile.update') }}">
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
</script>
@endsection