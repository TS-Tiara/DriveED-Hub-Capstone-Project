@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Student Reports')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $school->schoolSetting->primary_color ?? '#667eea';
@endphp

<div style="padding: 20px; margin: 20px auto; max-width: 1600px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid {{ $primaryColor }};">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 600; color: #1f2937; margin: 0;">Student Reports</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">Student performance and progress metrics for {{ $schoolName }}</p>
        </div>
    </div>
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 60px 40px; text-align: center;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, {{ $primaryColor }} 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; color: #1f2937; margin-bottom: 10px;">Student Reports Coming Soon</h3>
        <p style="color: #6b7280; max-width: 400px; margin: 0 auto;">Comprehensive student performance tracking and reporting features will be available here.</p>
    </div>
</div>
@endsection