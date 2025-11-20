@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Reports')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
@endphp

<div style="padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px;">
    <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f1c40f;">
        <h1 style="font-size: 2rem; color: #333; margin: 0;">Student Reports - {{ $schoolName }}</h1>
    </div>
    
    <p style="font-size: 16px; color: #666; margin-bottom: 20px;">
        This is where the admin can review student performance and progress metrics for {{ $schoolName }}.
    </p>
    
    <div style="text-align: center; padding: 40px; color: #999;">
        <h3>Student Reports Coming Soon</h3>
        <p>Comprehensive student performance tracking and reporting features will be available here.</p>
    </div>
</div>
@endsection
@endsection