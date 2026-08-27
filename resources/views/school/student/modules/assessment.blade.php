@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', $module->title ?? 'Assessment')

@section('content')
@include('school.partials.lms-shared-styles')

<div class="lms-page" data-breadcrumb-course="{{ $course->title ?? '' }}" data-breadcrumb-module="{{ $module->title ?? '' }}">
    <div class="lms-header">
        <div>
            <h1 class="lms-title">{{ $module->title }}</h1>
            <p class="lms-subtitle">
                @if($module->questions->count() > 0)
                    Please answer all questions below to complete this assessment.
                @else
                    This assessment is currently empty.
                @endif
            </p>
        </div>
        <div class="lms-actions">
            <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-muted">Back to My Course</a>
            
            <div style="display: flex; gap: 0.5rem;">
                <span class="lms-chip" style="background: #eff6ff; color: #1d4ed8;">
                    {{ $module->questions->count() }} Questions
                </span>
                <span class="lms-chip" style="background: #ecfdf5; color: #059669;">
                    {{ $module->questions->sum('pivot.points') }} Total Points
                </span>
            </div>
        </div>
    </div>

    <div class="lms-card" style="max-width: 900px; margin: 0 auto 2rem auto;">
        @if($latestAttempt)
            <div class="lms-inline-note" style="margin-bottom: 1rem;">
                Latest result: {{ $latestAttempt->score }}/{{ $latestAttempt->total_points }} points ({{ $latestAttempt->percentage }}%).
                {{ $latestAttempt->passed ? 'Assessment passed.' : 'Assessment not passed yet.' }}
            </div>
        @endif

        <form action="{{ school_route('student.courses.modules.assessment.submit', ['course' => $course->id, 'module' => $module->id]) }}" method="POST" id="quizForm" class="lms-form">
            @csrf
            
            @forelse($module->questions as $index => $question)
                <div class="lms-quiz-question" style="padding: 2rem; border-bottom: 1px solid #f1f5f9; position: relative;">
                    <div style="display: flex; gap: 1.5rem; align-items: flex-start;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; font-size: 0.85rem;">
                            {{ $index + 1 }}
                        </div>
                        
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 600; color: #1e293b; line-height: 1.5;">
                                    {{ $question->question_text }}
                                </h3>
                                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; background: #f8fafc; padding: 4px 8px; border-radius: 4px; margin-left: 1rem; white-space: nowrap;">
                                    {{ $question->pivot->points }} {{ Str::plural('POINT', $question->pivot->points) }}
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @if($question->question_type === 'multiple_choice')
                                    @foreach($question->options as $optionIndex => $option)
                                        <label class="lms-quiz-option">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" required>
                                            <div class="lms-quiz-option-ui">
                                                <div class="radio-circle"></div>
                                                <span class="option-text">{{ $option }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                @elseif($question->question_type === 'true_false')
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        @foreach(['True', 'False'] as $option)
                                            <label class="lms-quiz-option">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" required>
                                                <div class="lms-quiz-option-ui">
                                                    <div class="radio-circle"></div>
                                                    <span class="option-text">{{ $option }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div style="margin-top: 0.5rem;">
                                        <textarea 
                                            name="answers[{{ $question->id }}]" 
                                            class="lms-input" 
                                            rows="3"
                                            placeholder="Type your answer here..."
                                            required
                                            style="resize: vertical;"></textarea>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 4rem 2rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">📋</div>
                    <h2 style="font-size: 1.25rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Ready to be set</h2>
                    <p style="color: #94a3b8;">Questions are currently being prepared for this assessment.</p>
                </div>
            @endforelse

            @if($module->questions->count() > 0)
                <div style="padding: 2.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                    <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.5rem; font-style: italic;">
                        Please ensure you have answered all questions correctly before submitting.
                    </p>
                    <button type="submit" class="lms-btn lms-btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);">
                        Submit Assessment
                    </button>
                </div>
            @endif
        </form>
    </div>

    <div id="resultsOverlay" style="display: none; padding: 2.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; border-radius: 12px; margin-top: 1rem; animation: fadeIn 0.5s ease-out;">
        <div id="verdictBadge" style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;"></div>
        <h2 id="resultScore" style="font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">0/0</h2>
        <p id="resultText" style="color: #64748b; font-size: 1.1rem; margin-bottom: 2rem;">You've completed the practice test.</p>
        <div style="display: flex; justify-content: center; gap: 1rem;">
            <button onclick="window.location.reload()" class="lms-btn lms-btn-muted">Retake Test</button>
            @if(isset($navigation->next))
                <a href="{{ $navigation->next['url'] }}" class="lms-btn lms-btn-primary">Continue to Next Lesson</a>
            @else
                <a href="{{ school_route('student.my-course') }}" class="lms-btn lms-btn-primary">Back to My Course</a>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <div id="standardNavigation" style="display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 2rem auto 0; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
        @if(isset($navigation->prev))
            <a href="{{ $navigation->prev['url'] }}" class="lms-btn lms-btn-muted" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <span>Previous: {{ $navigation->prev['title'] }}</span>
            </a>
        @else
            <div></div>
        @endif

        @if(isset($navigation->next))
            <a href="{{ $navigation->next['url'] }}" class="lms-btn lms-btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                <span>Next: {{ $navigation->next['title'] }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        @endif
    </div>
</div>

<script>
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        return;
        
        const form = this;
        const questions = @json($module->questions);
        let score = 0;
        let totalPoints = 0;

        questions.forEach(q => {
            const userAnswer = form.querySelector(`[name="answers[${q.id}]"]:checked`)?.value || 
                               form.querySelector(`[name="answers[${q.id}]"]`)?.value;
            
            const isCorrect = String(userAnswer).trim().toLowerCase() === String(q.correct_answer).trim().toLowerCase();
            const points = parseInt(q.pivot?.points || 1);
            totalPoints += points;

            if (isCorrect) {
                score += points;
            }

            // Highlight answers
            const inputs = form.querySelectorAll(`[name="answers[${q.id}]"]`);
            if (inputs.length > 0) {
                const questionContainer = inputs[0].closest('.lms-quiz-question');
                const options = questionContainer.querySelectorAll('.lms-quiz-option');
                
                if (q.question_type === 'multiple_choice' || q.question_type === 'true_false') {
                    options.forEach(opt => {
                        const input = opt.querySelector('input');
                        const ui = opt.querySelector('.lms-quiz-option-ui');
                        
                        if (String(input.value).trim().toLowerCase() === String(q.correct_answer).trim().toLowerCase()) {
                            ui.style.borderColor = '#10b981';
                            ui.style.background = '#f0fdf4';
                            ui.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.1)';
                            opt.querySelector('.option-text').style.color = '#065f46';
                        } else if (input.checked && !isCorrect) {
                            ui.style.borderColor = '#ef4444';
                            ui.style.background = '#fef2f2';
                            opt.querySelector('.option-text').style.color = '#991b1b';
                        }
                        input.disabled = true;
                    });
                } else {
                    // Text answer
                    const textarea = questionContainer.querySelector('textarea');
                    textarea.disabled = true;
                    if (isCorrect) {
                        textarea.style.borderColor = '#10b981';
                        textarea.style.background = '#f0fdf4';
                    } else {
                        textarea.style.borderColor = '#ef4444';
                        textarea.style.background = '#fef2f2';
                        const correctDiv = document.createElement('div');
                        correctDiv.style.marginTop = '0.5rem';
                        correctDiv.style.fontSize = '0.85rem';
                        correctDiv.style.color = '#059669';
                        correctDiv.style.fontWeight = '600';
                        correctDiv.innerHTML = 'Correct Answer: ' + q.correct_answer;
                        textarea.parentNode.appendChild(correctDiv);
                    }
                }
            }
        });

        // Show Results
        form.querySelector('button[type="submit"]').style.display = 'none';
        document.getElementById('standardNavigation').style.display = 'none';
        const overlay = document.getElementById('resultsOverlay');
        const scoreEl = document.getElementById('resultScore');
        const verdictEl = document.getElementById('verdictBadge');
        const textEl = document.getElementById('resultText');

        const percentage = totalPoints > 0 ? (score / totalPoints) * 100 : 0;
        scoreEl.textContent = `${score} / ${totalPoints}`;
        
        if (percentage >= 90) {
            verdictEl.textContent = 'Excellent!';
            verdictEl.style.background = '#ecfdf5';
            verdictEl.style.color = '#059669';
            textEl.textContent = "Outstanding performance! You're definitely ready for the real test.";
        } else if (percentage >= 75) {
            verdictEl.textContent = 'Very Good';
            verdictEl.style.background = '#eff6ff';
            verdictEl.style.color = '#1d4ed8';
            textEl.textContent = "Great job! You've shown strong understanding. A bit more review and you'll be perfect.";
        } else if (percentage >= 50) {
            verdictEl.textContent = 'Good Effort';
            verdictEl.style.background = '#fffbeb';
            verdictEl.style.color = '#d97706';
            textEl.textContent = "You're getting there! Review the incorrect answers and try again to improve your readiness.";
        } else {
            verdictEl.textContent = 'Need More Review';
            verdictEl.style.background = '#fef2f2';
            verdictEl.style.color = '#dc2626';
            textEl.textContent = "Don't worry! Use this as a guide on what to study next. Review the course materials and try again.";
        }

        overlay.style.display = 'block';
        window.scrollTo({ top: overlay.offsetTop - 100, behavior: 'smooth' });
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .lms-quiz-option {
        display: block;
        cursor: pointer;
        margin-bottom: 0.5rem;
    }
    .lms-quiz-option input {
        display: none;
    }
    .lms-quiz-option-ui {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
        background: white;
    }
    .lms-quiz-option:hover .lms-quiz-option-ui {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .radio-circle {
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 50%;
        margin-right: 1rem;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .lms-quiz-option input:checked + .lms-quiz-option-ui {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .lms-quiz-option input:checked + .lms-quiz-option-ui .radio-circle {
        border-color: #3b82f6;
        background: #3b82f6;
    }
    .lms-quiz-option input:checked + .lms-quiz-option-ui .radio-circle::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
    }
    .option-text {
        font-size: 0.95rem;
        color: #475569;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .lms-quiz-option input:checked + .lms-quiz-option-ui .option-text {
        color: #1e40af;
        font-weight: 600;
    }
</style>
@endsection
