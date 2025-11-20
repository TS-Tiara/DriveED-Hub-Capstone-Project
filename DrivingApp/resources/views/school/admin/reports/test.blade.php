@extends('layouts.app')

@section('title', 'Reports Test')

@section('content')
<div style="padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h1 style="color: #27ae60; margin-bottom: 20px;">✅ Reports Page Loaded Successfully!</h1>
    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 15px;">
        <strong>Data from Controller:</strong>
        <p>Total Students: <strong style="color: #3498db;">{{ $analytics['total_students'] ?? 'N/A' }}</strong></p>
        <p>Active Students: <strong style="color: #27ae60;">{{ $analytics['active_students'] ?? 'N/A' }}</strong></p>
        <p>Total Instructors: <strong style="color: #e67e22;">{{ $analytics['total_instructors'] ?? 'N/A' }}</strong></p>
        <p>Bookings This Month: <strong style="color: #9b59b6;">{{ $analytics['total_bookings_this_month'] ?? 'N/A' }}</strong></p>
    </div>
    <div style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60;">
        <strong>✓ Page loaded at: {{ now()->format('Y-m-d H:i:s') }}</strong>
    </div>
</div>
@endsection
