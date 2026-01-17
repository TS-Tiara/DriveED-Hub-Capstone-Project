@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'System Logs')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $primaryColor = $settings->primary_color ?? '#667eea';
@endphp

<div style="padding: 20px; margin: 20px auto; max-width: 1600px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid {{ $primaryColor }};">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 600; color: #1f2937; margin: 0;">System Logs</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin-top: 5px;">Recent activities and audit logs for {{ $schoolName }}</p>
        </div>
    </div>
    
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 60px 40px; text-align: center;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, {{ $primaryColor }} 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
        </div>
        <h3 style="font-size: 1.25rem; color: #1f2937; margin-bottom: 10px;">System Logs Coming Soon</h3>
        <p style="color: #6b7280; max-width: 400px; margin: 0 auto;">Activity tracking and audit logs for your school will be available here.</p>
    </div>
</div>
@endsection