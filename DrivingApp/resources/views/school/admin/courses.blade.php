@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Courses Management')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
    .courses-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #667eea;
    }

    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .page-subtitle {
        color: #666;
        font-size: 0.95rem;
        margin-top: 5px;
    }

    .btn-create {
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideIn 0.3s ease;
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

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }

    .close-btn:hover {
        opacity: 1;
    }

    /* Courses Grid */
    .courses-grid {
        display: none;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 30px;
    }

    .courses-grid.active {
        display: grid;
    }

    .course-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .course-banner {
        width: 100%;
        height: 220px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .course-content {
        padding: 25px;
    }

    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .course-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
    }

    .course-type {
        display: inline-block;
        padding: 5px 12px;
        background: #e2e8f0;
        color: #4a5568;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .course-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .course-features {
        list-style: none;
        margin-bottom: 15px;
    }

    .course-features li {
        padding: 6px 0;
        color: #4a5568;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .course-features li:before {
        content: "✓";
        color: #10b981;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .packages-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #e2e8f0;
    }

    .packages-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .packages-title {
        font-weight: 700;
        color: #2d3748;
        font-size: 1.1rem;
    }

    .btn-add-package {
        padding: 6px 12px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-add-package:hover {
        background: #059669;
    }

    .package-item {
        background: #f8fafc;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .package-info {
        flex: 1;
    }

    .package-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .package-details {
        font-size: 0.85rem;
        color: #64748b;
    }

    .package-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #667eea;
        margin-right: 15px;
    }

    .course-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }

    .btn-preview, .btn-edit, .btn-delete, .btn-package-edit, .btn-package-delete {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .btn-preview {
        background: #8b5cf6;
        color: white;
    }

    .btn-preview:hover {
        background: #7c3aed;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-edit:hover {
        background: #2563eb;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
    }

    .btn-package-edit {
        padding: 6px 12px;
        background: #3b82f6;
        color: white;
        font-size: 0.8rem;
    }

    .btn-package-delete {
        padding: 6px 12px;
        background: #ef4444;
        color: white;
        font-size: 0.8rem;
    }

    .status-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .status-active {
        background: #10b981;
        color: white;
    }

    .status-inactive {
        background: #64748b;
        color: white;
    }

    .featured-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 8px 16px;
        background: #f59e0b;
        color: white;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 700px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 25px 30px;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 0 0;
    }

    .modal-header h5 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .btn-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: white;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 30px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2d3748;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .form-check input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .features-list {
        list-style: none;
    }

    .feature-input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .feature-input-group input {
        flex: 1;
    }

    .btn-remove-feature {
        padding: 12px 16px;
        background: #ef4444;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-remove-feature:hover {
        background: #dc2626;
    }

    .btn-add-feature {
        padding: 10px 16px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s;
    }

    .btn-add-feature:hover {
        background: #059669;
    }

    .image-preview {
        margin-top: 15px;
        max-width: 100%;
        border-radius: 8px;
        display: none;
    }

    .modal-footer {
        padding: 20px 30px;
        border-top: 2px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    .btn-secondary {
        padding: 12px 24px;
        background: #64748b;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-secondary:hover {
        background: #475569;
    }

    .btn-primary {
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .empty-state i {
        font-size: 5rem;
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .empty-state p {
        font-size: 1.2rem;
        color: #64748b;
        margin-bottom: 30px;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* View Toggle Styles */
    .view-toggle {
        display: flex;
        gap: 0;
        background: #e2e8f0;
        border-radius: 10px;
        padding: 4px;
    }

    .view-toggle-btn {
        padding: 10px 20px;
        background: transparent;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .view-toggle-btn.active {
        background: white;
        color: #667eea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .view-toggle-btn:hover:not(.active) {
        color: #2d3748;
    }

    /* List View Styles */
    .courses-list {
        display: none;
        flex-direction: column;
        gap: 20px;
        width: 100%;
    }

    .courses-list.active {
        display: flex;
    }

    .courses-grid.active {
        display: grid;
    }

    .course-list-item {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        display: flex;
        gap: 25px;
        transition: all 0.3s;
        width: 100%;
    }

    .course-list-item:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateX(5px);
    }

    .course-list-banner {
        width: 280px;
        height: 160px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
    }

    .course-list-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .course-list-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .course-list-title-section {
        flex: 1;
    }

    .course-list-actions {
        display: flex;
        gap: 10px;
    }

    .course-list-info {
        display: flex;
        gap: 30px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.1rem;
        color: #2d3748;
        font-weight: 700;
    }

    .packages-inline {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .package-inline-item {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }

    .package-inline-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .package-inline-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #667eea;
    }
</style>

<div class="courses-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-mortarboard-fill"></i>
                Courses Management
            </h1>
            <p class="page-subtitle">Manage courses, packages, and pricing for {{ $schoolName }}</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div class="view-toggle">
                <button class="view-toggle-btn active" onclick="switchView('cards')">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Cards
                </button>
                <button class="view-toggle-btn" onclick="switchView('list')">
                    <i class="bi bi-list-ul"></i> List
                </button>
            </div>
            <button class="btn-create" onclick="openCreateModal()">
                <i class="bi bi-plus-circle-fill"></i>
                Create New Course
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" id="successAlert">
            {{ session('success') }}
            <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error" id="errorAlert">
            {{ session('error') }}
            <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
    @endif

    @if($courses->isEmpty())
        <div class="empty-state">
            <i class="bi bi-mortarboard"></i>
            <p>No courses created yet. Start by creating your first course!</p>
            <button class="btn-create" onclick="openCreateModal()">
                <i class="bi bi-plus-circle-fill"></i>
                Create Your First Course
            </button>
        </div>
    @else
        <div class="courses-grid">
            @foreach($courses as $course)
                <div class="course-card">
                    <div style="position: relative;">
                        @if($course->banner_image && file_exists(public_path($course->banner_image)))
                            <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}" class="course-banner">
                        @else
                            <div class="course-banner">
                                <i class="bi bi-car-front-fill"></i>
                            </div>
                        @endif
                        
                        @if($course->is_featured)
                            <span class="featured-badge">⭐ Featured</span>
                        @endif
                        
                        <span class="status-badge status-{{ $course->status }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </div>
                    
                    <div class="course-content">
                        <div class="course-header">
                            <div>
                                <h3 class="course-title">{{ $course->title }}</h3>
                                <span class="course-type">{{ $course->type }}</span>
                                @if($course->vehicle_type)
                                    <span class="course-type" style="background: #dbeafe; color: #1e40af;">{{ $course->vehicle_type }}</span>
                                @endif
                            </div>
                        </div>

                        @if($course->description)
                            <p class="course-description">{{ $course->description }}</p>
                        @endif

                        @if($course->features && count($course->features) > 0)
                            <ul class="course-features">
                                @foreach(array_slice($course->features, 0, 3) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                                @if(count($course->features) > 3)
                                    <li style="color: #667eea; font-weight: 600;">+{{ count($course->features) - 3 }} more features</li>
                                @endif
                            </ul>
                        @endif

                        @if($course->packages && $course->packages->count() > 0)
                            <div class="packages-section">
                                <div class="packages-header">
                                    <span class="packages-title">📦 Packages ({{ $course->packages->count() }})</span>
                                    <button class="btn-add-package" onclick="openPackageModal({{ $course->id }}, null)">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>
                                
                                @foreach($course->packages as $package)
                                    <div class="package-item">
                                        <div class="package-info">
                                            <div class="package-name">
                                                {{ $package->name }}
                                                @if($package->vehicle_type)
                                                    <span style="font-size: 0.8rem; padding: 2px 8px; background: #8b5cf6; color: white; border-radius: 10px; margin-left: 5px;">
                                                        {{ $package->vehicle_type }}
                                                    </span>
                                                @endif
                                                <span style="font-size: 0.8rem; padding: 2px 8px; background: {{ $package->transmission_type == 'manual' ? '#fbbf24' : '#3b82f6' }}; color: white; border-radius: 10px; margin-left: 5px;">
                                                    {{ strtoupper($package->transmission_type) }}
                                                </span>
                                                @if($package->is_popular)
                                                    <span style="font-size: 0.75rem; padding: 2px 8px; background: #f59e0b; color: white; border-radius: 10px; margin-left: 5px;">POPULAR</span>
                                                @endif
                                            </div>
                                            <div class="package-details">
                                                @if($package->training_hours) {{ $package->training_hours }} hours • @endif
                                                @if($package->description) {{ Str::limit($package->description, 40) }} @endif
                                            </div>
                                        </div>
                                        <span class="package-price">₱{{ number_format($package->price, 2) }}</span>
                                        <div style="display: flex; gap: 5px;">
                                            <button class="btn-package-edit" onclick="openPackageModal({{ $course->id }}, {{ $package->id }})">Edit</button>
                                            <button class="btn-package-delete" onclick="deletePackage({{ $course->id }}, {{ $package->id }})">Delete</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="packages-section">
                                <button class="btn-add-package" style="width: 100%;" onclick="openPackageModal({{ $course->id }}, null)">
                                    <i class="bi bi-plus-circle"></i> Add First Package
                                </button>
                            </div>
                        @endif

                        <div class="course-actions">
                            <button class="btn-preview" onclick="openPreviewModal({{ $course->id }})" title="Preview how guests see this course">
                                <i class="bi bi-eye-fill"></i> Preview
                            </button>
                            <button class="btn-edit" onclick="openEditModal({{ $course->id }})">
                                <i class="bi bi-pencil-fill"></i> Edit
                            </button>
                            <button class="btn-delete" onclick="deleteCourse({{ $course->id }})">
                                <i class="bi bi-trash-fill"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- List View -->
        <div class="courses-list">
            @foreach($courses as $course)
                <div class="course-list-item">
                    @if($course->banner_image && file_exists(public_path($course->banner_image)))
                        <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}" class="course-list-banner">
                    @else
                        <div class="course-list-banner">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                    @endif
                    
                    <div class="course-list-content">
                        <div class="course-list-header">
                            <div class="course-list-title-section">
                                <h3 class="course-title" style="margin-bottom: 10px;">{{ $course->title }}</h3>
                                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                                    <span class="course-type">{{ $course->type }}</span>
                                    @if($course->vehicle_type)
                                        <span class="course-type" style="background: #dbeafe; color: #1e40af;">{{ $course->vehicle_type }}</span>
                                    @endif
                                    <span class="status-badge status-{{ $course->status }}" style="position: static;">
                                        {{ ucfirst($course->status) }}
                                    </span>
                                    @if($course->is_featured)
                                        <span class="featured-badge" style="position: static;">⭐ Featured</span>
                                    @endif
                                </div>
                                @if($course->description)
                                    <p class="course-description" style="-webkit-line-clamp: 3; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;">{{ $course->description }}</p>
                                @endif
                                
                                @if($course->features && count($course->features) > 0)
                                    <ul class="course-features" style="margin-top: 15px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                                        @foreach($course->features as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            
                            <div class="course-list-actions">
                                <button class="btn-preview" onclick="openPreviewModal({{ $course->id }})" title="Preview how guests see this course">
                                    <i class="bi bi-eye-fill"></i> Preview
                                </button>
                                <button class="btn-edit" onclick="openEditModal({{ $course->id }})">
                                    <i class="bi bi-pencil-fill"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteCourse({{ $course->id }})">
                                    <i class="bi bi-trash-fill"></i> Delete
                                </button>
                            </div>
                        </div>

                        @if($course->packages && $course->packages->count() > 0)
                            <div class="course-list-info">
                                <div class="info-item">
                                    <span class="info-label">Total Packages</span>
                                    <span class="info-value">{{ $course->packages->count() }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Price Range</span>
                                    <span class="info-value">
                                        ₱{{ number_format($course->packages->min('price'), 0) }} - ₱{{ number_format($course->packages->max('price'), 0) }}
                                    </span>
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span class="info-label">Available Packages</span>
                                        <button class="btn-add-package" onclick="openPackageModal({{ $course->id }}, null)">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="packages-inline">
                                        @foreach($course->packages as $package)
                                            <div class="package-inline-item">
                                                <div class="package-inline-name">
                                                    {{ $package->name }}
                                                    @if($package->vehicle_type)
                                                        <span style="font-size: 0.75rem; padding: 2px 6px; background: #8b5cf6; color: white; border-radius: 8px; margin-left: 4px;">
                                                            {{ $package->vehicle_type }}
                                                        </span>
                                                    @endif
                                                    <span style="font-size: 0.75rem; padding: 2px 6px; background: {{ $package->transmission_type == 'manual' ? '#fbbf24' : '#3b82f6' }}; color: white; border-radius: 8px; margin-left: 4px;">
                                                        {{ strtoupper($package->transmission_type) }}
                                                    </span>
                                                </div>
                                                <div class="package-inline-price">₱{{ number_format($package->price, 2) }}</div>
                                                <div style="display: flex; gap: 5px; margin-top: 8px;">
                                                    <button class="btn-package-edit" onclick="openPackageModal({{ $course->id }}, {{ $package->id }})">Edit</button>
                                                    <button class="btn-package-delete" onclick="deletePackage({{ $course->id }}, {{ $package->id }})">Delete</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="course-list-info">
                                <button class="btn-add-package" style="width: 100%;" onclick="openPackageModal({{ $course->id }}, null)">
                                    <i class="bi bi-plus-circle"></i> Add First Package
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Create/Edit Course Modal -->
<div class="modal" id="courseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="courseModalTitle">Create New Course</h5>
            <button class="btn-close" onclick="closeCourseModal()">&times;</button>
        </div>
        <form id="courseForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="courseMethod" value="POST">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Course Title *</label>
                    <input type="text" name="title" id="courseTitle" class="form-control" required placeholder="e.g., Transitional Driving Programs">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="courseDescription" class="form-control" rows="4" placeholder="Describe the course details..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Image (Max 2MB)</label>
                    <input type="file" name="banner_image" id="courseBanner" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <img id="imagePreview" class="image-preview">
                    <small style="color: #64748b; display: block; margin-top: 5px;">Recommended size: 1200x400px for best results</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Course Type *</label>
                    <select name="type" id="courseType" class="form-control" required>
                        <option value="standard">Standard</option>
                        <option value="intensive">Intensive</option>
                        <option value="refresher">Refresher</option>
                        <option value="transitional">Transitional</option>
                        <option value="defensive">Defensive Driving</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Vehicle Type</label>
                    <select name="vehicle_type" id="courseVehicleType" class="form-control">
                        <option value="">Select vehicle type</option>
                        <option value="Sedan">Sedan (Vios / Mirage)</option>
                        <option value="SUV">SUV (Innova / L300)</option>
                        <option value="Hi-Lux">Hi-Lux (Pick-up Truck)</option>
                        <option value="Montero">Montero / Fortuner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Course Features</label>
                    <div id="featuresContainer">
                        <div class="feature-input-group">
                            <input type="text" name="features[]" class="form-control" placeholder="e.g., UP TO 23 HOURS OF DRIVING LESSONS">
                            <button type="button" class="btn-remove-feature" onclick="removeFeature(this)">−</button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-feature" onclick="addFeature()">+ Add Feature</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="courseStatus" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="is_featured" id="courseFeatured">
                    <label for="courseFeatured" class="form-label" style="margin-bottom: 0;">Mark as Featured Course</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCourseModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Course</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal (Guest View) -->
<div class="modal" id="previewModal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);">
            <h5><i class="bi bi-eye-fill"></i> Course Preview (Guest View)</h5>
            <button class="btn-close" onclick="closePreviewModal()">&times;</button>
        </div>
        <div class="modal-body" id="previewContent" style="padding: 0;">
            <!-- Preview content will be injected here -->
        </div>
        <div class="modal-footer" style="background: #f8fafc;">
            <button type="button" class="btn-secondary" onclick="closePreviewModal()">Close</button>
            <button type="button" class="btn-primary" onclick="closePreviewAndEdit()">
                <i class="bi bi-pencil-fill"></i> Edit Course
            </button>
        </div>
    </div>
</div>

<!-- Package Modal -->
<div class="modal" id="packageModal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 id="packageModalTitle">Add Package</h5>
            <button class="btn-close" onclick="closePackageModal()">&times;</button>
        </div>
        <form id="packageForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="packageMethod" value="POST">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Package Name *</label>
                    <input type="text" name="name" id="packageName" class="form-control" required placeholder="e.g., SPECIAL, RUSH, EXECUTIVE, Smart Basic, Smart All-In">
                </div>

                <div class="form-group">
                    <label class="form-label">Vehicle Type</label>
                    <input type="text" name="vehicle_type" id="packageVehicleType" class="form-control" placeholder="e.g., Sedan, SUV, Motorcycle, Truck">
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">Optional - Specify if this package is for a specific vehicle type</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Transmission Type *</label>
                    <select name="transmission_type" id="packageTransmission" class="form-control" required>
                        <option value="manual">Manual</option>
                        <option value="automatic">Automatic</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" id="packagePrice" class="form-control" required min="0" step="0.01" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label class="form-label">Training Hours</label>
                    <input type="number" name="training_hours" id="packageHours" class="form-control" min="0" placeholder="e.g., 23">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="packageDescription" class="form-control" rows="3" placeholder="Brief description of this package..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Package Features</label>
                    <div id="packageFeaturesContainer">
                        <div class="feature-input-group">
                            <input type="text" name="features[]" class="form-control" placeholder="e.g., UP TO 23 HOURS TRAINING">
                            <button type="button" class="btn-remove-feature" onclick="removePackageFeature(this)">−</button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-feature" onclick="addPackageFeature()">+ Add Feature</button>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="is_popular" id="packagePopular">
                    <label for="packagePopular" class="form-label" style="margin-bottom: 0;">Mark as Popular Package</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closePackageModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
    const coursesData = @json($courses);
    let currentCourseId = null;
    let currentPackageId = null;

    // Course Modal Functions
    function openCreateModal() {
        document.getElementById('courseModalTitle').textContent = 'Create New Course';
        document.getElementById('courseForm').action = '{{ route("schools.admin.courses.store", $school) }}';
        document.getElementById('courseMethod').value = 'POST';
        document.getElementById('courseForm').reset();
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('courseModal').style.display = 'flex';
    }

    function openEditModal(courseId) {
        const course = coursesData.find(c => c.id === courseId);
        if (!course) return;

        document.getElementById('courseModalTitle').textContent = 'Edit Course';
        document.getElementById('courseForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}`;
        document.getElementById('courseMethod').value = 'PUT';
        
        document.getElementById('courseTitle').value = course.title || '';
        document.getElementById('courseDescription').value = course.description || '';
        document.getElementById('courseType').value = course.type || 'standard';
        document.getElementById('courseVehicleType').value = course.vehicle_type || '';
        document.getElementById('courseStatus').value = course.status || 'active';
        document.getElementById('courseFeatured').checked = course.is_featured || false;

        // Load features
        const featuresContainer = document.getElementById('featuresContainer');
        featuresContainer.innerHTML = '';
        if (course.features && course.features.length > 0) {
            course.features.forEach(feature => {
                addFeature(feature);
            });
        } else {
            addFeature();
        }

        // Show banner preview if exists
        if (course.banner_image) {
            const preview = document.getElementById('imagePreview');
            preview.src = '{{ asset("") }}' + course.banner_image;
            preview.style.display = 'block';
        }

        document.getElementById('courseModal').style.display = 'flex';
    }

    function closeCourseModal() {
        document.getElementById('courseModal').style.display = 'none';
    }

    function deleteCourse(courseId) {
        if (!confirm('Are you sure you want to delete this course? This will also delete all associated packages.')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url($school->slug . '/admin/courses') }}/${courseId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }

    // Package Modal Functions
    function openPackageModal(courseId, packageId) {
        currentCourseId = courseId;
        currentPackageId = packageId;

        if (packageId) {
            // Edit mode
            const course = coursesData.find(c => c.id === courseId);
            const package = course.packages.find(p => p.id === packageId);
            
            document.getElementById('packageModalTitle').textContent = 'Edit Package';
            document.getElementById('packageForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages/${packageId}`;
            document.getElementById('packageMethod').value = 'PUT';
            
            document.getElementById('packageName').value = package.name || '';
            document.getElementById('packageVehicleType').value = package.vehicle_type || '';
            document.getElementById('packageTransmission').value = package.transmission_type || 'manual';
            document.getElementById('packagePrice').value = package.price || '';
            document.getElementById('packageHours').value = package.training_hours || '';
            document.getElementById('packageDescription').value = package.description || '';
            document.getElementById('packagePopular').checked = package.is_popular || false;

            // Load package features
            const featuresContainer = document.getElementById('packageFeaturesContainer');
            featuresContainer.innerHTML = '';
            if (package.features && package.features.length > 0) {
                package.features.forEach(feature => {
                    addPackageFeature(feature);
                });
            } else {
                addPackageFeature();
            }
        } else {
            // Create mode
            document.getElementById('packageModalTitle').textContent = 'Add New Package';
            document.getElementById('packageForm').action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages`;
            document.getElementById('packageMethod').value = 'POST';
            document.getElementById('packageForm').reset();
            
            const featuresContainer = document.getElementById('packageFeaturesContainer');
            featuresContainer.innerHTML = '';
            addPackageFeature();
        }

        document.getElementById('packageModal').style.display = 'flex';
    }

    function closePackageModal() {
        document.getElementById('packageModal').style.display = 'none';
        currentCourseId = null;
        currentPackageId = null;
    }

    function deletePackage(courseId, packageId) {
        if (!confirm('Are you sure you want to delete this package?')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url($school->slug . '/admin/courses') }}/${courseId}/packages/${packageId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }

    // Feature Management
    function addFeature(value = '') {
        const container = document.getElementById('featuresContainer');
        const div = document.createElement('div');
        div.className = 'feature-input-group';
        div.innerHTML = `
            <input type="text" name="features[]" class="form-control" placeholder="Enter a feature" value="${value}">
            <button type="button" class="btn-remove-feature" onclick="removeFeature(this)">−</button>
        `;
        container.appendChild(div);
    }

    function removeFeature(button) {
        const container = document.getElementById('featuresContainer');
        if (container.children.length > 1) {
            button.parentElement.remove();
        }
    }

    function addPackageFeature(value = '') {
        const container = document.getElementById('packageFeaturesContainer');
        const div = document.createElement('div');
        div.className = 'feature-input-group';
        div.innerHTML = `
            <input type="text" name="features[]" class="form-control" placeholder="Enter a feature" value="${value}">
            <button type="button" class="btn-remove-feature" onclick="removePackageFeature(this)">−</button>
        `;
        container.appendChild(div);
    }

    function removePackageFeature(button) {
        const container = document.getElementById('packageFeaturesContainer');
        if (container.children.length > 1) {
            button.parentElement.remove();
        }
    }

    // Image Preview
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const courseModal = document.getElementById('courseModal');
        const packageModal = document.getElementById('packageModal');
        
        if (event.target == courseModal) {
            closeCourseModal();
        }
        if (event.target == packageModal) {
            closePackageModal();
        }
    }

    // Preview Modal Functions
    function openPreviewModal(courseId) {
        const course = coursesData.find(c => c.id === courseId);
        if (!course) return;

        currentCourseId = courseId;

        // Build guest-view HTML
        const previewHTML = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; color: white; text-align: center;">
                ${course.banner_image ? 
                    `<img src="{{ asset('') }}${course.banner_image}" style="max-width: 600px; width: 100%; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">` : 
                    `<i class="bi bi-car-front-fill" style="font-size: 5rem; opacity: 0.8; display: block; margin-bottom: 20px;"></i>`
                }
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 15px;">${course.title}</h2>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 15px;">
                    <span style="background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 20px; font-weight: 600;">${course.type}</span>
                    ${course.vehicle_type ? `<span style="background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 20px; font-weight: 600;">${course.vehicle_type}</span>` : ''}
                    ${course.is_featured ? `<span style="background: #f59e0b; padding: 8px 20px; border-radius: 20px; font-weight: 600;">⭐ Featured</span>` : ''}
                </div>
                ${course.description ? `<p style="font-size: 1.1rem; max-width: 700px; margin: 0 auto; opacity: 0.95;">${course.description}</p>` : ''}
            </div>

            <div style="padding: 40px;">
                ${course.features && course.features.length > 0 ? `
                    <div style="margin-bottom: 40px;">
                        <h3 style="font-size: 1.8rem; font-weight: 700; color: #2d3748; margin-bottom: 25px; text-align: center;">✨ What's Included</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; max-width: 800px; margin: 0 auto;">
                            ${course.features.map(feature => `
                                <div style="background: #f8fafc; padding: 15px 20px; border-radius: 10px; border-left: 4px solid #667eea;">
                                    <i class="bi bi-check-circle-fill" style="color: #10b981; margin-right: 10px;"></i>
                                    ${feature}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}

                ${course.packages && course.packages.length > 0 ? `
                    <div>
                        <h3 style="font-size: 1.8rem; font-weight: 700; color: #2d3748; margin-bottom: 25px; text-align: center;">📦 Choose Your Package</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; max-width: 1000px; margin: 0 auto;">
                            ${course.packages.map(pkg => `
                                <div style="background: white; border: 3px solid ${pkg.is_popular ? '#f59e0b' : '#e2e8f0'}; border-radius: 12px; padding: 25px; position: relative; transition: all 0.3s; ${pkg.is_popular ? 'box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);' : ''}">
                                    ${pkg.is_popular ? `<div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #f59e0b; color: white; padding: 5px 20px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">MOST POPULAR</div>` : ''}
                                    <h4 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin-bottom: 10px; ${pkg.is_popular ? 'margin-top: 10px;' : ''}">${pkg.name}</h4>
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                                        ${pkg.vehicle_type ? `<span style="background: #8b5cf6; color: white; padding: 5px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">${pkg.vehicle_type}</span>` : ''}
                                        <span style="background: ${pkg.transmission_type === 'manual' ? '#fbbf24' : '#3b82f6'}; color: white; padding: 5px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;">
                                            ${pkg.transmission_type.toUpperCase()}
                                        </span>
                                        ${pkg.training_hours ? `<span style="color: #64748b; font-weight: 600;"><i class="bi bi-clock"></i> ${pkg.training_hours} hours</span>` : ''}
                                    </div>
                                    ${pkg.description ? `<p style="color: #64748b; margin-bottom: 20px; line-height: 1.6;">${pkg.description}</p>` : ''}
                                    ${pkg.features && pkg.features.length > 0 ? `
                                        <ul style="list-style: none; padding: 0; margin: 20px 0;">
                                            ${pkg.features.map(f => `<li style="padding: 5px 0; color: #475569;"><i class="bi bi-check-circle-fill" style="color: #10b981; margin-right: 8px;"></i>${f}</li>`).join('')}
                                        </ul>
                                    ` : ''}
                                    <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
                                        <div style="font-size: 2.5rem; font-weight: 700; color: #667eea;">₱${new Intl.NumberFormat().format(pkg.price)}</div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : '<p style="text-align: center; color: #64748b; font-size: 1.1rem;">No packages available yet.</p>'}
            </div>
        `;

        document.getElementById('previewContent').innerHTML = previewHTML;
        document.getElementById('previewModal').style.display = 'flex';
    }

    function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
        currentCourseId = null;
    }

    function closePreviewAndEdit() {
        if (currentCourseId) {
            closePreviewModal();
            setTimeout(() => openEditModal(currentCourseId), 200);
        }
    }

    // View Toggle Functions
    function switchView(view) {
        const gridView = document.querySelector('.courses-grid');
        const listView = document.querySelector('.courses-list');
        const gridBtn = document.querySelector('.view-toggle-btn:nth-child(1)');
        const listBtn = document.querySelector('.view-toggle-btn:nth-child(2)');

        if (view === 'cards' || view === 'grid') { // Support both for backward compatibility
            if (gridView) gridView.classList.add('active');
            if (listView) listView.classList.remove('active');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
            localStorage.setItem('coursesView', 'cards');
        } else {
            if (gridView) gridView.classList.remove('active');
            if (listView) listView.classList.add('active');
            gridBtn.classList.remove('active');
            listBtn.classList.add('active');
            localStorage.setItem('coursesView', 'list');
        }
    }

    // Restore view preference on load
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('coursesView') || 'cards';
        switchView(savedView);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
</script>

@endsection
