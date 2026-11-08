<section class="profile-section" aria-label="Documents and activity">
    <h4 class="profile-section-title">Documents and Activity</h4>
    @if($role === 'student')
        <h5>Submitted License</h5>
        @if($user->student_license_path || $user->student_license_data)
            @php
                $licenseUrl = route('schools.admin.enrollments.viewLicense', ['school' => $school, 'student' => $user]);
                $licensePdf = $user->student_license_mime_type === 'application/pdf' || str_ends_with(strtolower($user->student_license_path ?? ''), '.pdf');
            @endphp
            <p>{{ $user->license_number ?: "Student permit / Driver's license" }} · {{ ucfirst($user->student_license_status ?? 'Submitted') }}</p>
            @include('school.admin.partials.user-document', ['documentUrl' => $licenseUrl, 'documentTitle' => "Student permit / Driver's license", 'documentPdf' => $licensePdf])
        @else
            <p class="text-muted">No student permit or driver's license uploaded to this profile. Enrollment documents are listed below.</p>
        @endif

        <h5 class="mt-4">Enrollment History</h5>
        @forelse($enrollments as $enrollment)
            <article class="user-activity-entry">
                <strong>{{ $enrollment->course?->title ?? 'Course unavailable' }}</strong>
                <p>{{ $enrollment->uid }} · {{ ucfirst($enrollment->status) }} · {{ $enrollment->created_at?->format('M d, Y') }}</p>
                <p>{{ $enrollment->branchRelation?->name ?? 'No branch assigned' }} · ₱{{ number_format($enrollment->price, 2) }} · Payment: {{ ucfirst(str_replace('_', ' ', $enrollment->payment_status ?? 'pending')) }}</p>
                @if($enrollment->credentials_file_path)
                    @include('school.admin.partials.user-document', [
                        'documentUrl' => route('schools.admin.enrollments.view-credential', ['school' => $school, 'enrollmentRequest' => $enrollment]),
                        'documentTitle' => 'Enrollment license / credential',
                        'documentPdf' => str_ends_with(strtolower($enrollment->credentials_file_path), '.pdf'),
                    ])
                @endif
                @if($enrollment->payment_proof_path)
                    @include('school.admin.partials.user-document', [
                        'documentUrl' => app(\App\Services\ReceiptStorageService::class)->getUrl($enrollment->payment_proof_path, $school),
                        'documentTitle' => 'Enrollment payment receipt', 'documentPdf' => false,
                    ])
                @endif
                <a href="{{ route('schools.admin.enrollments.index', ['school' => $school, 'search' => $enrollment->uid]) }}">View enrollment</a>
            </article>
        @empty
            <p class="text-muted">No enrollment history.</p>
        @endforelse
        {{ $enrollments->links() }}

        <h5 class="mt-4">Payment Receipts and Transactions</h5>
        @forelse($payments as $payment)
            <article class="user-activity-entry">
                <strong>{{ $payment->enrollmentRequest?->course?->title ?? $payment->booking?->course?->title ?? 'Payment' }}</strong>
                <p>{{ $payment->uid }} · {{ $payment->receipt_uid }} · ₱{{ number_format($payment->amount, 2) }} · {{ ucfirst($payment->status) }}</p>
                <p>{{ ucfirst(str_replace('_', ' ', $payment->method ?? '')) }} · Reference: {{ $payment->reference ?: $payment->or_number ?: 'Not supplied' }} · {{ $payment->created_at?->format('M d, Y') }}</p>
                @if($payment->proof_url)
                    @include('school.admin.partials.user-document', ['documentUrl' => $payment->proof_url, 'documentTitle' => 'Payment receipt', 'documentPdf' => false])
                @else
                    <p class="text-muted">No receipt uploaded.</p>
                @endif
            </article>
        @empty
            <p class="text-muted">No payment transactions.</p>
        @endforelse
        {{ $payments->links() }}
    @endif

    <h5 class="mt-4">{{ $role === 'instructor' ? 'Teaching Sessions' : 'Bookings' }}</h5>
    @forelse($bookings as $booking)
        <article class="user-activity-entry">
            <strong>{{ $booking->course?->title ?? 'Course unavailable' }}</strong>
            <p>{{ ($booking->scheduled_at ?? $booking->booking_date)?->format('M d, Y g:i A') ?? 'Not scheduled' }} · {{ ucfirst($booking->status) }}</p>
        </article>
    @empty
        <p class="text-muted">No session bookings.</p>
    @endforelse
    {{ $bookings->links() }}
</section>
