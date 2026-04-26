@php
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $borderRadius = $settings?->border_radius ?? 12;
@endphp

<style>
    .license-journey-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 2px solid {{ $primaryColor }}22;
        border-radius: {{ $borderRadius }}px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .journey-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .journey-title svg {
        width: 24px;
        height: 24px;
        fill: {{ $primaryColor }};
    }

    .journey-steps {
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
    }

    .journey-steps::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 20px;
        bottom: 20px;
        width: 2px;
        background: {{ $primaryColor }}33;
    }

    .journey-step {
        display: flex;
        gap: 20px;
        position: relative;
        z-index: 1;
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: white;
        border: 2px solid {{ $primaryColor }};
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: {{ $primaryColor }};
        font-size: 0.875rem;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .journey-step.active .step-number {
        background: {{ $primaryColor }};
        color: white;
    }

    .step-content {
        flex: 1;
    }

    .step-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    .step-desc {
        font-size: 0.85rem;
        color: #6b7280;
        line-height: 1.5;
    }

    .step-badge {
        display: inline-block;
        margin-top: 6px;
        padding: 2px 8px;
        background: {{ $primaryColor }}15;
        color: {{ $primaryColor }};
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }
</style>

<div class="license-journey-card">
    <h3 class="journey-title">
        <svg viewBox="0 0 24 24"><path d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z"/></svg>
        Your Roadmap to a Driver's License
    </h3>
    
    <div class="journey-steps">
        <div class="journey-step {{ !($student->has_passed_theoretical ?? false) ? 'active' : '' }}">
            <div class="step-number">1</div>
            <div class="step-content">
                <div class="step-title">Theoretical Driving Course (TDC)</div>
                <div class="step-desc">Attend 15 hours of classroom instruction across 3 sessions. Pass the final exam to get your TDC Certificate.</div>
                @if(!($student->has_passed_theoretical ?? false))
                    <span class="step-badge">In Progress</span>
                @else
                    <span class="step-badge" style="background:#d1fae5; color:#065f46;">Completed</span>
                @endif
            </div>
        </div>

        <div class="journey-step {{ ($student->has_passed_theoretical ?? false) && !$student->student_license_path ? 'active' : '' }}">
            <div class="step-number">2</div>
            <div class="step-content">
                <div class="step-title">Apply for Student Permit</div>
                <div class="step-desc">Visit any LTO branch with your TDC Certificate and Medical Certificate to secure your Student Permit.</div>
            </div>
        </div>

        <div class="journey-step {{ $student->student_license_path && !$student->has_passed_practical ? 'active' : '' }}">
            <div class="step-number">3</div>
            <div class="step-content">
                <div class="step-title">Practical Driving Course (PDC)</div>
                <div class="step-desc">Once you have your permit, enroll in PDC to start hands-on driving with our professional instructors.</div>
                <span class="step-badge">Requires Student Permit</span>
            </div>
        </div>

        <div class="journey-step">
            <div class="step-number">4</div>
            <div class="step-content">
                <div class="step-title">LTO Driver's License Application</div>
                <div class="step-desc">After finishing PDC and holding your permit for at least 31 days, you can apply for your Non-Professional License.</div>
            </div>
        </div>
    </div>
</div>
