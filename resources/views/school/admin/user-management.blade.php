@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'User Management')

@section('content')
    @php
        $school = $school ?? $currentSchool ?? null;
        $settings = $school?->schoolSetting;
        $schoolName = $school->name ?? 'Driving School';

        $primaryColor = $settings->primary_color ?? '#3b82f6';
        $secondaryColor = $settings->secondary_color ?? '#60a5fa';

        // Statistics are now passed from the controller to ensure accuracy with pagination
        $totalUsers = $totalStudents + $totalInstructors;
        $totalActive = $activeStudents + $activeInstructors;
        $totalInactive = $inactiveStudents + $inactiveInstructors;

        $oldInviteRole = old('role');
        $showStudentInviteErrors = $errors->any() && $oldInviteRole === 'student';
        $showInstructorInviteErrors = $errors->any() && $oldInviteRole === 'instructor';
        $requireInstructorLicense = $settings?->require_instructor_license ?? true;
    @endphp

    @include('school.admin.partials.admin-styles')

    <style>
        :root {
            --primary-color:
                {{ $primaryColor }}
            ;
            --secondary-color:
                {{ $secondaryColor }}
            ;
            --primary-rgb:
                {{ implode(',', sscanf($primaryColor, '#%02x%02x%02x')) }}
            ;
            --header-gradient: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    100%);
            --border-radius: 20px;
            --button-border-radius: 12px;

            /* Branded Button Variables */
            --btn-primary-bg:
                {{ $primaryColor }}
            ;
            --btn-primary-text: #ffffff;
            --btn-secondary-bg:
                {{ $secondaryColor }}
            ;
            --btn-secondary-text: #ffffff;
            --btn-gradient: var(--header-gradient);
        }

        .admin-mgmt-container {
            padding: 20px;
            margin: 0 auto;
            max-width: 1600px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .page-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-review-requests {
            position: relative;
        }

        .request-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
        }

        /* Statistics Cards - Using shared styles from admin-styles.blade.php */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Additional stat card color variants for user management */
        .stat-card.total {
            border-left-color: var(--primary-color);
        }

        .stat-card.total::before {
            background: var(--primary-color);
        }

        .stat-card.total .stat-icon {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary-color);
        }

        .stat-card.inactive {
            border-left-color: #6b7280;
        }

        .stat-card.inactive::before {
            background: #6b7280;
        }

        .stat-card.inactive .stat-icon {
            background: #6b728015;
            color: #6b7280;
        }

        .stat-card.filter-selected {
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.18), 0 8px 20px rgba(0, 0, 0, 0.14);
            transform: translateY(-1px);
        }

        /* Action Buttons */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .control-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .contact-input-group {
            display: flex;
            align-items: stretch;
        }

        .contact-prefix {
            display: flex;
            align-items: center;
            background: #f3f4f6;
            border: 2px solid #e1e5e9;
            border-right: none;
            padding: 0 12px;
            border-radius: 8px 0 0 8px;
            color: #4b5563;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .contact-input-group input {
            border-radius: 0 8px 8px 0 !important;
            flex: 1;
        }

        /* --- ENHANCED USER PROFILE MODAL STYLES --- */
        .profile-modal-container {
            border-radius: 20px;
            overflow: visible; /* Allow content to flow, handled by modal wrapper scroll */
            border: none;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            max-width: none !important;
        }

        .profile-modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 40px 30px;
            text-align: center;
            position: relative;
            color: white;
        }
        
        .profile-modal-header .btn-close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1;
        }
        
        .profile-modal-header .btn-close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .profile-avatar-wrapper {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: white;
            margin: 0 auto 15px;
            padding: 5px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .profile-avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 52px;
            font-weight: 700;
            color: var(--primary-color);
            text-transform: uppercase;
        }
        
        .profile-avatar-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-modal-name {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .profile-modal-role {
            display: inline-block;
            padding: 5px 14px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .profile-modal-body {
            padding: 35px;
            background: white;
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .profile-section:last-child {
            margin-bottom: 0;
        }
        
        .profile-section-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-section-title i {
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }
        
        .profile-info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .profile-info-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .profile-info-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }

        .profile-info-value.masked {
            font-family: monospace;
            letter-spacing: 1px;
        }
        
        .profile-license-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            margin-top: 10px;
        }

        .profile-license-preview {
            width: 100%;
            height: 220px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-license-preview:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .profile-license-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-license-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.8);
            color: white;
            padding: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            backdrop-filter: blur(4px);
        }

        .profile-modal-footer {
            padding: 20px 35px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* Restriction Chip Styles */
        .restriction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 10px;
            width: 100%;
        }

        .restriction-chip {
            cursor: pointer;
            position: relative;
        }

        .restriction-chip input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .restriction-chip .chip-content {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-weight: 700;
            color: #64748b;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .restriction-chip input:checked + .chip-content {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.3);
        }

        .status-btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .action-controls {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-box input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-box::after {
            content: "";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .btn-create {
            padding: 10px 20px;
            @if(($settings->button_style ?? 'solid') === 'gradient')
                background: var(--btn-gradient);
            @else background: var(--btn-primary-bg);
            @endif color: var(--btn-primary-text);
            border: none;
            border-radius: var(--button-border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
        }

        .btn-create:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .icon-18 {
            width: 18px;
            height: 18px;
        }

        .icon-14 {
            width: 14px;
            height: 14px;
        }

        /* Validation Feedback Styles */
        .is-invalid {
            border-color: #ef4444 !important;
            background-color: #fffafb !important;
        }

        .field-error {
            color: #ef4444;
            font-size: 0.8rem;
            margin-top: 4px;
            font-weight: 500;
            display: block;
        }

        .modal-error-summary {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #991b1b;
        }

        .modal-error-summary ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
            font-size: 0.85rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
            white-space: nowrap;
        }

        thead {
            background: var(--header-gradient);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f3f5;
        }

        tbody tr {
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .role-student,
        .role-guest {
            background: var(--role-student-bg);
            color: var(--role-student-text);
        }

        .role-guest {
            border: 1px dashed var(--role-student-text);
        }

        .role-instructor {
            background: var(--role-instructor-bg);
            color: var(--role-instructor-text);
        }

        .branch-label {
            font-size: 0.85rem;
        }

        .branch-assigned {
            color: #374151;
        }

        .branch-unassigned {
            color: #9ca3af;
        }

        .btn-action {
            padding: 6px 12px;
            margin: 0;
            border: none;
            border: 1px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-buttons form {
            margin: 0;
            display: inline-flex;
        }

        .action-buttons .btn-action {
            padding: 8px 12px;
            border-radius: 10px;
        }

        .action-buttons .btn-info {
            background: #e8efff;
            color: #2563eb;
            border-color: #c7d2fe;
        }

        .action-buttons .btn-warning {
            background: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        .action-buttons .btn-success {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .action-buttons .btn-secondary {
            background: #ffe4e6;
            color: #be123c;
            border-color: #fecdd3;
        }

        .action-buttons .btn-primary {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .action-buttons .btn-action:hover {
            transform: translateY(-1px);
            filter: brightness(0.98);
        }

        .actions-cell {
            min-width: 220px;
        }

        .actions-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-edit:hover {
            background: #ffb300;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background: #138496;
            transform: scale(1.05);
        }

        .btn-toggle {
            background: #f0ad4e;
            color: white;
        }

        .btn-toggle:hover {
            background: #ec971f;
            transform: scale(1.05);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: flex-start; /* Align to top for long content scrolling */
            padding: 40px 10px; /* Add breathing room for scroll */
        }

        .icon-14 {
            width: 14px;
            height: 14px;
        }

        .icon-18 {
            width: 18px;
            height: 18px;
        }

        .icon-20 {
            width: 20px;
            height: 20px;
        }

        .icon-24 {
            width: 24px;
            height: 24px;
        }

        .icon-32 {
            width: 32px;
            height: 32px;
        }

        .ms-auto {
            margin-left: auto !important;
        }

        .me-1 {
            margin-right: 0.25rem !important;
        }

        .me-2 {
            margin-right: 0.5rem !important;
        }

        .me-3 {
            margin-right: 1rem !important;
        }

        /* Consolidate Modal Content styles */
        .modal-content {
            padding: 0 !important;
            border: none !important;
            border-radius: 20px !important;
            overflow: visible;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: white;
            transition: transform 0.2s ease-out;
            width: min(600px, 92%);
            margin: auto;
            flex-shrink: 0;
        }

        #verifyLicenseModal .modal-content {
            width: 1100px;
            max-width: 95%;
            flex-shrink: 0;
        }

        /* Override shared admin transform scaling for this page's .modal pattern to prevent 'shrinking' effect. */
        .modal .modal-content {
            transform: none !important;
            transition: none !important;
            animation: fadeIn 0.2s ease-out !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }



        .modal-header {
            background: var(--header-gradient);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h3 {
            color: white !important;
            margin: 0 !important;
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            background: none !important;
            padding: 0 !important;
            border: none !important;
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            line-height: 1;
        }

        .btn-close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 24px 32px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .modal-content form {
            padding: 0 !important;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            margin-top: 0;
            width: 100%;
            justify-content: flex-end;
        }

        .modal-buttons button,
        .modal-buttons .btn-submit,
        .modal-buttons .btn-cancel,
        .modal-footer .btn {
            padding: 10px 20px;
            border-radius: var(--button-border-radius, 8px);
            font-weight: 600;
            transition: all 0.2s;
            font-size: 0.95rem;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--header-gradient);
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Verification Modal Specifics */
        .verify-modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .restriction-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }

        .restriction-chip {
            position: relative;
            cursor: pointer;
            user-select: none;
        }

        .restriction-chip input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .chip-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: var(--button-border-radius, 12px);
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .restriction-chip:hover .chip-content {
            border-color: var(--primary-color);
            background: #f1f5f9;
            transform: translateY(-2px);
        }

        .restriction-chip input:checked+.chip-content {
            background: var(--header-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }

        .verification-preview-container {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: var(--border-radius, 12px);
            overflow: hidden;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .verification-preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .verification-preview-container:hover img {
            transform: scale(1.05);
        }

        .verification-preview-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .verification-preview-container:hover .verification-preview-overlay {
            opacity: 1;
        }

        .status-badge-selector {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .status-option {
            flex: 1;
            cursor: pointer;
        }

        .status-option input {
            position: absolute;
            opacity: 0;
        }

        .status-btn {
            display: block;
            padding: 12px;
            text-align: center;
            border-radius: var(--button-border-radius, 10px);
            border: 2px solid #e2e8f0;
            font-weight: 600;
            transition: all 0.2s;
            color: #64748b;
        }

        .status-option:nth-child(1) input:checked+.status-btn {
            background: #dcfce7;
            border-color: #22c55e;
            color: #166534;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
        }

        .status-option:nth-child(2) input:checked+.status-btn {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.98);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .unlock-requests-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 2px;
            margin: 0 24px 0;
        }

        .unlock-request-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            background: #f9fafb;
        }

        .unlock-request-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .unlock-request-name {
            font-weight: 600;
            color: #1f2937;
        }

        .unlock-request-role {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .unlock-request-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .unlock-request-reason {
            margin: 10px 0;
            font-size: 0.88rem;
            color: #374151;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .unlock-request-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .unlock-request-actions form {
            margin: 0;
        }

        .btn-approve-request,
        .btn-deny-request {
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-approve-request {
            background: #059669;
        }

        .btn-approve-request:hover {
            background: #047857;
        }

        .btn-deny-request {
            background: #dc2626;
        }

        .btn-deny-request:hover {
            background: #b91c1c;
        }

        .modal-content form {
            padding: 32px;
            background: #fff;
            margin: 0;
            border-radius: 0 0 16px 16px;
            max-height: 72vh;
            overflow-y: auto;
            scrollbar-gutter: stable;
        }

        .modal-required-note {
            margin: 0 0 18px;
            font-size: 0.86rem;
            color: #6b7280;
        }

        .required-indicator {
            color: #dc2626;
            margin-left: 2px;
        }

        .field-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
        }

        .field-error {
            font-size: 0.8rem;
            color: #b91c1c;
            margin-top: 6px;
        }

        .modal-error-summary {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .modal-error-summary strong {
            display: block;
            margin-bottom: 8px;
            font-size: 0.88rem;
        }

        .modal-error-summary ul {
            margin: 0;
            padding-left: 18px;
        }

        .modal-error-summary li {
            margin-bottom: 4px;
            font-size: 0.82rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #1a202c;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            box-sizing: border-box;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            padding: 24px 32px 32px 32px;
            background: #fff;
        }

        .btn-cancel {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            flex: 1;
            padding: 16px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            flex: 1;
            padding: 16px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            border: none;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        }

        .btn-submit:disabled {
            cursor: not-allowed;
            opacity: 0.75;
            transform: none;
            box-shadow: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .empty-state-text {
            font-size: 1.2rem;
            color: #666;
        }

        .pagination-groups {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .pagination-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #6b7280;
        }

        .pagination-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #6b7280;
        }

        .export-dropdown-menu a .dot.pdf {
            background: #ef4444;
        }

        .export-dropdown-menu a .dot.excel {
            background: #10b981;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .admin-mgmt-container {
                padding: 15px;
                margin: 10px auto;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-header-right {
                width: 100%;
            }

            .page-header-right .btn-create {
                width: 100%;
                justify-content: center;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 18px;
            }

            .stat-value {
                font-size: 1.8rem;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .tab {
                padding: 10px 16px;
                font-size: 0.9rem;
                flex: 1;
                min-width: 120px;
                text-align: center;
            }

            .action-bar {
                flex-direction: column;
                gap: 15px;
                padding: 12px;
            }

            .action-bar>div:last-child {
                flex-direction: row;
                width: auto;
            }

            .action-controls {
                width: 100%;
                align-items: center;
                justify-content: flex-start;
                gap: 8px;
            }

            .search-box {
                max-width: 100%;
                width: 100%;
            }

            .control-label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .btn-create {
                width: auto;
                justify-content: center;
            }

            .table-container {
                border-radius: 8px;
            }

            table {
                font-size: 0.85rem;
                min-width: 780px;
            }

            th,
            td {
                padding: 10px 8px;
                white-space: nowrap;
            }

            .btn-action {
                padding: 5px 8px;
                font-size: 0.8rem;
                margin: 0;
            }

            .actions-group {
                gap: 4px;
            }

            .status-badge {
                padding: 4px 8px;
                font-size: 0.75rem;
            }

            /* Hide less important columns on mobile */
            .hide-mobile {
                display: none;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                min-width: 0;
                margin: 10px;
            }

            .modal-content h3 {
                padding: 20px;
                font-size: 1.3rem;
            }

            .modal-content form {
                padding: 20px;
            }

            .modal-buttons {
                padding: 15px 20px 20px;
                flex-direction: column;
            }

            .btn-cancel,
            .btn-submit {
                width: 100%;
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .admin-mgmt-container {
                padding: 10px;
                margin: 5px auto;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .stat-detail {
                font-size: 0.75rem;
            }

            .tab {
                padding: 8px 12px;
                font-size: 0.85rem;
                min-width: 100px;
            }

            table {
                font-size: 0.8rem;
                min-width: 720px;
            }

            th,
            td {
                padding: 8px 6px;
                white-space: nowrap;
            }

            .btn-action {
                padding: 4px 6px;
                font-size: 0.75rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-group input,
            .form-group select {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
        }

        /* Contact Input Styling */
        .contact-input-group {
            display: flex;
            align-items: stretch;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #f9fafb;
            transition: all 0.2s;
        }

        .contact-input-group:focus-within {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px var(--primary-shadow);
        }

        .contact-prefix {
            background: #f1f5f9;
            padding: 10px 16px;
            color: #475569;
            font-weight: 600;
            border-right: 1px solid #e2e8f0;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .contact-input-group input {
            border: none !important;
            box-shadow: none !important;
            padding: 10px 16px !important;
            background: transparent !important;
            width: 100%;
            font-size: 0.95rem;
        }

        .field-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Pagination styles are inherited from shared admin-styles */
        .btn-reveal-pii {
            background: none;
            border: none;
            padding: 0;
            color: var(--primary-color);
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-reveal-pii:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .btn-reveal-pii.timer-active {
            color: #94a3b8;
            cursor: wait;
        }

        .masked-pii {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .pii-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 24px;
        }

        .pii-indicator {
            font-size: 0.75rem;
            color: #6b7280;
            margin-left: 4px;
            cursor: help;
        }

        /* Improved Table Spacing */
        #usersTable th,
        #usersTable td {
            vertical-align: middle !important;
            padding: 16px 12px;
        }

        #usersTable thead th {
            white-space: nowrap;
        }

        .availability-indicator {
            position: relative;
            display: inline-block;
        }

        .availability-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            position: absolute;
            top: -4px;
            left: -14px;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        .availability-dot.available {
            background-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        .availability-dot.unavailable {
            background-color: #94a3b8;
        }

        /* License Badges */
        .license-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .license-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .license-badge.verified {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .license-badge.rejected {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .license-badge.none {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
    </style>

    <div class="admin-mgmt-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage students and instructors in your driving school</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('all')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value">{{ $totalUsers }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $totalStudents }}</strong> Students &middot;
                        <strong>{{ $totalInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card active {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'active') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('active')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value">{{ $totalActive }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Students &middot;
                        <strong>{{ $activeInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card inactive {{ (($activeRoleFilter ?? 'all') === 'all' && ($activeStatusFilter ?? 'all') === 'inactive') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('inactive')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Inactive Users</div>
                            <div class="stat-value">{{ $totalInactive }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $inactiveStudents }}</strong> Students &middot;
                        <strong>{{ $inactiveInstructors }}</strong> Instructors
                    </div>
                </div>
            </div>

            <div class="stat-card students {{ (($activeRoleFilter ?? 'all') === 'student' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('students')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Students</div>
                            <div class="stat-value">{{ $totalStudents }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeStudents }}</strong> Active &middot;
                        <strong>{{ $inactiveStudents }}</strong> Inactive
                    </div>
                </div>
            </div>

            <div class="stat-card instructors {{ (($activeRoleFilter ?? 'all') === 'instructor' && ($activeStatusFilter ?? 'all') === 'all') ? 'filter-selected' : '' }}"
                onclick="applyQuickFilter('instructors')">
                <div class="stat-content">
                    <div class="stat-header">
                        <div>
                            <div class="stat-label">Instructors</div>
                            <div class="stat-value">{{ $totalInstructors }}</div>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-detail"><strong>{{ $activeInstructors }}</strong> Active &middot;
                        <strong>{{ $inactiveInstructors }}</strong> Inactive
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications handled at the bottom of the file -->

        <!-- Students Section -->
        <div id="usersSection" class="user-section">
            <div class="action-bar">
                <div class="search-box">
                    <label for="userSearch" class="control-label">Search Users</label>
                    <input type="text" id="userSearch" value="{{ $activeSearch ?? '' }}"
                        placeholder="Search users by name, email, or role...">
                </div>
                <div class="action-controls">
                    @if(isset($branches) && $branches->count() > 0)
                        <label for="branchFilter" class="control-label">Branch Filter</label>
                        <select id="branchFilter" onchange="applyBranchFilter()"
                            style="padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; background: white; cursor: pointer;">
                            <option value="all" {{ ($activeBranchFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Branches
                            </option>
                            <option value="unassigned" {{ ($activeBranchFilter ?? 'all') === 'unassigned' ? 'selected' : '' }}>
                                Unassigned</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) ($activeBranchFilter ?? 'all') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <div class="export-dropdown" id="exportDropdown">
                        <button class="btn-export-trigger" onclick="toggleExportDropdown()">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                            <svg class="chevron icon-14" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="export-dropdown-menu">
                            <div class="dropdown-header">Students</div>
                            <a href="{{ school_route('admin.exports.students.pdf') }}">
                                <span class="dot pdf"></span> Students (PDF)
                            </a>
                            <a href="{{ school_route('admin.exports.students.excel') }}">
                                <span class="dot excel"></span> Students (Excel)
                            </a>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header">Instructors</div>
                            <a href="{{ school_route('admin.exports.instructors.pdf') }}">
                                <span class="dot pdf"></span> Instructors (PDF)
                            </a>
                            <a href="{{ school_route('admin.exports.instructors.excel') }}">
                                <span class="dot excel"></span> Instructors (Excel)
                            </a>
                        </div>
                    </div>
                    <button class="btn-create" onclick="openCreateStudentModal()">
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Add Student
                    </button>
                    <button class="btn-create" onclick="openCreateInstructorModal()">
                        <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Add Instructor
                    </button>
                </div>
            </div>

            <div class="table-container">
                @if($users->count() > 0)
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>
                                    Email
                                    @if($settings->enable_pii_masking ?? false)
                                        <i class="bi bi-shield-lock pii-indicator"
                                            title="Privacy Protected (PII Masking Active)"></i>
                                    @endif
                                </th>
                                <th>
                                    Contact
                                    @if($settings->enable_pii_masking ?? false)
                                        <i class="bi bi-shield-lock pii-indicator"
                                            title="Privacy Protected (PII Masking Active)"></i>
                                    @endif
                                </th>
                                <th>Role</th>
                                <th>Branch</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Portal Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}"
                                    data-branch="{{ $user->branch_id ?? 'unassigned' }}">
                                    <td>
                                        <div class="availability-indicator" style="{{ $user->role === 'instructor' ? 'margin-left: 16px;' : '' }}">
                                            @if($user->role === 'instructor')
                                                <span class="availability-dot {{ ($user->availability ?? 'available') === 'available' ? 'available' : 'unavailable' }}" 
                                                      title="{{ ($user->availability ?? 'available') === 'available' ? 'Available' : 'Unavailable' }}"></span>
                                            @endif
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @if(($settings->enable_pii_masking ?? false) && $user->email)
                                            @php
                                                $emailParts = explode('@', $user->email);
                                                $maskedEmail = substr($emailParts[0], 0, 2) . str_repeat('*', max(0, strlen($emailParts[0]) - 2)) . '@' . $emailParts[1];
                                            @endphp
                                            <div class="pii-wrapper">
                                                <span class="masked-pii" data-full="{{ $user->email }}">{{ $maskedEmail }}</span>
                                                <button type="button" class="btn-reveal-pii" onclick="revealPII(this)"
                                                    title="Click to reveal (5s)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        @else
                                            {{ $user->email }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(($settings->enable_pii_masking ?? false) && $user->contact)
                                            @php
                                                $contactValue = $user->contact;
                                                $maskedContact = substr($contactValue, 0, 4) . '****' . substr($contactValue, -2);
                                            @endphp
                                            <div class="pii-wrapper">
                                                <span class="masked-pii" data-full="{{ $contactValue }}">{{ $maskedContact }}</span>
                                                <button type="button" class="btn-reveal-pii" onclick="revealPII(this)"
                                                    title="Click to reveal (5s)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        @else
                                            {{ $user->contact ?: '—' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="role-badge role-{{ $user->role }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $branchName = $user->branch_id ? ($branches->firstWhere('id', $user->branch_id)?->name ?? null) : null;
                                        @endphp
                                        <span
                                            class="branch-label {{ $branchName ? 'branch-assigned' : 'branch-unassigned' }}">{{ $branchName ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($user->role === 'instructor')
                                            @php
                                                $lStatus = $user->license_status ?? 'none';
                                                $lColors = [
                                                    'none' => 'bg-secondary',
                                                    'pending' => 'bg-warning text-dark',
                                                    'verified' => 'bg-success',
                                                    'rejected' => 'bg-danger'
                                                ];
                                                $lLabels = [
                                                    'none' => 'None',
                                                    'pending' => 'Pending',
                                                    'verified' => 'Verified',
                                                    'rejected' => 'Rejected'
                                                ];
                                            @endphp
                                            <div class="d-flex flex-column gap-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <span class="license-badge {{ $lStatus }}">
                                                        {{ $lLabels[$lStatus] }}
                                                    </span>
                                                    @if(($lStatus === 'pending' || $lStatus === 'none') && $user->license_image)
                                                        <button type="button" class="btn-action btn-success js-verify-license"
                                                           style="padding: 4px 10px; font-size: 0.75rem;"
                                                           data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                           data-license-number="{{ $user->license_number }}"
                                                           data-license-image="{{ asset('storage/' . $user->license_image) }}"
                                                           data-status="{{ $user->license_status }}"
                                                           data-restrictions="{{ json_encode($user->restriction_codes ?? []) }}"
                                                           data-rejection-reason="{{ $user->license_rejection_reason }}">
                                                           <i class="bi bi-shield-check me-1"></i> Verify
                                                        </button>
                                                    @endif
                                                </div>
                                                @if($lStatus === 'verified' && !empty($user->restriction_codes))
                                                    <div class="d-flex flex-wrap gap-1 mt-1" style="max-width: 120px;">
                                                        @foreach($user->restriction_codes as $code)
                                                            <span class="badge bg-primary" style="font-size: 0.65rem;">{{ $code }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $user->status }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; line-height: 1.4; color: #4b5563;">
                                            <div>
                                                <span style="font-weight: 600; color: #1f2937;">In:</span>
                                                {{ ($user->last_login_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}
                                            </div>
                                            <div>
                                                <span style="font-weight: 600; color: #1f2937;">Out:</span>
                                                {{ ($user->last_logout_at ?? null) ? \Illuminate\Support\Carbon::parse($user->last_logout_at)->diffForHumans() : 'Never' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            {{-- Global View Button (replaces Edit) --}}
                                            @if($user->role === 'student' || $user->role === 'guest')
                                                <button type="button" class="btn-action btn-info js-view-user" data-role="student"
                                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}" data-contact="{{ $user->contact }}"
                                                    data-address="{{ $user->address }}" data-branch="{{ $user->branch_id }}"
                                                    data-branch-name="{{ $branches->find($user->branch_id)->name ?? 'Not Assigned' }}"
                                                    data-status="{{ $user->status }}"
                                                    data-profile-picture="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : '' }}"
                                                    title="View Student">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>
                                            @elseif($user->role === 'instructor')
                                                <button type="button" class="btn-action btn-info js-view-user" data-role="instructor"
                                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}" data-contact="{{ $user->contact }}"
                                                    data-license="{{ $user->license_number }}" data-address="{{ $user->address }}"
                                                    data-availability="{{ $user->availability }}" data-branch="{{ $user->branch_id }}"
                                                    data-branch-name="{{ $branches->find($user->branch_id)->name ?? 'Not Assigned' }}"
                                                    data-status="{{ $user->status }}"
                                                    data-specializations="{{ json_encode($user->course_specializations ?? []) }}"
                                                    data-license-image="{{ $user->license_image ? asset('storage/' . $user->license_image) : '' }}"
                                                    data-license-status="{{ $user->license_status }}"
                                                    data-restrictions="{{ json_encode($user->restriction_codes ?? []) }}"
                                                    data-rejection-reason="{{ $user->license_rejection_reason }}"
                                                    data-profile-picture="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : '' }}"
                                                    title="View Instructor">
                                                    <i class="bi bi-eye"></i>
                                                    <span>View</span>
                                                </button>

                                            @endif

                                            {{-- Global Status Toggle --}}
                                            @php
                                                $toggleRoute = '';
                                                if ($user->role === 'student' || $user->role === 'guest') {
                                                    $toggleRoute = route('schools.admin.students.toggleStatus', [$school, $user->id]);
                                                } elseif ($user->role === 'instructor') {
                                                    $toggleRoute = route('schools.admin.instructors.toggleStatus', [$school, $user->id]);
                                                }
                                            @endphp

                                            @if($toggleRoute)
                                                <button type="button"
                                                    class="btn-action {{ $user->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                                                    onclick="unifiedToggle({{ $user->id }}, 'status', '{{ $user->status }}', '{{ $user->role }}')"
                                                    title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i
                                                        class="bi {{ $user->status === 'active' ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                    <span>{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            @endif

                                            {{-- Instructor Specific Availability --}}
                                            @if($user->role === 'instructor')
                                                <button type="button"
                                                    class="btn-action {{ $user->availability === 'available' ? 'btn-secondary' : 'btn-primary' }}"
                                                    onclick="unifiedToggle({{ $user->id }}, 'availability', '{{ $user->availability }}', '{{ $user->role }}')"
                                                    title="{{ $user->availability === 'available' ? 'Mark Unavailable' : 'Mark Available' }}">
                                                    <i
                                                        class="bi {{ $user->availability === 'available' ? 'bi-person-dash' : 'bi-person-check' }}"></i>
                                                    <span>{{ $user->availability === 'available' ? 'Unavailable' : 'Available' }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"></div>
                        <div class="empty-state-text">No users found. Add your first student or instructor to get started!</div>
                    </div>
                @endif
            </div>

            @if($users->count() > 0)
                <div class="mt-4 pagination-groups">
                    <div>
                        <h4 style="font-size: 0.9rem; margin-bottom: 5px; color: #6b7280;">
                            Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                        </h4>
                        @if($users->hasPages())
                            <div class="admin-pagination-wrapper">
                                {!! $users->links('vendor.pagination.drivingapp') !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
 
    <!-- USER VIEW MODAL (Unified with Inline Editing) -->
    <div id="userViewModal" class="modal">
        <div class="modal-content profile-modal-container" style="width: min(850px, 95%); padding: 0;">
            <div id="userViewContent">
                <!-- Content will be injected via JS -->
                <div class="modal-body p-5 text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
        </div>
    </div>
 
    <!-- INVITE STUDENT MODAL -->
    <div id="createStudentModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-person-plus me-2"></i>Invite New Student</h3>
                <button type="button" class="btn-close-modal" onclick="closeCreateStudentModal()">×</button>
            </div>
            <form id="createStudentInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}"
                data-no-ajax="1">
                @csrf
                <div class="modal-body">
                    <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are
                        required.</p>

                    @if($showStudentInviteErrors)
                        <div class="modal-error-summary" role="alert">
                            <strong>Please fix the following:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" name="name" value="{{ $showStudentInviteErrors ? old('name') : '' }}"
                            placeholder="Enter student's full name" required
                            class="{{ $showStudentInviteErrors && $errors->has('name') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" name="email" value="{{ $showStudentInviteErrors ? old('email') : '' }}"
                            placeholder="student@example.com" required
                            class="{{ $showStudentInviteErrors && $errors->has('email') ? 'is-invalid' : '' }}">
                        <p class="field-help">An account setup link will be sent to this email address.</p>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" name="contact" value="{{ $showStudentInviteErrors ? old('contact') : '' }}"
                                placeholder="9123456789" required maxlength="10"
                                class="{{ $showStudentInviteErrors && $errors->has('contact') ? 'is-invalid' : '' }}">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" name="address" value="{{ $showStudentInviteErrors ? old('address') : '' }}"
                            placeholder="Enter full address" required
                            class="{{ $showStudentInviteErrors && $errors->has('address') ? 'is-invalid' : '' }}">
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $showStudentInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No branches available.</strong> Please <a href="{{ school_route('admin.branches.index') }}"
                                class="fw-bold">create a branch</a> first to add users.
                        </div>
                    @endif

                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Assigned Course <span class="required-indicator">*</span></label>
                            <select name="course_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $showStudentInviteErrors && (string) old('course_id') === (string) $course->id ? 'selected' : '' }}>
                                        {{ $course->title }} ({{ ucfirst($course->course_type) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="field-help">Student will be automatically enrolled upon registration.</p>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active courses available.</strong> Please <a
                                href="{{ school_route('admin.courses.index') }}" class="fw-bold">create a course</a> first to
                            add students.
                        </div>
                    @endif
                    <input type="hidden" name="role" value="student">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateStudentModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--btn-gradient); color: var(--btn-primary-text); border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;"
                        {{ (!isset($branches) || $branches->count() == 0 || !isset($courses) || $courses->count() == 0) ? 'disabled' : '' }}>
                        Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT STUDENT MODAL -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Student</h3>
                <button type="button" class="btn-close-modal" onclick="closeEditStudentModal()">×</button>
            </div>
            <form id="editStudentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_student_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" id="edit_student_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="edit_student_contact" name="contact" required maxlength="10"
                                placeholder="9123456789">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_student_address" name="address" required>
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select id="edit_student_branch" name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditStudentModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--header-gradient); color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD INSTRUCTOR MODAL -->
    <div id="createInstructorModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-person-plus me-2"></i>Add New Instructor</h3>
                <button type="button" class="btn-close-modal" onclick="closeCreateInstructorModal()">×</button>
            </div>
            <form id="createInstructorInviteForm" method="POST" action="{{ school_route('admin.storeAccount') }}"
                data-no-ajax="1" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="modal-required-note">Fields marked with <span class="required-indicator">*</span> are
                        required.</p>

                    @if($showInstructorInviteErrors)
                        <div class="modal-error-summary" role="alert">
                            <strong>Please fix the following:</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" name="name" value="{{ $showInstructorInviteErrors ? old('name') : '' }}"
                            placeholder="Enter instructor's full name" required
                            class="{{ $showInstructorInviteErrors && $errors->has('name') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" name="email" value="{{ $showInstructorInviteErrors ? old('email') : '' }}"
                            placeholder="instructor@example.com" required
                            class="{{ $showInstructorInviteErrors && $errors->has('email') ? 'is-invalid' : '' }}">
                        <p class="field-help">An account setup link will be sent to this email address.</p>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" name="contact"
                                value="{{ $showInstructorInviteErrors ? old('contact') : '' }}" placeholder="9123456789"
                                required maxlength="10"
                                class="{{ $showInstructorInviteErrors && $errors->has('contact') ? 'is-invalid' : '' }}">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>License Number <span class="required-indicator">*</span></label>
                        <input type="text" name="license_number"
                            value="{{ $showInstructorInviteErrors ? old('license_number') : '' }}"
                            placeholder="Enter license number" required
                            class="{{ $showInstructorInviteErrors && $errors->has('license_number') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" name="address" value="{{ $showInstructorInviteErrors ? old('address') : '' }}"
                            placeholder="Enter full address" required
                            class="{{ $showInstructorInviteErrors && $errors->has('address') ? 'is-invalid' : '' }}">
                    </div>
                    <div class="form-group">
                        <label>License Image <span class="required-indicator">*</span></label>
                        <input type="file" name="license_image" accept="image/*" required
                            class="{{ $showInstructorInviteErrors && $errors->has('license_image') ? 'is-invalid' : '' }}">
                        <p class="field-help">Upload a photo of the instructor's license.</p>
                    </div>
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled selected>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $showInstructorInviteErrors && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No branches available.</strong> Please <a href="{{ school_route('admin.branches.index') }}"
                                class="fw-bold">create a branch</a> first to add instructors.
                        </div>
                    @endif
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Course Specializations <span class="required-indicator">*</span></label>
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                                @foreach($courses as $course)
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer; font-weight: normal; font-size: 0.9rem;">
                                        <input type="checkbox" name="course_specializations[]" value="{{ $course->id }}"
                                            style="width: auto;">
                                        {{ $course->title }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="field-help">Select courses this instructor is qualified to teach.</p>
                        </div>
                    @else
                        <div class="alert alert-warning" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>No active courses available.</strong> Please <a
                                href="{{ school_route('admin.courses.index') }}" class="fw-bold">create a course</a> first to
                            assign specializations.
                        </div>
                    @endif
                    <input type="hidden" name="role" value="instructor">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateInstructorModal()"
                        style="background: #64748b; color: white; border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: var(--btn-gradient); color: var(--btn-primary-text); border: none; height: 42px; padding: 0 20px; border-radius: 10px; font-weight: 600;"
                        {{ (!isset($branches) || $branches->count() == 0 || !isset($courses) || $courses->count() == 0) ? 'disabled' : '' }}>
                        Add Instructor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT INSTRUCTOR MODAL -->
    <div id="editInstructorModal" class="modal">
        <div class="modal-content" style="width: min(600px, 95%);">
            <div class="modal-header">
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Instructor</h3>
                <button type="button" class="btn-close-modal" onclick="closeEditInstructorModal()">×</button>
            </div>
            <form id="editInstructorForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required-indicator">*</span></label>
                        <input type="email" id="edit_instructor_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact <span class="required-indicator">*</span></label>
                        <div class="contact-input-group">
                            <span class="contact-prefix">+63</span>
                            <input type="text" id="edit_instructor_contact" name="contact" required maxlength="10"
                                placeholder="9123456789">
                        </div>
                        <p class="field-help">Enter the 10-digit number after +63 (e.g., 9123456789).</p>
                    </div>
                    <div class="form-group">
                        <label>License Number <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_license" name="license_number" required>
                    </div>
                    <div class="form-group">
                        <label>Address <span class="required-indicator">*</span></label>
                        <input type="text" id="edit_instructor_address" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>Availability <span class="required-indicator">*</span></label>
                        <select id="edit_instructor_availability" name="availability" required>
                            <option value="available">Available (Visible for Bookings)</option>
                            <option value="unavailable">Unavailable (Hidden from Bookings)</option>
                        </select>
                        <p class="field-help">Set if this instructor is currently available for session assignments.</p>
                    </div>
                    <div class="form-group">
                        <label>License Image</label>
                        <input type="file" name="license_image" accept="image/*">
                        <p class="field-help">Leave empty to keep current license image.</p>
                    </div>
                    @if(isset($courses) && $courses->count() > 0)
                        <div class="form-group">
                            <label>Course Specializations <span class="required-indicator">*</span></label>
                            <div id="edit_instructor_specializations"
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                                @foreach($courses as $course)
                                    <label
                                        style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer; font-weight: normal; font-size: 0.9rem;">
                                        <input type="checkbox" name="course_specializations[]" value="{{ $course->id }}"
                                            style="width: auto;">
                                        {{ $course->title }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="field-help">Select courses this instructor is qualified to teach.</p>
                        </div>
                    @endif
                    @if(isset($branches) && $branches->count() > 0)
                        <div class="form-group">
                            <label>Branch <span class="required-indicator">*</span></label>
                            <select id="edit_instructor_branch" name="branch_id" class="branch-modal-select" required>
                                <option value="" disabled>Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditInstructorModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Instructor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- GLOBALS ---
        window.schoolSlug = '{{ $school->slug }}';
        window.studentBaseUrl = '/{{ $school->slug }}/admin/students';
        window.instructorBaseUrl = '/{{ $school->slug }}/admin/instructors';
        
        // State for persistence across AJAX loads
        window.__userMgmtInitialFilterState = window.__userMgmtInitialFilterState || {
            search: @json($activeSearch ?? ''),
            status: @json($activeStatusFilter ?? 'all'),
            role: @json($activeRoleFilter ?? 'all'),
            branch: @json((string) ($activeBranchFilter ?? 'all'))
        };

        // --- PAGE INITIALIZATION ---
        function initializeUserManagementPage() {
            console.log('User Management: Initializing...');
            
            const searchInput = document.getElementById('userSearch');
            const branchFilter = document.getElementById('branchFilter');
            const state = window.__userMgmtInitialFilterState;

            if (searchInput) {
                // Restore search value from global state
                if (state.search) {
                    searchInput.value = state.search;
                }

                // Preserve focus and cursor position if we have a search term
                if (state.search && state.search.trim() !== '') {
                    searchInput.focus();
                    const len = searchInput.value.length;
                    searchInput.setSelectionRange(len, len);
                }

                // Debounced real-time search
                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    // Sync state immediately while typing to avoid loss on rapid keystrokes
                    state.search = this.value; 
                    debounceTimer = setTimeout(() => {
                        applyFilters();
                    }, 600);
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyFilters();
                    }
                });
            }

            // Sync branch filter dropdown
            if (branchFilter && state.branch) {
                branchFilter.value = state.branch;
            }
            
            // Close export dropdown on outside click
            document.addEventListener('click', function(e) {
                const exportDropdown = document.getElementById('exportDropdown');
                if (exportDropdown && !exportDropdown.contains(e.target)) {
                    exportDropdown.classList.remove('active');
                }
            });

            // Harden forms (Hostinger hack)
            hardenUserManagementActionForms();
        }

        function applyFilters() {
            const state = window.__userMgmtInitialFilterState;
            const search = document.getElementById('userSearch')?.value || '';
            const branch = document.getElementById('branchFilter')?.value || 'all';
            const role = state.role || 'all';
            const status = state.status || 'all';
            
            // Save state for persistence across AJAX loads
            window.__userMgmtInitialFilterState = { role, branch, search, status };

            const params = new URLSearchParams();
            if (role !== 'all') params.set('role', role);
            if (branch !== 'all') params.set('branch', branch); 
            if (search.trim()) params.set('search', search.trim());
            if (status !== 'all') params.set('status', status);
            
            const url = '{{ school_route('admin.userManagement') }}' + '?' + params.toString();
            
            if (window.loadContent) {
                loadContent(url);
            } else {
                window.location.href = url;
            }
        }

        function applyBranchFilter() { applyFilters(); }
        
        function applyQuickFilter(type) {
            const state = window.__userMgmtInitialFilterState;
            const currentBranch = document.getElementById('branchFilter')?.value || 'all';
            const currentSearch = document.getElementById('userSearch')?.value || '';
            
            // Reset filters based on card clicked
            if (type === 'instructors') {
                state.role = 'instructor';
                state.status = 'all';
            } else if (type === 'students') {
                state.role = 'student';
                state.status = 'all';
            } else if (type === 'active') {
                state.role = 'all';
                state.status = 'active';
            } else if (type === 'inactive') {
                state.role = 'all';
                state.status = 'inactive';
            } else {
                state.role = 'all';
                state.status = 'all';
            }
            
            state.branch = currentBranch;
            state.search = currentSearch;
            
            applyFilters();
        }

        function toggleExportDropdown() {
            const dropdown = document.getElementById('exportDropdown');
            if (dropdown) dropdown.classList.toggle('active');
        }

        function hardenUserManagementActionForms() {
            const actionForms = document.querySelectorAll('form[data-no-ajax][action*="/toggle-status"], form[data-no-ajax][action*="/availability"]');
            actionForms.forEach(form => {
                form.classList.add('native-form');
                form.setAttribute('data-no-ajax', '1');
            });
        }

        // --- USER VIEW & EDIT MODAL ---
        let currentEditingData = null;

        function openUserViewModal(data) {
            currentEditingData = data;
            const modal = document.getElementById('userViewModal');
            if (modal) {
                modal.style.display = 'flex';
                renderUserViewState();
            }
        }

        function closeUserViewModal() {
            const modal = document.getElementById('userViewModal');
            if (modal) modal.style.display = 'none';
            currentEditingData = null;
        }

        function renderUserViewState() {
            const content = document.getElementById('userViewContent');
            const data = currentEditingData;
            if (!content || !data) return;
            
            const modal = document.getElementById('userViewModal');
            const modalContent = modal.querySelector('.modal-content');
            modalContent.style.width = 'min(750px, 95%)';

            let instructorSection = '';
            if (data.role === 'instructor') {
                instructorSection = `
                    <div class="profile-section">
                        <div class="profile-section-title"><i class="bi bi-card-text"></i>Instructor Information</div>
                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">License Number</div>
                                <div class="profile-info-value">${data.license || 'N/A'}</div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Availability</div>
                                <div class="profile-info-value">
                                    <span class="status-badge status-${data.availability === 'available' ? 'active' : 'inactive'}">
                                        ${data.availability === 'available' ? 'AVAILABLE' : 'UNAVAILABLE'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            content.innerHTML = `
                <div class="profile-modal-header" style="padding: 25px;">
                    <button type="button" class="btn-close-modal" onclick="closeUserViewModal()">×</button>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div class="profile-modal-avatar">
                            ${data.profilePicture ? `<img src="${data.profilePicture}" style="width: 100%; height: 100%; object-fit: cover;">` : data.name.charAt(0)}
                        </div>
                        <div>
                            <h3 class="profile-modal-name">${data.name}</h3>
                            <span class="profile-modal-role" style="background: ${data.role === 'instructor' ? '#e0f2fe' : '#fef3c7'}; color: ${data.role === 'instructor' ? '#0369a1' : '#92400e'};">
                                ${data.role.toUpperCase()}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-modal-body">
                    <div class="profile-section">
                        <div class="profile-section-title"><i class="bi bi-person-lines-fill"></i>Contact Information</div>
                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Email Address</div>
                                <div class="profile-info-value">${data.email}</div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Contact Number</div>
                                <div class="profile-info-value">
                                    <span class="masked-pii" data-full="${data.contact}">${data.contact ? (data.contact.substring(0, 6) + '****') : 'N/A'}</span>
                                    <button class="btn-reveal-pii" onclick="revealPII(this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="profile-info-item" style="grid-column: span 2;">
                                <div class="profile-info-label">Home Address</div>
                                <div class="profile-info-value">${data.address || 'N/A'}</div>
                            </div>
                        </div>
                    </div>

                    ${instructorSection}

                    <div class="profile-section">
                        <div class="profile-section-title"><i class="bi bi-shield-lock"></i>Account Status</div>
                        <div class="profile-info-grid">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Assigned Branch</div>
                                <div class="profile-info-value">${data.branchName || 'Not Assigned'}</div>
                            </div>
                            <div class="profile-info-item">
                                <div class="profile-info-label">Current Status</div>
                                <div class="profile-info-value">
                                    <span class="status-badge status-${data.status}">${data.status.toUpperCase()}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserViewModal()" style="border-radius: 10px; font-weight: 600;">Close</button>
                    ${data.role === 'instructor' && data.licenseStatus !== 'verified' ? `
                        <button type="button" class="btn btn-success" onclick="toggleLicenseVerificationMode(true)" style="background: #10b981; border: none; border-radius: 10px; font-weight: 600; color: white;">
                            <i class="bi bi-shield-check me-2"></i>Verify License
                        </button>
                    ` : ''}
                </div>
            `;
        }

        function toggleLicenseVerificationMode(isVerify) {
            if (!isVerify) {
                renderUserViewState();
                return;
            }

            const content = document.getElementById('userViewContent');
            const data = currentEditingData;

            let restrictions = [];
            try {
                restrictions = typeof data.restrictions === 'string' ? JSON.parse(data.restrictions) : (data.restrictions || []);
            } catch (e) { restrictions = []; }

            const restrictionCodes = ['A', 'A1', 'B', 'B1', 'B2', 'C', 'D', 'BE', 'CE'];
            const restrictionHtml = restrictionCodes.map(code => `
                <label class="restriction-chip" style="cursor: pointer;">
                    <input type="checkbox" name="restriction_codes[]" value="${code}" ${restrictions.includes(code) ? 'checked' : ''} style="display: none;">
                    <div class="chip-content" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; text-align: center; font-weight: 600; background: white; transition: all 0.2s; min-width: 80px;">
                        ${code}
                    </div>
                </label>
            `).join('');

            const modal = document.getElementById('userViewModal');
            const modalContent = modal.querySelector('.modal-content');
            modalContent.style.width = 'min(1100px, 98%)';

            content.innerHTML = `
                <div class="profile-modal-header" style="padding: 20px 25px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); display: flex; align-items: center; justify-content: space-between; border-radius: 20px 20px 0 0;">
                    <h3 style="font-size: 1.25rem; margin: 0; color: white !important; font-weight: 600;"><i class="bi bi-shield-check me-2"></i>Verify Instructor License</h3>
                    <button type="button" onclick="closeUserViewModal()" style="background: rgba(255,255,255,0.2); border: none; border-radius: 8px; color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
                </div>
                
                <form id="integratedVerifyForm" style="margin: 0;">
                    <div class="profile-modal-body" style="padding: 35px; background: white; position: relative;">
                        <div class="row">
                            <!-- LEFT COLUMN: Details & Decision -->
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Instructor Details</p>
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px;">
                                        <div style="margin-bottom: 8px; display: flex; align-items: center;">
                                            <span style="font-weight: 700; color: #1e293b; width: 130px; font-size: 0.9rem;">Full Name:</span>
                                            <span style="color: #334155; font-weight: 500;">${data.name}</span>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <span style="font-weight: 700; color: #1e293b; width: 130px; font-size: 0.9rem;">License Number:</span>
                                            <span style="color: #3b82f6; font-family: monospace; font-weight: 700;">${data.license || 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Verification Decision</p>
                                    <div style="display: flex; gap: 20px;">
                                        <label style="flex: 1; cursor: pointer;">
                                            <input type="radio" name="status" value="verified" ${data.licenseStatus === 'verified' ? 'checked' : ''} onchange="toggleIntegratedRejection(false)" style="display:none;">
                                            <div class="decision-btn" style="border: 2px solid #10b981; background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 12px; text-align: center; font-weight: 700; transition: 0.2s;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Approve
                                            </div>
                                        </label>
                                        <label style="flex: 1; cursor: pointer;">
                                            <input type="radio" name="status" value="rejected" ${data.licenseStatus === 'rejected' ? 'checked' : ''} onchange="toggleIntegratedRejection(true)" style="display:none;">
                                            <div class="decision-btn" style="border: 2px solid #e2e8f0; background: white; color: #64748b; padding: 15px; border-radius: 12px; text-align: center; font-weight: 700; transition: 0.2s;">
                                                <i class="bi bi-x-circle-fill me-1"></i> Reject
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN: License Image (Pinned Top Right) -->
                            <div class="col-md-5">
                                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-align: right;">License Document</p>
                                <div style="background: #0f172a; border-radius: 20px; height: 230px; display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: pointer; border: 4px solid #f1f5f9; box-shadow: 0 10px 25px rgba(0,0,0,0.1);" onclick="openLightbox('${data.licenseImage}')">
                                    <img src="${data.licenseImage}" style="max-width: 90%; max-height: 90%; border-radius: 8px; box-shadow: 0 15px 40px rgba(0,0,0,0.5);">
                                </div>
                                <p style="text-align: center; font-size: 0.8rem; color: #94a3b8; margin-top: 12px; font-style: italic;">Carefully verify the name and expiry date against the physical ID</p>
                            </div>
                        </div>

                        <!-- RESTRICTIONS (Full Width) -->
                        <div id="integrated_restrictions_group" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9;">
                            <h5 style="font-weight: 700; font-size: 1.05rem; color: #1e293b; margin-bottom: 5px;">LTO Restriction Codes</h5>
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">Check all codes indicated on the instructor's physical license.</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                                ${restrictionHtml}
                            </div>
                        </div>

                        <!-- REJECTION REASON (Full Width) -->
                        <div id="integrated_rejection_group" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9; display: none;">
                            <h5 style="font-weight: 700; font-size: 1.05rem; color: #ef4444; margin-bottom: 12px;">Reason for Rejection</h5>
                            <textarea name="rejection_reason" class="form-control" rows="4" style="border-radius: 15px; border: 2px solid #fee2e2; padding: 20px; font-size: 0.95rem; background: #fffafb;" placeholder="Please provide a detailed reason for rejecting this license...">${data.rejectionReason || ''}</textarea>
                        </div>
                    </div>

                    <div style="padding: 25px 35px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 15px; border-radius: 0 0 20px 20px;">
                        <button type="button" class="btn" onclick="toggleLicenseVerificationMode(false)" style="background: #94a3b8; color: white; padding: 12px 30px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; border: none; transition: 0.2s;">Cancel</button>
                        <button type="submit" class="btn" id="saveVerifyBtn" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 12px 40px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; border: none; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); transition: 0.2s;">Save Verification</button>
                    </div>
                </form>
            `;

            initIntegratedVerificationStyles();
            document.getElementById('integratedVerifyForm').addEventListener('submit', saveIntegratedVerification);
            
            if (data.licenseStatus === 'rejected') {
                toggleIntegratedRejection(true);
            }
        }

        function initIntegratedVerificationStyles() {
            const restrictionInputs = document.querySelectorAll('.restriction-chip input');
            restrictionInputs.forEach(input => {
                const chip = input.nextElementSibling;
                const updateChip = () => {
                    if (input.checked) {
                        chip.style.borderColor = '#6366f1';
                        chip.style.backgroundColor = '#eef2ff';
                        chip.style.color = '#4f46e5';
                    } else {
                        chip.style.borderColor = '#e2e8f0';
                        chip.style.backgroundColor = 'white';
                        chip.style.color = '#1e293b';
                    }
                };
                input.addEventListener('change', updateChip);
                updateChip();
            });

            const decisionOptions = document.querySelectorAll('input[name="status"]');
            decisionOptions.forEach(input => {
                const updateDecision = () => {
                    decisionOptions.forEach(opt => {
                        const btn = opt.nextElementSibling;
                        if (opt.checked) {
                            if (opt.value === 'verified') {
                                btn.style.borderColor = '#10b981';
                                btn.style.background = '#ecfdf5';
                                btn.style.color = '#065f46';
                            } else {
                                btn.style.borderColor = '#ef4444';
                                btn.style.background = '#fef2f2';
                                btn.style.color = '#991b1b';
                            }
                        } else {
                            btn.style.borderColor = '#e2e8f0';
                            btn.style.background = 'white';
                            btn.style.color = '#64748b';
                        }
                    });
                };
                input.addEventListener('change', updateDecision);
                updateDecision();
            });
        }

        function toggleIntegratedRejection(show) {
            const restGrp = document.getElementById('integrated_restrictions_group');
            const rejGrp = document.getElementById('integrated_rejection_group');
            if (restGrp) restGrp.style.display = show ? 'none' : 'block';
            if (rejGrp) rejGrp.style.display = show ? 'block' : 'none';
        }

        async function saveIntegratedVerification(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('saveVerifyBtn');
            const data = currentEditingData;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            const formData = new FormData(form);
            const url = `{{ school_route('admin.instructors.verify', ['id' => ':id']) }}`.replace(':id', data.id);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.ok) {
                    showToast('success', 'License verification saved!');
                    closeUserViewModal();
                    loadContent('{{ school_route('admin.userManagement') }}');
                } else {
                    const res = await response.json();
                    alert('Error: ' + (res.message || 'Unknown error'));
                }
            } catch (err) {
                console.error(err);
                alert('A network error occurred.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Save Verification';
            }
        }

        function revealPII(button) {
            const span = button.previousElementSibling;
            if (!span || span.classList.contains('revealed')) return;
            const fullValue = span.getAttribute('data-full');
            const originalMasked = span.textContent;
            span.textContent = fullValue;
            span.classList.add('revealed');
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="bi bi-clock-history"></i>';
            button.classList.add('timer-active');
            button.disabled = true;
            setTimeout(() => {
                span.textContent = originalMasked;
                span.classList.remove('revealed');
                button.innerHTML = originalIcon;
                button.classList.remove('timer-active');
                button.disabled = false;
            }, 5000);
        }

        function unifiedToggle(id, type, currentValue, role) {
            const action = (type === 'status')
                ? (currentValue === 'active' ? 'Deactivate' : 'Activate')
                : (currentValue === 'available' ? 'Mark Unavailable' : 'Mark Available');

            const typeLabel = (type === 'status') ? 'Account Status' : 'Availability';

            showConfirm({
                type: (currentValue === 'active' || currentValue === 'available') ? 'warning' : 'success',
                title: `${action} ${role.charAt(0).toUpperCase() + role.slice(1)}`,
                message: `Are you sure you want to ${action.toLowerCase()} this ${role}'s ${typeLabel.toLowerCase()}?`,
                confirmText: `Yes, ${action}`,
                onConfirm: async () => {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    let url = '';
                    if (type === 'availability') {
                        url = '/' + window.schoolSlug + '/admin/instructors/' + id + '/availability';
                    } else {
                        const basePart = (role === 'instructor') ? 'instructors' : 'students';
                        url = '/' + window.schoolSlug + '/admin/' + basePart + '/' + id + '/toggle-status';
                    }

                    try {
                        const response = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok || response.status === 200) {
                            showToast('success', `${typeLabel} updated successfully!`);
                            loadContent(window.location.pathname + window.location.search);
                        } else {
                            showToast('error', 'Failed to update. Please try again.');
                        }
                    } catch (error) {
                        console.error('Toggle error:', error);
                        showToast('error', 'A network error occurred.');
                    }
                }
            });
        }

        // --- GLOBAL EVENT DELEGATION ---
        document.addEventListener('click', function (e) {
            const viewBtn = e.target.closest('.js-view-user');
            if (viewBtn) {
                e.preventDefault();
                openUserViewModal({
                    id: viewBtn.dataset.id,
                    role: viewBtn.dataset.role,
                    name: viewBtn.dataset.name,
                    email: viewBtn.dataset.email,
                    contact: viewBtn.dataset.contact,
                    address: viewBtn.dataset.address,
                    branchId: viewBtn.dataset.branch,
                    branchName: viewBtn.dataset.branchName,
                    status: viewBtn.dataset.status,
                    license: viewBtn.dataset.license,
                    availability: viewBtn.dataset.availability,
                    licenseImage: viewBtn.dataset.licenseImage,
                    licenseStatus: viewBtn.dataset.licenseStatus,
                    restrictions: viewBtn.dataset.restrictions,
                    rejectionReason: viewBtn.dataset.rejectionReason,
                    profilePicture: viewBtn.dataset.profilePicture
                });
                return;
            }

            const verifyBtn = e.target.closest('.js-verify-license');
            if (verifyBtn) {
                e.preventDefault();
                openUserViewModal({
                    id: verifyBtn.dataset.id,
                    role: 'instructor',
                    name: verifyBtn.dataset.name,
                    license: verifyBtn.dataset.licenseNumber,
                    licenseImage: verifyBtn.dataset.licenseImage,
                    licenseStatus: verifyBtn.dataset.status || 'pending',
                    restrictions: verifyBtn.dataset.restrictions || '[]',
                    rejectionReason: verifyBtn.dataset.rejectionReason || '',
                    email: verifyBtn.closest('tr')?.querySelector('td:nth-child(2)')?.textContent?.trim() || '',
                    contact: verifyBtn.closest('tr')?.querySelector('td:nth-child(3)')?.textContent?.trim() || '',
                    branchName: verifyBtn.closest('tr')?.querySelector('td:nth-child(5)')?.textContent?.trim() || '',
                    status: verifyBtn.closest('tr')?.dataset.status || 'active'
                });
                toggleLicenseVerificationMode(true);
                return;
            }
        });

        // --- LEGACY MODAL HELPERS ---
        function openCreateStudentModal() { document.getElementById('createStudentModal').style.display = 'flex'; }
        function closeCreateStudentModal() { document.getElementById('createStudentModal').style.display = 'none'; }
        function openCreateInstructorModal() { document.getElementById('createInstructorModal').style.display = 'flex'; }
        function closeCreateInstructorModal() { document.getElementById('createInstructorModal').style.display = 'none'; }

        // Final Initialization
        initializeUserManagementPage();
    </script>

    

@endsection