<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\EnrollmentValidator;

class MarkTheoreticalPassedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only instructors and admins can mark theoretical as passed
        $user = $this->user();
        
        return $user && ($user->role === 'instructor' || $user->role === 'admin' || $user->role === 'superadmin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'enrollment_id.required' => 'Enrollment ID is required.',
            'enrollment_id.exists' => 'The selected enrollment does not exist.',
            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $enrollment = \App\Models\EnrollmentRequest::find($this->enrollment_id);

            if ($enrollment) {
                // Use our custom validator to check if this enrollment can be marked as passed
                $validation = EnrollmentValidator::canMarkTheoreticalPassed($enrollment);

                if (!$validation['allowed']) {
                    $validator->errors()->add('enrollment_id', $validation['message']);
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'enrollment_id' => 'enrollment',
        ];
    }
}
