<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\EnrollmentValidator;

class StoreEnrollmentRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Guest enrollment is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'course_id' => ['required', 'exists:courses,id'],
            'requested_license_type' => ['required', 'in:non_professional,professional'],
            'experience_level' => ['required', 'in:new_driver,experienced'],
            'credentials_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120' // 5MB in kilobytes
            ],
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
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'phone.required' => 'Please enter your phone number.',
            'date_of_birth.required' => 'Please enter your date of birth.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'course_id.required' => 'Please select a course.',
            'course_id.exists' => 'The selected course does not exist.',
            'requested_license_type.required' => 'Please select a license type.',
            'requested_license_type.in' => 'Invalid license type selected.',
            'experience_level.required' => 'Please indicate your experience level.',
            'experience_level.in' => 'Invalid experience level selected.',
            'credentials_file.mimes' => 'Credentials must be a PDF, JPG, or PNG file.',
            'credentials_file.max' => 'Credentials file must not exceed 5MB.',
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
            // Validate enrollment request using our custom validator
            $validation = EnrollmentValidator::validateEnrollmentRequest([
                'course_id' => $this->course_id,
                'experience_level' => $this->experience_level,
                'credentials_file_path' => $this->hasFile('credentials_file') ? 'present' : null,
            ]);

            if (!$validation['valid']) {
                $validator->errors()->add('course_id', $validation['message']);
            }

            // If file is present, validate it
            if ($this->hasFile('credentials_file')) {
                $fileValidation = EnrollmentValidator::validateCredentialFile($this->file('credentials_file'));
                
                if (!$fileValidation['valid']) {
                    $validator->errors()->add('credentials_file', $fileValidation['message']);
                }
            }

            // Require credentials for experienced drivers applying to practical courses
            if ($this->experience_level === 'experienced' && !$this->hasFile('credentials_file')) {
                $course = \App\Models\Course::find($this->course_id);
                if ($course && $course->isPractical()) {
                    $validator->errors()->add('credentials_file', 'Experienced drivers must upload proof of theoretical completion when applying for practical courses.');
                }
            }
        });
    }
}
