@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Browse Courses')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;

    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#8b5cf6';
@endphp

<style>
    .courses-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .courses-header {
        margin-bottom: 20px;
        border-bottom: 4px solid {{ $primaryColor }};
        padding-bottom: 15px;
    }
    
    .courses-header h1 {
        font-size: 2rem;
        font-weight: 400;
        margin: 0;
        color: #1a202c;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    
    .course-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s;
    }
    
    .course-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    
    .course-banner {
        height: 160px;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .course-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-banner-icon {
        color: rgba(255,255,255,0.9);
    }
    
    .featured-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .course-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .badge-type {
        padding: 4px 10px;
        background: #e0e7ff;
        color: #3730a3;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-vehicle {
        padding: 4px 10px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .course-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 10px 0;
    }
    
    .course-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
    }
    
    .course-features {
        list-style: none;
        padding: 0;
        margin: 0 0 15px 0;
    }
    
    .course-features li {
        padding: 5px 0 5px 22px;
        position: relative;
        color: #374151;
        font-size: 0.85rem;
    }
    
    .course-features li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        background: {{ $primaryColor }};
        border-radius: 50%;
    }
    
    .course-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .info-value {
        font-weight: 600;
        color: #1e293b;
    }
    
    .packages-section {
        background: #fafafa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #e5e7eb;
    }
    
    .packages-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .package-item {
        background: white;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .package-item:last-child {
        margin-bottom: 0;
    }
    
    .package-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .package-tag {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .tag-manual {
        background: #fef3c7;
        color: #92400e;
    }
    
    .tag-automatic {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .tag-popular {
        background: #fef3c7;
        color: #b45309;
    }
    
    .package-details {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .package-price {
        font-weight: 700;
        color: {{ $primaryColor }};
        font-size: 1rem;
    }
    
    .more-packages {
        text-align: center;
        color: {{ $primaryColor }};
        font-weight: 600;
        font-size: 0.8rem;
        padding-top: 6px;
    }
    
    .course-warning {
        background: #fef3c7;
        color: #92400e;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin-bottom: 10px;
        text-align: center;
        font-weight: 600;
    }
    
    .btn-enroll {
        width: 100%;
        padding: 12px;
        background: {{ $primaryColor }};
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: opacity 0.2s;
        margin-top: auto;
    }
    
    .btn-enroll:hover {
        opacity: 0.9;
    }
    
    .btn-enroll:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
    
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    .empty-state svg {
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .empty-state p {
        margin: 5px 0;
    }
    
    @media (max-width: 768px) {
        .courses-container {
            padding: 15px;
        }
        
        .courses-header h1 {
            font-size: 1.5rem;
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .course-banner {
            height: 140px;
        }
        
        .course-body {
            padding: 15px;
        }
        
        .package-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .package-price {
            align-self: flex-end;
        }
    }
</style>

<div class="courses-container">
    <div class="courses-header">
        <h1>Available Courses</h1>
    </div>

    <div class="courses-grid">
        @php $activeCourses = $courses->where('status', 'active'); @endphp
        
        @forelse($activeCourses as $course)
        <div class="course-card">
            <div class="course-banner">
                @if($course->banner_image && file_exists(public_path($course->banner_image)))
                    <img src="{{ asset($course->banner_image) }}" alt="{{ $course->title }}">
                @else
                    <svg class="course-banner-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679c.033.161.049.325.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.807.807 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6ZM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17 1.247 0 3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z"/>
                    </svg>
                @endif
                
                @if($course->is_featured)
                    <span class="featured-badge">Featured</span>
                @endif
            </div>
            
            <div class="course-body">
                <div class="course-badges">
                    <span class="badge-type">{{ ucfirst($course->type ?? 'Standard') }}</span>
                    @if($course->vehicle_type)
                        <span class="badge-vehicle">{{ $course->vehicle_type }}</span>
                    @endif
                </div>
                
                <h3 class="course-title">{{ $course->title }}</h3>
                
                @if($course->description)
                    <p class="course-description">{{ Str::limit($course->description, 120) }}</p>
                @endif
                
                @php $features = $course->features; @endphp
                @if($features && is_array($features) && count($features) > 0)
                    <ul class="course-features">
                        @foreach(array_slice($features, 0, 3) as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                        @if(count($features) > 3)
                            <li style="color: {{ $primaryColor }}; font-weight: 600;">+{{ count($features) - 3 }} more</li>
                        @endif
                    </ul>
                @endif
                
                @if($course->packages && $course->packages->count() > 0)
                    <div class="packages-section">
                        <div class="packages-title">Available Packages</div>
                        @foreach($course->packages->take(2) as $package)
                            <div class="package-item">
                                <div>
                                    <div class="package-name">
                                        {{ $package->name }}
                                        @if($package->transmission_type)
                                            <span class="package-tag tag-{{ $package->transmission_type }}">{{ strtoupper($package->transmission_type) }}</span>
                                        @endif
                                        @if($package->is_popular)
                                            <span class="package-tag tag-popular">POPULAR</span>
                                        @endif
                                    </div>
                                    <div class="package-details">
                                        @if($package->training_hours){{ $package->training_hours }} hours @endif
                                    </div>
                                </div>
                                <span class="package-price">P{{ number_format($package->price, 2) }}</span>
                            </div>
                        @endforeach
                        @if($course->packages->count() > 2)
                            <div class="more-packages">+{{ $course->packages->count() - 2 }} more packages</div>
                        @endif
                    </div>
                @else
                    <div class="course-info">
                        <div class="info-row">
                            <span class="info-label">Duration</span>
                            <span class="info-value">{{ $course->duration_hours }} hours</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Price</span>
                            <span class="info-value">P{{ number_format($course->price, 2) }}</span>
                        </div>
                    </div>
                @endif

                @if($course->isFull())
                    <div class="course-warning">Course is currently full</div>
                    <button class="btn-enroll" disabled>Course Full</button>
                @elseif($course->max_students && $course->availableSlots() <= 3 && $course->availableSlots() > 0)
                    <div class="course-warning">Only {{ $course->availableSlots() }} slot{{ $course->availableSlots() == 1 ? '' : 's' }} left!</div>
                    <button class="btn-enroll" onclick="bookCourse({{ $course->id }})">Enroll Now</button>
                @else
                    <button class="btn-enroll" onclick="bookCourse({{ $course->id }})">Enroll Now</button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5Z"/>
                <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Z"/>
            </svg>
            <p style="font-size: 1.1rem;">No courses available at the moment</p>
            <p style="font-size: 0.9rem;">Please check back later for new courses.</p>
        </div>
        @endforelse
    </div>
</div>

<script>
function bookCourse(courseId) {
    const schoolSlug = '{{ $school->slug }}';
    window.location.href = '/' + schoolSlug + '/student/courses/' + courseId;
}
</script>
@endsection
