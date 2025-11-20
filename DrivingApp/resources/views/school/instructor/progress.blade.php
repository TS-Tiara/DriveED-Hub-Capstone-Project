@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Update Student Progress')

@section('content')
@php
    $schoolName = $school->name ?? 'Driving School';
@endphp

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: white;
    padding: 20px;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 30px;
}

.progress-grid {
    display: grid;
    gap: 25px;
}

.progress-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
}

.student-info h3 {
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 5px;
}

.student-info p {
    color: #666;
    margin: 0;
}

.progress-percentage {
    font-size: 2rem;
    font-weight: 700;
    color: #f59e0b;
}

.progress-bar-container {
    width: 100%;
    height: 30px;
    background: #e5e7eb;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 15px;
    transition: width 0.5s ease;
}

.progress-bar-text {
    color: white;
    font-weight: 600;
}

.progress-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item .label {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 600;
}

.detail-item .value {
    color: #1f2937;
    font-weight: 500;
}

.notes-section {
    padding: 15px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 6px;
}

.notes-section h4 {
    margin: 0 0 8px 0;
    color: #92400e;
    font-size: 0.9rem;
}

.notes-section p {
    margin: 0;
    color: #78350f;
}

@media (max-width: 768px) {
    .progress-details { grid-template-columns: 1fr; }
}
</style>

<div class="container">
    <div class="page-header">
        <h1>📊 Student Progress - {{ $schoolName }}</h1>
    </div>

    <div class="progress-grid">
        @forelse($progresses as $progress)
        <div class="progress-card">
            <div class="progress-header">
                <div class="student-info">
                    <h3>{{ $progress->student->name }}</h3>
                    <p>{{ $progress->course->title }}</p>
                </div>
                <div class="progress-percentage">
                    {{ $progress->completion_percent }}%
                </div>
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: {{ $progress->completion_percent }}%;">
                    @if($progress->completion_percent > 15)
                    <span class="progress-bar-text">{{ $progress->completion_percent }}%</span>
                    @endif
                </div>
            </div>

            <div class="progress-details">
                <div class="detail-item">
                    <span class="label">Last Updated</span>
                    <span class="value">{{ $progress->last_updated ? $progress->last_updated->format('M d, Y') : 'Never' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Course Duration</span>
                    <span class="value">{{ $progress->course->duration_hours }} hours</span>
                </div>
                <div class="detail-item">
                    <span class="label">Status</span>
                    <span class="value">
                        @if($progress->completion_percent == 100)
                            <span style="color: #10b981; font-weight: 700;">✓ Completed</span>
                        @elseif($progress->completion_percent > 0)
                            <span style="color: #3b82f6; font-weight: 700;">⏳ In Progress</span>
                        @else
                            <span style="color: #6b7280; font-weight: 700;">⚪ Not Started</span>
                        @endif
                    </span>
                </div>
            </div>

            @if($progress->notes)
            <div class="notes-section">
                <h4>📝 Your Notes</h4>
                <p>{{ $progress->notes }}</p>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
            <p style="font-size: 1.2rem;">No progress records found</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
