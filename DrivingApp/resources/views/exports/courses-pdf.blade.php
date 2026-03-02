<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        .header h1 { color: #667eea; margin: 5px 0; font-size: 24px; }
        .header p { color: #666; margin: 3px 0; }
        .info-box { background: #f3f4f6; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .course-card { border: 1px solid #e5e7eb; border-radius: 8px; margin: 15px 0; overflow: hidden; page-break-inside: avoid; }
        .course-header { background: #667eea; color: white; padding: 12px 15px; font-weight: 600; font-size: 14px; }
        .course-body { padding: 15px; }
        .course-type { display: inline-block; padding: 3px 8px; background: #e0e7ff; color: #3730a3; border-radius: 3px; font-size: 10px; font-weight: 600; margin-bottom: 10px; }
        .course-description { color: #4b5563; font-size: 11px; margin: 10px 0; line-height: 1.5; }
        .course-details { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 5px; }
        .detail-item { text-align: center; }
        .detail-value { font-size: 14px; font-weight: bold; color: #1f2937; }
        .detail-label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        .packages-section { margin-top: 15px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .packages-title { font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 11px; }
        .package-item { display: inline-block; padding: 5px 10px; background: #f0f4ff; border: 1px solid #c7d2fe; border-radius: 5px; margin: 3px; }
        .package-name { font-weight: 600; font-size: 10px; color: #1e40af; }
        .package-price { font-size: 11px; color: #059669; font-weight: 600; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        <p>Courses Catalog</p>
        <p>Generated: {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    <div class="info-box">
        <strong>Total Courses:</strong> {{ $courses->count() }} |
        <strong>Active:</strong> {{ $courses->where('is_active', true)->count() }} |
        <strong>Inactive:</strong> {{ $courses->where('is_active', false)->count() }}
    </div>

    @foreach($courses as $course)
    <div class="course-card">
        <div class="course-header">
            {{ $course->title }}
        </div>
        <div class="course-body">
            <span class="course-type">{{ ucfirst($course->course_type ?? 'Standard') }}</span>
            
            @if($course->description)
            <div class="course-description">
                {{ $course->description }}
            </div>
            @endif
            
            <div class="course-details">
                <div class="detail-item">
                    <div class="detail-value">{{ $course->total_sessions ?? 0 }}</div>
                    <div class="detail-label">Sessions</div>
                </div>
                <div class="detail-item">
                    <div class="detail-value">{{ $course->hours_per_session ?? 0 }}h</div>
                    <div class="detail-label">Per Session</div>
                </div>
                <div class="detail-item">
                    <div class="detail-value">{{ ($course->total_sessions ?? 0) * ($course->hours_per_session ?? 0) }}h</div>
                    <div class="detail-label">Total Hours</div>
                </div>
                <div class="detail-item">
                    <div class="detail-value">{{ $course->is_active ? '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Active' : '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> Inactive' }}</div>
                    <div class="detail-label">Status</div>
                </div>
            </div>
            
            @if($course->packages && $course->packages->count() > 0)
            <div class="packages-section">
                <div class="packages-title">Available Packages:</div>
                @foreach($course->packages as $package)
                <div class="package-item">
                    <span class="package-name">{{ $package->name }}</span>
                    <span class="package-price">- ₱{{ number_format($package->price, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>This report is confidential and for internal use only.</p>
        <p>&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
    </div>
</body>
</html>
