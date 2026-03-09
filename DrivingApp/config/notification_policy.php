<?php

return [
    // Global guard: lifecycle transition emails are disabled by default.
    // These transitions are admin confirmation actions and should remain in-app only.
    'enable_lifecycle_transition_emails' => false,

    // Explicit channel policy for enrollment lifecycle transitions.
    'enrollment_transitions' => [
        'approved' => [
            'channels' => ['email', 'in_app'],
        ],
        'rejected' => [
            'channels' => ['email', 'in_app'],
        ],
        'payment_status_updated' => [
            'channels' => ['in_app'],
        ],
        'enrollment_completed' => [
            'channels' => ['in_app'],
        ],
        'enrollment_cancelled' => [
            'channels' => ['in_app'],
        ],
        'theoretical_passed' => [
            'channels' => ['in_app'],
        ],
        'license_verified' => [
            'channels' => ['in_app'],
        ],
        'license_rejected' => [
            'channels' => ['in_app'],
        ],
    ],

    // Keep lifecycle email code paths in place but explicitly disable these
    // transitions unless intentionally removed from this list.
    'disabled_lifecycle_email_transitions' => [
        'payment_status_updated',
        'enrollment_completed',
        'enrollment_cancelled',
        'theoretical_passed',
        'license_verified',
        'license_rejected',
    ],

    // Explicit command-level notification intent.
    'commands' => [
        'reminders' => [
            'sessions' => [
                'email' => true,
                'in_app_student' => true,
                'in_app_instructor' => true,
            ],
        ],
        'bookings' => [
            'confirm_queued' => [
                'email' => false,
                'in_app' => false,
            ],
        ],
    ],
];
