<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 15px 0; }
        .info-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .reason-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { font-weight: 600; color: #6b7280; display: inline-block; width: 120px; }
        .next-steps { background: #eff6ff; border-left: 4px solid #667eea; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .next-steps ul { margin: 8px 0; padding-left: 20px; }
        .next-steps li { margin-bottom: 6px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Enrollment Request Update</h1>
        </div>
        <div class="content">
            <p>Dear {{ $enrollment->learner->name }},</p>
            
            <div class="info-box">
                <strong>Your enrollment request was not approved at this time.</strong> Please review the details below.
            </div>

            <h3 style="color: #1f2937; margin-top: 25px;">Request Details:</h3>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span>{{ $enrollment->course->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span>{{ $enrollment->course->course_type === 'theoretical' ? 'TDC (Theoretical)' : 'PDC (Practical)' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted:</span>
                <span>{{ $enrollment->created_at->format('F d, Y h:i A') }}</span>
            </div>

            @if($enrollment->remarks)
            <h3 style="color: #1f2937; margin-top: 25px;">Reason:</h3>
            <div class="reason-box">
                <p style="margin: 0; font-size: 14px; color: #4b5563;">{{ $enrollment->remarks }}</p>
            </div>
            @endif

            <div class="next-steps">
                <strong>What you can do:</strong>
                <ul>
                    <li>Review the reason above and address any issues</li>
                    <li>Submit a new enrollment request with updated information</li>
                    <li>Contact the school for more details or clarification</li>
                    @if($enrollment->course->isPractical())
                    <li>Ensure your driver's license has been uploaded and verified</li>
                    @endif
                </ul>
            </div>

            <p style="margin-top: 20px;">Don't be discouraged! You can always submit a new enrollment request once you've addressed the feedback above.</p>

            <center>
                <a href="{{ route('schools.guest.courses', ['school' => $school]) }}" class="button">Browse Courses</a>
            </center>

            <p style="color: #6b7280; font-size: 14px; margin-top: 25px;">
                If you have any questions, please don't hesitate to contact us at {{ $school->schoolSetting->contact_email ?? 'the school office' }}.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ $school->name }}. All rights reserved.</p>
            <p style="margin: 5px 0;">This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
