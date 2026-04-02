@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'Manage Schedule')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $schoolName = $school->name ?? 'Driving School';
    $instructors = $instructors ?? collect();
    $currentFilter = request('type', 'all');
    $primaryColor = $settings->primary_color ?? '#3b82f6';
    $secondaryColor = $settings->secondary_color ?? '#60a5fa';
@endphp

@include('school.admin.partials.admin-styles')

<style>
    /* Custom Button Styles (replacing Bootstrap) */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        display: inline-block;
        text-align: center;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    /* Alert Styles (replacing Bootstrap alerts) */
    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        position: relative;
    }
    
    .alert-success {
        background: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }
    
    .alert-danger {
        background: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
    
    .alert .close-btn {
        position: absolute;
        right: 15px;
        top: 15px;
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
    }
    
    .alert .close-btn:hover {
        opacity: 1;
    }
    
    /* Badge Styles (replacing Bootstrap badges) */
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-light {
        background: #f3f4f6;
        color: #374151;
    }
    
    .badge-success {
        background: #10b981;
        color: white;
    }
    
    .badge-primary {
        background: #3b82f6;
        color: white;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: white;
    }
    
    /* Form Styles (replacing Bootstrap forms) */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        outline: none;
        border-color: {{ $primaryColor }};
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    
    /* Checkbox Styles */
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-label {
        cursor: pointer;
        user-select: none;
    }
    
    /* Grid System (replacing Bootstrap grid) */
    .row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .col {
        flex: 1;
    }
    
    .col-half {
        flex: 0 0 calc(50% - 7.5px);
    }
    
    .col-third {
        flex: 0 0 calc(33.333% - 10px);
    }
    
    .col-two-thirds {
        flex: 0 0 calc(66.666% - 5px);
    }
    
    /* Margin/Padding Utilities */
    .mb-3 {
        margin-bottom: 20px;
    }
    
    .me-2 {
        margin-right: 10px;
    }
    
    /* Text Utilities */
    .text-muted {
        color: #6b7280;
    }
    
    .text-center {
        text-align: center;
    }

    .text-danger {
        color: #ef4444;
    }

    .filter-wrap {
        margin-bottom: 20px;
        padding: 16px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .filter-field {
        min-width: 180px;
        flex: 1 1 180px;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .filter-note {
        margin-top: 10px;
        color: #6b7280;
        font-size: 0.85rem;
    }
    
    /* Container Styles */
    .timeslots-container {
        padding: 20px;
        margin: 20px auto;
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

    .icon-20 {
        width: 20px;
        height: 20px;
    }

    .center-toggle-wrap {
        display: flex;
        justify-content: center;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .chevron-icon {
        margin-left: 10px;
        transition: transform 0.3s;
    }

    .course-badge {
        margin-bottom: 12px;
        padding: 8px 12px;
        background: var(--header-gradient);
        color: white;
        border-radius: 8px;
        display: inline-block;
        font-weight: 500;
        box-shadow: var(--brand-shadow);
    }

    .course-type {
        opacity: 0.9;
        font-size: 0.85em;
        margin-left: 5px;
    }

    .count-admin {
        color: #3b82f6;
    }

    .count-self {
        color: #10b981;
    }

    .mt-12 {
        margin-top: 12px;
    }

    .timeslot-notes {
        margin-top: 12px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #666;
    }

    .mr-8 {
        margin-right: 8px;
    }

    .hidden-form {
        display: none;
    }

    .slot-badge-more {
        background: #6b7280;
        color: white;
    }

    .calendar-legend {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 0.9rem;
        color: #666;
    }

    .legend-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }

    .legend-indicator-open {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        margin: 0 5px;
    }

    .legend-indicator-assigned {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        margin: 0 5px 0 15px;
    }

    .legend-help {
        margin-top: 8px;
        display: block;
    }

    .info-panel {
        padding: 15px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .info-panel-list {
        margin: 10px 0 0 0;
        padding-left: 20px;
        font-size: 0.9rem;
    }

    .info-panel-toggle {
        display: none;
        width: 100%;
        border: 1px solid #bfdbfe;
        background: #ffffff;
        color: #1d4ed8;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 10px;
        margin-top: 10px;
        cursor: pointer;
        text-align: left;
    }

    .no-courses-msg {
        padding: 20px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 8px;
    }

    .instructors-count {
        color: #888;
    }

    .no-instructors-msg {
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .loading-center {
        text-align: center;
        padding: 40px;
    }

    .day-modal-content {
        max-width: 800px;
    }

    .day-modal-title {
        margin: 0;
    }

    .day-modal-date {
        color: rgba(255,255,255,0.9);
        display: block;
        margin-top: 5px;
    }

    .details-wrap {
        padding: 10px;
    }

    .details-section {
        margin-bottom: 20px;
    }

    .details-label {
        color: #666;
        display: block;
        margin-bottom: 8px;
    }

    .details-time {
        font-size: 1.1rem;
    }

    .details-notes {
        background: #f9fafb;
        padding: 12px;
        border-radius: 6px;
        min-height: 40px;
    }

    .day-empty {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .day-empty-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .day-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .day-card {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        background: #f9fafb;
        transition: all 0.2s;
    }

    .day-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .day-card-time {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .day-card-meta {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #666;
    }

    .day-counts {
        font-size: 0.85rem;
        color: #999;
    }

    .day-instructors {
        margin-bottom: 8px;
    }

    .day-instructors-label {
        color: #666;
    }

    .day-notes {
        margin-top: 10px;
        padding: 10px;
        background: white;
        border-radius: 4px;
        font-size: 0.9rem;
        color: #666;
    }

    .multi-select-auto {
        height: auto;
    }
    
    /* View Toggle Styles */
    .view-toggle {
        display: flex;
        gap: 0;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
    }
    
    .view-btn {
        padding: 8px 20px;
        background: transparent;
        color: #4b5563;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .view-btn.active {
        background: {{ $primaryColor }};
        color: white;
    }
    
    .view-btn:hover:not(.active) {
        background: #e5e7eb;
    }
    
    .view-content {
        display: none;
    }
    
    .view-content.active {
        display: block;
    }
    
    /* Filter Dropdown Styles */
    .filter-dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 30px;
    }
    
    .filter-dropdown select {
        padding: 12px 40px 12px 16px;
        border: 2px solid {{ $primaryColor }};
        border-radius: 8px;
        background: white;
        color: #333;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        appearance: none;
        min-width: 200px;
        transition: all 0.3s ease;
    }
    
    .filter-dropdown select:hover {
        border-color: {{ $secondaryColor }};
        box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.2);
    }
    
    .filter-dropdown select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    
    .filter-dropdown::after {
        content: '▼';
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: {{ $primaryColor }};
        font-size: 12px;
    }
    
    .filter-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .info-panel {
        padding: 15px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .info-panel-list {
        margin: 10px 0 0 0;
        padding-left: 20px;
        font-size: 0.9rem;
    }

    .info-panel-toggle {
        display: none;
        width: 100%;
        border: 1px solid #bfdbfe;
        background: #ffffff;
        color: #1d4ed8;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 10px;
        margin-top: 10px;
        cursor: pointer;
        text-align: left;
    }
    
    .btn-create {
        padding: 10px 20px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 30px;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #666;
        font-weight: 500;
        padding: 12px 24px;
        transition: all 0.3s;
    }
    
    .nav-tabs .nav-link:hover {
        color: {{ $primaryColor }};
        background: #f9fafb;
    }
    
    .nav-tabs .nav-link.active {
        color: {{ $primaryColor }};
        border-bottom: 3px solid {{ $primaryColor }};
        background: transparent;
    }
    
    .timeslot-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .timeslot-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .date-header {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        padding: 15px 20px;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.3s;
    }
    
    .date-header:hover {
        filter: brightness(0.95);
    }
    
    .date-header.collapsed .bi-chevron-down {
        transform: rotate(-90deg);
    }
    
    .timeslot-card .card-body {
        padding: 20px;
        transition: all 0.3s ease;
        max-height: 5000px;
        overflow: hidden;
    }
    
    .timeslot-card .card-body.collapsed {
        max-height: 0;
        padding: 0 20px;
        overflow: hidden;
    }
    
    .timeslot-item {
        background: #f9fafb;
        border-left: 4px solid transparent;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 12px;
        transition: all 0.3s;
    }
    
    .timeslot-item.type-open {
        border-left-color: #10b981;
    }
    
    .timeslot-item.type-assigned {
        border-left-color: #3b82f6;
    }
    
    .timeslot-row {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .timeslot-time {
        flex: 0 0 250px;
    }
    
    .timeslot-details {
        flex: 1;
    }
    
    .timeslot-actions {
        flex: 0 0 100px;
        text-align: right;
    }
    
    .time-badge {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .type-badge {
        font-size: 0.75rem;
        padding: 4px 12px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .instructor-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 8px;
    }
    
    .instructor-info .info-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #6b7280;
        font-size: 0.9rem;
    }
    
    .instructor-info .info-item i {
        color: #9ca3af;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }
    
    .empty-state p {
        color: #9ca3af;
        font-size: 1.1rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    
    .btn-sm-custom {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 5px;
        transition: all 0.2s;
    }
    
    .modal-header.modal-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .modal-header.modal-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .modal-header h5 {
        margin: 0;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }
    
    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 10px 12px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
    }
    
    /* Multi-select styling */
    select[multiple] {
        padding: 8px !important;
        background: #f9fafb !important;
        min-height: 180px !important;
        display: block !important;
        width: 100% !important;
        border: 2px solid #d1d5db !important;
    }
    
    select[multiple] option {
        padding: 8px 10px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
    }
    
    select[multiple] option:hover {
        background: #e0e7ff;
    }
    
    select[multiple] option:checked {
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        font-weight: 500;
    }
    
    .instructor-list {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        max-height: 250px;
        overflow-y: auto;
        background: #f9fafb;
        margin-bottom: 8px;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .instructor-list::-webkit-scrollbar {
        display: none;
    }
    
    .instructor-checkbox {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    
    .instructor-checkbox:hover {
        background: white;
        border-color: {{ $primaryColor }};
    }
    
    .instructor-checkbox:has(.checkbox:checked) {
        background: #f0f4ff;
        border-color: {{ $primaryColor }};
    }
    
    .instructor-checkbox:has(.checkbox:checked) .checkbox-label {
        color: {{ $primaryColor }};
        font-weight: 500;
    }
    
    /* Custom Modal Styles - Optimized for Performance */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background: white;
        width: 600px;
        max-width: 92%;
        border-radius: 19px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        will-change: auto;
    }
    
    .modal-header {
        position: relative;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        color: white;
        padding: 32px;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header.modal-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .modal-header.modal-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .modal-header h5 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
    }
    
    .modal-header .btn-close {
        position: absolute;
        right: 24px;
        top: 24px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 24px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .modal-header .btn-close:hover {
        background: rgba(255,255,255,0.3);
    }
    
    .modal-body {
        padding: 32px;
        max-height: 70vh;
        overflow-y: auto;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .modal-body::-webkit-scrollbar {
        display: none;
    }
    
    .modal-footer {
        padding: 20px 32px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-radius: 0 0 16px 16px;
    }
    
    /* Calendar View Styles */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        border-radius: 10px;
        color: white;
    }
    
    .calendar-nav {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .nav-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .nav-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    
    .current-month {
        font-size: 1.4rem;
        font-weight: 600;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-top: 20px;
    }
    
    .calendar-day-header {
        text-align: center;
        font-weight: 600;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
        color: #495057;
        font-size: 0.9rem;
    }
    
    .calendar-day {
        min-height: 120px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px;
        background: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .calendar-day:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 12px var(--brand-shadow);
        transform: translateY(-2px);
    }
    
    .calendar-day.other-month {
        background: #f8f9fa;
        opacity: 0.5;
    }
    
    .calendar-day.today {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(var(--primary-rgb), 0.02) 100%);
    }
    
    .calendar-day.has-schedule {
        border-color: #4CAF50;
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.05) 0%, rgba(76, 175, 80, 0.02) 100%);
    }
    
    .calendar-day.clickable {
        cursor: pointer;
    }
    
    .calendar-day.clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px var(--brand-shadow);
    }
    
    .day-number {
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 8px;
    }
    
    .day-slots {
        font-size: 0.75rem;
        margin-top: 5px;
    }
    
    .slot-badge {
        display: block;
        padding: 4px 6px;
        margin: 3px 0;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .slot-badge.assigned {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    
    .slot-badge.open {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    /* Export Buttons */
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .btn-export {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .btn-export-pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    
    .btn-export-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        color: white;
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .timeslots-container {
            padding: 15px;
            margin: 10px auto;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .page-title {
            font-size: 1.4rem;
        }
        
        .page-subtitle {
            font-size: 0.85rem;
        }
        
        .view-toggle {
            width: 100%;
        }
        
        .view-btn {
            flex: 1;
            padding: 10px 15px;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .filter-dropdown {
            width: 100%;
        }
        
        .filter-dropdown select {
            width: 100%;
            min-width: auto;
        }
        
        .row {
            flex-direction: column;
            gap: 12px;
        }
        
        .col-half, .col-third, .col-two-thirds {
            flex: 1 1 100%;
        }
        
        .timeslot-card {
            margin-bottom: 15px;
        }
        
        .date-header {
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .timeslot-table th,
        .timeslot-table td {
            padding: 10px 8px;
            font-size: 0.85rem;
        }

        .info-panel {
            margin-bottom: 12px;
            padding: 12px;
        }

        .info-panel-list {
            display: none;
            margin-top: 8px;
        }

        .info-panel.expanded .info-panel-list {
            display: block;
        }

        .info-panel-toggle {
            display: block;
        }
        
        .slot-badge {
            font-size: 0.65rem;
            padding: 3px 5px;
        }

        .info-panel {
            margin-bottom: 12px;
            padding: 12px;
        }

        .info-panel-list {
            display: none;
            margin-top: 8px;
        }

        .info-panel.expanded .info-panel-list {
            display: block;
        }

        .info-panel-toggle {
            display: block;
        }
        
        .modal-content {
            width: 95%;
            max-width: 95%;
            margin: 10px;
        }
        
        .modal-header {
            padding: 20px;
        }
        
        .modal-header h5 {
            font-size: 1.3rem;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            flex-direction: column;
            gap: 10px;
        }
        
        .modal-footer .btn {
            width: 100%;
        }
        
        /* Calendar View Responsive */
        .calendar-grid {
            gap: 3px;
        }
        
        .calendar-day {
            min-height: 60px;
            padding: 5px;
        }
        
        .day-number {
            font-size: 0.9rem;
        }
        
        .day-slots {
            display: none;
        }
        
        .calendar-day.has-slots::after {
            content: '•';
            display: block;
            color: #3b82f6;
            font-size: 1.5rem;
            line-height: 1;
        }
        
        .btn-create {
            width: 100%;
            justify-content: center;
        }
        
        .export-buttons {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .timeslots-container {
            padding: 10px;
            margin: 5px auto;
        }
        
        .page-title {
            font-size: 1.2rem;
        }
        
        .view-btn {
            padding: 8px 10px;
            font-size: 0.8rem;
        }
        
        .timeslot-table th,
        .timeslot-table td {
            padding: 8px 6px;
            font-size: 0.8rem;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
        }
        
        .calendar-day {
            min-height: 50px;
            padding: 3px;
        }
        
        .day-number {
            font-size: 0.8rem;
        }
        
        .form-control {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
        
        .form-label {
            font-size: 0.85rem;
        }
        
        .btn-export {
            padding: 8px 12px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="timeslots-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Schedule</h1>
            <p class="page-subtitle">Manage TDC and PDC sessions with verified student capacity.</p>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
            <div class="view-toggle">
                <button type="button" class="view-btn active" data-view-toggle="list" onclick="switchView('list')">List View</button>
                <button type="button" class="view-btn" data-view-toggle="calendar" onclick="switchView('calendar')">Calendar View</button>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" id="createScheduleBtn" class="btn btn-success btn-create" onclick="openCreateModal(); return false;">
                    <i class="bi bi-calendar-plus"></i> Create Schedule
                </button>
                <div class="export-buttons" style="margin-top: 0;">
                    <a href="{{ school_route('admin.exports.schedules.pdf') }}" class="btn-export btn-export-pdf">
                        Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>



    <div class="filter-wrap">
        <form method="GET" action="{{ route('schools.admin.schedules', $school) }}" class="filter-form">
            <div class="filter-field">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ old('start_date', $startDate) }}" required>
            </div>
            <div class="filter-field">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ old('end_date', $endDate) }}" required>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('schools.admin.schedules', $school) }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        @if($errors->has('start_date') || $errors->has('end_date') || $errors->has('month'))
            <div class="text-danger" style="margin-top: 10px;">
                {{ $errors->first('start_date') ?: ($errors->first('end_date') ?: $errors->first('month')) }}
            </div>
        @endif
        <div class="filter-note">
            Month navigation applies a full-month date range and overrides custom start/end dates.
        </div>
    </div>


    <!-- List View -->
    <div id="list-view" class="view-content active">
    @if($timeslots->isEmpty())
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p>No schedules found. Create your first schedule above!</p>
        </div>
    @else
        @foreach($timeslots as $date => $dateTimeslots)
            <div class="timeslot-card">
                <div class="date-header cursor-pointer" onclick="toggleDate(this)">
                    <span>
                        <i class="bi bi-calendar3"></i> 
                        {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                    </span>
                    <span>
                        <span class="badge badge-light">{{ \Carbon\Carbon::parse($date)->format('l') }}</span>
                        <i class="bi bi-chevron-down chevron-icon"></i>
                    </span>
                </div>
                <div class="card-body">
                    @foreach($dateTimeslots as $timeslot)
                        @php
                            $adminCount = $timeslot->getAdminAssignedCount();
                            $selfCount = $timeslot->getSelfSelectedCount();
                            $totalCount = $timeslot->instructors->count();
                            $availableSpots = $timeslot->getAvailableSpots();
                        @endphp
                        <div class="timeslot-item" 
                             data-slot-id="{{ $timeslot->id }}"
                             data-date="{{ $timeslot->date }}"
                             data-start-time="{{ $timeslot->start_time }}"
                             data-end-time="{{ $timeslot->end_time }}"
                             data-max-instructors="{{ $timeslot->max_instructors }}"
                             data-notes="{{ $timeslot->notes ?? '' }}">
                            <div class="timeslot-row">
                                <div class="timeslot-time">
                                    <div class="time-badge">
                                        <i class="bi bi-clock"></i>
                                        {{ \Carbon\Carbon::parse($timeslot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($timeslot->end_time)->format('g:i A') }}
                                    </div>
                                    <br>
                                    @if($timeslot->getAvailableStudentSpots() > 0)
                                        <span class="badge badge-success">
                                            {{ $timeslot->getAvailableStudentSpots() }} Spot{{ $timeslot->getAvailableStudentSpots() > 1 ? 's' : '' }} Available
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            ✓ Full
                                        </span>
                                    @endif
                                </div>
                                <div class="timeslot-details">
                                    @if($timeslot->course)
                                        <div class="course-badge">
                                            <i class="bi bi-book"></i> {{ $timeslot->course->title }}
                                            <span class="course-type">({{ ucfirst($timeslot->course->type) }})</span>
                                        </div>
                                    @endif
                                    <div class="instructor-info">
                                        <div class="info-item">
                                            <i class="bi bi-people-fill"></i>
                                            <strong>{{ $totalCount }}/{{ $timeslot->max_instructors }}</strong> Instructors
                                            @if($adminCount > 0 || $selfCount > 0)
                                                <small class="text-muted">
                                                    (
                                                    @if($adminCount > 0)
                                                        <span class="count-admin">{{ $adminCount }} admin</span>
                                                    @endif
                                                    @if($adminCount > 0 && $selfCount > 0), @endif
                                                    @if($selfCount > 0)
                                                        <span class="count-self">{{ $selfCount }} self</span>
                                                    @endif
                                                    )
                                                </small>
                                            @endif
                                        </div>
                                        <div class="info-item">
                                            <i class="bi bi-circle-fill text-{{ $timeslot->status === 'open' ? 'success' : ($timeslot->status === 'closed' ? 'secondary' : 'danger') }}"></i>
                                            {{ ucfirst($timeslot->status) }}
                                        </div>
                                    </div>
                                    @if($timeslot->instructors->isNotEmpty())
                                        <div class="mt-12">
                                            @foreach($timeslot->instructors as $instructor)
                                                @php
                                                    $assignmentType = $instructor->pivot->assignment_type ?? 'admin_assigned';
                                                    $badgeClass = $assignmentType === 'admin_assigned' ? 'badge-primary' : 'badge-success';
                                                    $icon = $assignmentType === 'admin_assigned' ? '[A]' : '[S]';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}" title="{{ ucfirst(str_replace('_', ' ', $assignmentType)) }}">
                                                    {{ $icon }} {{ $instructor->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($timeslot->notes)
                                        <div class="timeslot-notes">
                                            <i class="bi bi-sticky"></i> {{ $timeslot->notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="timeslot-actions">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-primary btn-sm-custom mr-8 btn-view-edit" data-slot-id="{{ $timeslot->id }}" onclick="showSlotDetails({{ $timeslot->id }})">
                                            <i class="bi bi-eye"></i> View/Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-sm-custom" onclick="confirmDeleteSchedule({{ $timeslot->id }})">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                        <form id="deleteScheduleForm{{ $timeslot->id }}" method="POST" action="{{ route('schools.admin.schedules.delete', [$school, $timeslot->id]) }}" class="hidden-form">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
    </div>
    <!-- End List View -->

    <!-- Calendar View -->
    <div id="calendar-view" class="view-content">
        @php
            $currentMonth = request('month', \Carbon\Carbon::parse($startDate)->format('Y-m'));
            $calendar = \Carbon\Carbon::parse($currentMonth . '-01');
            $monthStart = $calendar->copy()->startOfMonth();
            $monthEnd = $calendar->copy()->endOfMonth();
            $calendarStart = $monthStart->copy()->startOfWeek();
            $calendarEnd = $monthEnd->copy()->endOfWeek();
            
            // Flatten all timeslots and group by date
            $allTimeslots = collect();
            foreach($timeslots as $date => $dateTimeslots) {
                $allTimeslots = $allTimeslots->merge($dateTimeslots);
            }
            $slotsByDate = $allTimeslots->groupBy(function($slot) {
                return \Carbon\Carbon::parse($slot->date)->format('Y-m-d');
            });
        @endphp

        <div class="calendar-header">
            <div class="calendar-nav">
                <button type="button" class="nav-btn" onclick="changeMonth(-1)">← Previous</button>
            </div>
            <div class="current-month" id="currentMonth">
                {{ $calendar->format('F Y') }}
            </div>
            <div class="calendar-nav">
                <button type="button" class="nav-btn" onclick="changeMonth(1)">Next →</button>
            </div>
        </div>

        <div class="calendar-grid" id="calendarGrid">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="calendar-day-header">{{ $day }}</div>
            @endforeach

            @php
                $currentDate = $calendarStart->copy();
            @endphp

            @while($currentDate <= $calendarEnd)
                @php
                    $dateStr = $currentDate->format('Y-m-d');
                    $daySlots = $slotsByDate->get($dateStr, collect());
                    
                    $isOtherMonth = $currentDate->month !== $calendar->month;
                    $isToday = $currentDate->isToday();
                    $hasSchedule = $daySlots->isNotEmpty();
                @endphp

                 <div class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $hasSchedule && !$isOtherMonth ? 'has-schedule clickable cursor-pointer' : '' }}"
                     data-date="{{ $dateStr }}"
                     @if($hasSchedule && !$isOtherMonth)
                     onclick="showDayModal('{{ $dateStr }}', '{{ $currentDate->format('l, F j, Y') }}')"
                     @endif>
                    <div class="day-number">{{ $currentDate->day }}</div>
                    
                    @if(!$isOtherMonth && $hasSchedule)
                        <div class="day-slots">
                            @foreach($daySlots->take(3) as $slot)
                                @php
                                    $adminCount = $slot->getAdminAssignedCount();
                                    $selfCount = $slot->getSelfSelectedCount();
                                    $availableSpots = $slot->getAvailableSpots();
                                    $totalAssigned = $adminCount + $selfCount;
                                    $hasAvailableSpots = $availableSpots > 0;
                                @endphp
                                <div class="slot-badge {{ $hasAvailableSpots ? 'open' : 'assigned' }}" 
                                     title="{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }} | {{ $totalAssigned }}/{{ $slot->max_instructors }} ({{ $adminCount }} admin, {{ $selfCount }} self)">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    ({{ $totalAssigned }}/{{ $slot->max_instructors }})
                                </div>
                            @endforeach
                            
                            @if($daySlots->count() > 3)
                                <div class="slot-badge slot-badge-more" title="Click day to see all {{ $daySlots->count() }} schedules">
                                    +{{ $daySlots->count() - 3 }} more
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @php
                    $currentDate->addDay();
                @endphp
            @endwhile
        </div>

        <div class="calendar-legend">
            <strong>Legend:</strong> 
            <span class="legend-indicator legend-indicator-open"></span> Has Available Spots
            <span class="legend-indicator legend-indicator-assigned"></span> Fully Assigned
            <br><small class="legend-help">Click on a time slot to view details</small>
        </div>
    </div>
    <!-- End Calendar View -->
</div>

<!-- Create Schedule Modal (Unified) -->
<div class="modal" id="createModal">
    <div class="modal-content">
        <div class="modal-header modal-success">
            <h5><i class="bi bi-calendar-plus"></i> Create Schedule</h5>
            <button type="button" class="btn-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('schools.admin.schedules.create', $school) }}">
            @csrf
            <div class="modal-body">
                <div class="info-panel">
                    <strong>How it works:</strong>
                    <button type="button" class="info-panel-toggle" onclick="toggleInfoPanel(this)">Show details</button>
                    <ul class="info-panel-list">
                        <li>Select the course for this schedule</li>
                        <li>Set the max capacity for this schedule</li>
                        <li>Optionally pre-assign instructors (they'll be marked as "Admin Assigned")</li>
                        <li>Remaining spots will be available for instructor self-selection</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-book"></i> Course <span class="text-danger">*</span>
                    </label>
                    @if(isset($courses) && $courses->isNotEmpty())
                        <select name="course_id" id="createCourseSelect" class="form-control" required>
                            <option value="">-- Select a Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-type="{{ $course->course_type }}">
                                    {{ $course->title }} ({{ strtoupper($course->course_type) }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <p class="text-muted text-center no-courses-msg">
                            <i class="bi bi-exclamation-triangle"></i> No active courses available. Please create a course first.
                        </p>
                    @endif
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                </div>
                
                <div class="row">
                    <div class="col-half">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-half">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group" id="maxStudentsGroup">
                    <label class="form-label" id="maxStudentsLabel">Student Capacity (Seats)</label>
                    <input type="number" name="max_students" id="createMaxStudents" class="form-control" min="1" value="30">
                    <small class="text-muted" id="maxStudentsHelp">Total number of students allowed to book this classroom session.</small>
                </div>

                <div class="form-group" id="maxInstructorsGroup">
                    <label class="form-label">Max Instructors (Capacity)</label>
                    <input type="number" name="max_instructors" id="createMaxInstructors" class="form-control" min="1" max="10" value="3" required>
                    <small class="text-muted">Maximum number of instructors allowed to join this slot.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-people"></i> Pre-Assign Instructors (Optional)
                        <small class="instructors-count">({{ $instructors->count() }} available)</small>
                    </label>
                    @if($instructors->isEmpty())
                        <p class="text-muted text-center no-instructors-msg">
                            <i class="bi bi-info-circle"></i> No active instructors available.
                        </p>
                    @else
                        <select name="instructor_ids[]" class="form-control" multiple size="6">
                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}">
                                    {{ $instructor->name }} ({{ $instructor->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple instructors. 
                            Leave unselected to make all spots available for self-selection. 
                            Selected instructors will be marked as "Admin Assigned" [A].
                        </small>
                    @endif
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="E.g., Saturday morning rush - need experienced instructors"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div id="batchModeAlert" style="display: none; width: 100%; text-align: left; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px; margin-bottom: 10px; font-size: 0.9rem; color: #856404;">
                    <i class="bi bi-info-circle-fill"></i> <strong>Batch Mode Active:</strong> Since you have selected multiple instructors for a PDC session, the system will create <strong>one separate slot for each instructor</strong>.
                </div>
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Create Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Schedule Details Modal -->
<div class="modal" id="detailsModal">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <h5><i class="bi bi-info-circle"></i> Schedule Details</h5>
            <button type="button" class="btn-close" onclick="closeDetailsModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailsModalContent">
            <!-- Content will be populated by JavaScript -->
            <div class="loading-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDetailsModal()">Close</button>
            <button type="button" class="btn btn-primary" onclick="openEditModal()">
                <i class="bi bi-pencil"></i> Edit Schedule
            </button>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header modal-primary">
            <h5><i class="bi bi-pencil-square"></i> Edit Schedule</h5>
            <button type="button" class="btn-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" id="editScheduleForm">
            @csrf
            @method('PUT')
            <div class="modal-body" id="editModalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Day Schedule Modal (shows all schedules for clicked day) -->
<div class="modal" id="dayModal">
    <div class="modal-content day-modal-content">
        <div class="modal-header modal-success">
            <div>
                <h5 class="day-modal-title"><i class="bi bi-calendar-day"></i> <span id="modalDayTitle">Schedule Details</span></h5>
                <small id="modalDayDate" class="day-modal-date"></small>
            </div>
            <button type="button" class="btn-close" onclick="closeDayModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalDayBody">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDayModal()">Close</button>
        </div>
    </div>
</div>

<script>
    var currentSlotId = null;
    var currentSlotData = null;
    
    // Define global functions first for reliable access
    window.openCreateModal = function() {
        var modal = document.getElementById('createModal');
        if (modal) {
            modal.style.visibility = 'visible';
            modal.style.display = 'flex';
        }
    };

    window.closeCreateModal = function() {
        var createModal = document.getElementById('createModal');
        if(createModal) createModal.style.display = 'none';
    };

    // Course-Driven Smart Form Logic
    document.addEventListener('DOMContentLoaded', function() {
        const courseSelect = document.getElementById('createCourseSelect');
        const maxStudentsGroup = document.getElementById('maxStudentsGroup');
        const maxInstructorsGroup = document.getElementById('maxInstructorsGroup');
        const batchModeAlert = document.getElementById('batchModeAlert');
        const instructorSelect = document.querySelector('select[name="instructor_ids[]"]');
        const maxInstructorsInput = document.getElementById('createMaxInstructors');

        function updateFormMode() {
            const selectedOption = courseSelect.options[courseSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const type = selectedOption.getAttribute('data-type');
            const selectedInstructors = Array.from(instructorSelect.selectedOptions).length;

            if (type === 'theoretical') {
                // TDC Mode: Focus on Seating
                maxStudentsGroup.style.display = 'block';
                maxInstructorsGroup.style.display = 'block';
                batchModeAlert.style.display = 'none';
                maxInstructorsInput.disabled = false;
            } else {
                // PDC Mode: Focus on 1-on-1
                maxStudentsGroup.style.display = 'none';
                
                if (selectedInstructors > 1) {
                    // Batch Multi-Assign Mode active
                    batchModeAlert.style.display = 'block';
                    maxInstructorsGroup.style.display = 'none';
                } else {
                    batchModeAlert.style.display = 'none';
                    maxInstructorsGroup.style.display = 'block';
                    maxInstructorsInput.value = 1;
                    maxInstructorsInput.disabled = true;
                }
            }
        }

        if (courseSelect) courseSelect.addEventListener('change', updateFormMode);
        if (instructorSelect) instructorSelect.addEventListener('change', updateFormMode);
    });

    window.openDetailsModal = function() {
        var detailsModal = document.getElementById('detailsModal');
        if(detailsModal) {
            detailsModal.style.visibility = 'visible';
            detailsModal.style.display = 'flex';
        }
    };

    window.closeDetailsModal = function() {
        var detailsModal = document.getElementById('detailsModal');
        if(detailsModal) detailsModal.style.display = 'none';
    };

    window.openEditModal = function() {
        window.closeDetailsModal();
        if (window.currentSlotData) {
            populateEditModal(window.currentSlotData);
            var editModal = document.getElementById('editModal');
            if(editModal) {
                editModal.style.visibility = 'visible';
                editModal.style.display = 'flex';
            }
        }
    };

    window.closeEditModal = function() {
        var editModal = document.getElementById('editModal');
        if(editModal) editModal.style.display = 'none';
    };

    window.switchView = function(view) {
        const listView = document.getElementById('list-view');
        const calendarView = document.getElementById('calendar-view');
        const viewBtns = document.querySelectorAll('.view-btn');

        if (!listView || !calendarView || viewBtns.length === 0) return;
        
        viewBtns.forEach(btn => btn.classList.remove('active'));
        
        if (view === 'list') {
            listView.classList.add('active');
            calendarView.classList.remove('active');
            const listBtn = document.querySelector('.view-btn[data-view-toggle="list"]');
            if (listBtn) listBtn.classList.add('active');
            localStorage.setItem('adminTimeslotsView', 'list');
        } else {
            listView.classList.remove('active');
            calendarView.classList.add('active');
            const calendarBtn = document.querySelector('.view-btn[data-view-toggle="calendar"]');
            if (calendarBtn) calendarBtn.classList.add('active');
            localStorage.setItem('adminTimeslotsView', 'calendar');
        }
    };

    window.initSchedules = function() {
        console.log('Initializing schedules...');
        const createBtn = document.getElementById('createScheduleBtn');
        if (createBtn) {
            // Remove existing listener if any to avoid double triggers
            const newBtn = createBtn.cloneNode(true);
            createBtn.parentNode.replaceChild(newBtn, createBtn);
            newBtn.addEventListener('click', function(event) {
                event.preventDefault();
                window.openCreateModal();
            });
        }

        document.querySelectorAll('.view-btn[data-view-toggle]').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const view = this.getAttribute('data-view-toggle');
                if (view) window.switchView(view);
            });
        });

        document.querySelectorAll('.btn-view-edit[data-slot-id]').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const slotId = this.getAttribute('data-slot-id');
                if (slotId) window.showSlotDetails(slotId);
            });
        });

        const savedView = localStorage.getItem('adminTimeslotsView');
        if (savedView === 'calendar') window.switchView('calendar');
    };

    console.log('Schedules script starting execution...');
    
    // Store all schedule data for calendar modal access
    var allSchedulesData = {
        @foreach($timeslots as $date => $dateTimeslots)
            @php
                $formattedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            @endphp
            {!! json_encode($formattedDate) !!}: [
                @foreach($dateTimeslots as $timeslot)
                    @php
                        $availableSpots = $timeslot->getAvailableSpots();
                    @endphp
                    {
                        id: {!! json_encode($timeslot->id) !!},
                        time: {!! json_encode(\Carbon\Carbon::parse($timeslot->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($timeslot->end_time)->format('h:i A')) !!},
                        instructors: [
                            @foreach($timeslot->instructors as $instructor)
                            { 
                                name: {!! json_encode($instructor->name) !!}, 
                                type: {!! json_encode($instructor->pivot->assignment_type ?? "admin_assigned") !!} 
                            },
                            @endforeach
                        ],
                        notes: {!! json_encode($timeslot->notes ?? "No notes") !!},
                        status: {!! json_encode($timeslot->status) !!},
                        available: {!! json_encode((string)$availableSpots) !!},
                        max: {!! json_encode($timeslot->max_instructors) !!}
                    },
                @endforeach
            ],
        @endforeach
    };

    console.log('Schedules data loaded. Count:', Object.keys(allSchedulesData).length || 0);

    // Initialize on load
    window.initSchedules();
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const createModal = document.getElementById('createModal');
        const detailsModal = document.getElementById('detailsModal');
        const editModal = document.getElementById('editModal');
        const dayModal = document.getElementById('dayModal');
        
        if (event.target == createModal) {
            window.closeCreateModal();
        } else if (event.target == detailsModal) {
            window.closeDetailsModal();
        } else if (event.target == editModal) {
            window.closeEditModal();
        } else if (event.target == dayModal) {
            window.closeDayModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') {
            return;
        }

        const createModal = document.getElementById('createModal');
        const detailsModal = document.getElementById('detailsModal');
        const editModal = document.getElementById('editModal');
        const dayModal = document.getElementById('dayModal');

        if (createModal && createModal.style.display === 'flex') {
            window.closeCreateModal();
            return;
        }

        if (editModal && editModal.style.display === 'flex') {
            window.closeEditModal();
            return;
        }

        if (detailsModal && detailsModal.style.display === 'flex') {
            window.closeDetailsModal();
            return;
        }

        if (dayModal && dayModal.style.display === 'flex') {
            window.closeDayModal();
        }
    });
    
    // Collapsible Date Sections
    window.toggleDate = function(dateHeader) {
        const cardBody = dateHeader.closest('.timeslot-card').querySelector('.card-body');
        cardBody.classList.toggle('collapsed');
        dateHeader.classList.toggle('collapsed');
    };

    window.toggleInfoPanel = function(button) {
        const panel = button.closest('.info-panel');
        if (!panel) return;

        const expanded = panel.classList.toggle('expanded');
        button.textContent = expanded ? 'Hide details' : 'Show details';
    }
    
    // Calendar Navigation - Preserves view state
    window.changeMonth = function(delta) {
        const currentMonthEl = document.getElementById('currentMonth');
        const monthText = currentMonthEl.textContent.trim();
        const currentDate = new Date(monthText + ' 1');
        
        currentDate.setMonth(currentDate.getMonth() + delta);
        
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const monthStart = `${year}-${month}-01`;

        const endDateObj = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const endMonth = String(endDateObj.getMonth() + 1).padStart(2, '0');
        const endDay = String(endDateObj.getDate()).padStart(2, '0');
        const monthEnd = `${endDateObj.getFullYear()}-${endMonth}-${endDay}`;
        
        const url = new URL(window.location);
        url.searchParams.set('month', `${year}-${month}`);
        url.searchParams.set('start_date', monthStart);
        url.searchParams.set('end_date', monthEnd);
        window.location.href = url.toString();
    }
    
    // Show Slot Details Modal
    window.showSlotDetails = function(slotId) {
        console.log('showSlotDetails called with ID:', slotId);
        currentSlotId = slotId;
        
        // Find the slot data from the page DOM using data attributes
        const slotItem = document.querySelector(`.timeslot-item[data-slot-id="${slotId}"]`);
        
        if (!slotItem) {
            console.error('Could not find slot item with ID:', slotId);
            if (typeof Toast !== 'undefined' && Toast.error) {
                Toast.error('Could not find schedule details. Please refresh the page and try again.', 'Not Found');
            } else if (typeof showToast !== 'undefined') {
                showToast('error', 'Could not find schedule details. Please refresh the page and try again.');
            }
            return;
        }
        
        console.log('Found slot item:', slotItem);
        
        // Extract data from data attributes and DOM
        const startTime = slotItem.dataset.startTime || '';
        const endTime = slotItem.dataset.endTime || '';
        const maxInstructors = slotItem.dataset.maxInstructors || '';
        const notes = slotItem.dataset.notes || 'No notes';
        
        // Format time
        const timeText = startTime && endTime ? 
            `${window.formatTime(startTime)} - ${window.formatTime(endTime)}` : 
            'Time not available';
        
        // Get instructors from the DOM
        const instructorBadges = slotItem.querySelectorAll('.badge-primary, .badge-success');
        let instructorsList = [];
        instructorBadges.forEach(badge => {
            const nameText = badge.textContent.trim().replace(/^\[A\]\s*|^\[S\]\s*/, ''); // Remove prefix
            instructorsList.push({
                name: nameText,
                type: badge.classList.contains('badge-primary') ? 'admin' : 'self'
            });
        });
        
        console.log('Instructors found:', instructorsList);
        
        // Get availability
        const availableBadge = slotItem.querySelector('.badge-success:not(.badge-primary), .badge-secondary');
        const availableText = availableBadge ? availableBadge.textContent.trim() : 'Full';
        
        // Store data for edit modal
        currentSlotData = {
            id: slotId,
            time: timeText,
            instructors: instructorsList,
            notes: notes,
            available: availableText,
            maxInstructors: maxInstructors
        };
            
            // Populate details modal
            let instructorsHtml = '';
            if (instructorsList.length > 0) {
                instructorsHtml = instructorsList.map(inst => {
                    const icon = inst.type === 'admin' ? '[A]' : '[S]';
                    const label = inst.type === 'admin' ? 'Admin Assigned' : 'Self Selected';
                    return `<span class="badge badge-${inst.type === 'admin' ? 'primary' : 'success'}" title="${label}">${icon} ${inst.name}</span>`;
                }).join(' ');
            } else {
                instructorsHtml = '<span class="text-muted">No instructors assigned yet</span>';
            }
            
            document.getElementById('detailsModalContent').innerHTML = `
                <div class="details-wrap">
                    <div class="details-section">
                        <strong class="details-label">Time:</strong>
                        <div class="details-time">${timeText}</div>
                    </div>
                    
                    <div class="details-section">
                        <strong class="details-label">Instructors:</strong>
                        <div>${instructorsHtml}</div>
                    </div>
                    
                    <div class="details-section">
                        <strong class="details-label">Availability:</strong>
                        <div>${availableText}</div>
                    </div>
                    
                    <div>
                        <strong class="details-label">Notes:</strong>
                        <div class="details-notes">
                            ${notes}
                        </div>
                    </div>
                </div>
            `;
            
            window.openDetailsModal();
        
        console.log('Modal opened successfully');
    }
    
    // Helper function to format time (HH:MM:SS to HH:MM AM/PM)
    window.formatTime = function(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }
    
    // Show all schedules for a specific day in modal
    window.showDayModal = function(dateStr, formattedDate) {
        console.log('Showing day modal for:', dateStr);
        console.log('All available dates in allSchedulesData:', Object.keys(allSchedulesData));
        console.log('Full allSchedulesData object:', allSchedulesData);
        
        document.getElementById('modalDayTitle').textContent = 'Schedules for ' + formattedDate.split(',')[0];
        document.getElementById('modalDayDate').textContent = formattedDate;
        
        // Get schedules for this date from the stored data
        const daySchedules = allSchedulesData[dateStr] || [];
        
        console.log('Looking for date:', dateStr);
        console.log('Found schedules:', daySchedules);
        
        if (daySchedules.length === 0) {
            document.getElementById('modalDayBody').innerHTML = `
                <div class="day-empty">
                    <i class="bi bi-calendar-x day-empty-icon"></i>
                    <p>No schedules found for this date.</p>
                </div>
            `;
        } else {
            let modalContent = '<div class="day-list">';
            
            daySchedules.forEach(schedule => {
                // Build instructors HTML
                let instructorsHtml = '';
                if (schedule.instructors.length > 0) {
                    schedule.instructors.forEach(instructor => {
                        const icon = instructor.type === 'admin_assigned' ? '[A]' : '[S]';
                        const badgeClass = instructor.type === 'admin_assigned' ? 'badge-primary' : 'badge-success';
                        const title = instructor.type === 'admin_assigned' ? 'Admin Assigned' : 'Self Selected';
                        instructorsHtml += `<span class="badge ${badgeClass}" title="${title}">${icon} ${instructor.name}</span> `;
                    });
                } else {
                    instructorsHtml = '<span class="text-muted">No instructors assigned</span>';
                }
                
                // Build availability badge
                let availableHtml = '';
                if (schedule.availableSpots > 0) {
                    availableHtml = `<span class="badge badge-success">${schedule.availableSpots} Spot${schedule.availableSpots > 1 ? 's' : ''} Available</span>`;
                } else {
                    availableHtml = '<span class="badge badge-secondary">✓ Full</span>';
                }
                
                modalContent += `
                    <div class="day-card">
                        <div class="day-card-head">
                            <div>
                                <div class="day-card-time">
                                    <i class="bi bi-clock"></i> ${window.formatTime(schedule.startTime)} - ${window.formatTime(schedule.endTime)}
                                </div>
                                ${availableHtml}
                                <div class="day-card-meta">
                                    <i class="bi bi-people-fill"></i> ${schedule.totalCount}/${schedule.maxInstructors} Instructors
                                    ${schedule.adminCount > 0 || schedule.selfCount > 0 ? 
                                        `<span class="day-counts">
                                            (${schedule.adminCount > 0 ? `<span class="count-admin">${schedule.adminCount} admin</span>` : ''}${schedule.adminCount > 0 && schedule.selfCount > 0 ? ', ' : ''}${schedule.selfCount > 0 ? `<span class="count-self">${schedule.selfCount} self</span>` : ''})
                                        </span>` 
                                        : ''}
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="window.closeDayModal(); window.showSlotDetails(${schedule.id})">
                                <i class="bi bi-eye"></i> View/Edit
                            </button>
                        </div>
                        <div class="day-instructors">
                            <strong class="day-instructors-label">Instructors:</strong><br>
                            ${instructorsHtml}
                        </div>
                        ${schedule.notes ? `
                        <div class="day-notes">
                            <i class="bi bi-sticky"></i> ${schedule.notes}
                        </div>
                        ` : ''}
                    </div>
                `;
            });
            
            modalContent += '</div>';
            document.getElementById('modalDayBody').innerHTML = modalContent;
        }
        
        var dayModal = document.getElementById('dayModal');
        if (dayModal) {
            dayModal.style.visibility = 'visible';
            dayModal.style.display = 'flex';
        }
    }
    
    window.openDayModal = function() {
        var dayModal = document.getElementById('dayModal');
        if(dayModal) {
            dayModal.style.visibility = 'visible';
            dayModal.style.display = 'flex';
        }
    };

    window.closeDayModal = function() {
        document.getElementById('dayModal').style.display = 'none';
    }
    
    // Populate Edit Modal
    window.populateEditModal = function(slotData) {
        const schoolSlug = '{{ $school->slug }}';
        const actionUrl = `/${schoolSlug}/admin/schedules/${slotData.id}`;
        document.getElementById('editScheduleForm').action = actionUrl;
        
        // Get available instructors from the create modal
        const createModalSelect = document.querySelector('#createModal select[name="instructor_ids[]"]');
        const allInstructors = createModalSelect ? Array.from(createModalSelect.options).map(opt => ({
            id: opt.value,
            name: opt.text
        })) : [];
        
        // Build instructor multi-select
        let instructorOptions = allInstructors.map(inst => {
            // Match by checking if instructor name is in the assigned list
            const instructorName = inst.name.split(' (')[0].trim(); // Remove email part
            const isSelected = slotData.instructors.some(assigned => {
                const assignedName = assigned.name.replace(/[🔵🟢]\s*/, '').trim();
                return assignedName === instructorName;
            });
            return `<option value="${inst.id}" ${isSelected ? 'selected' : ''}>${inst.name}</option>`;
        }).join('');
        
        document.getElementById('editModalContent').innerHTML = `
            <div class="form-group">
                <label class="form-label">
                    <i class="bi bi-people"></i> Assigned Instructors
                </label>
                <select name="instructor_ids[]" class="form-control multi-select-auto" multiple size="6">
                    ${instructorOptions}
                </select>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Hold Ctrl (Windows) or Cmd (Mac) to select multiple. 
                    Changes will update instructor assignments.
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">${slotData.notes !== 'No notes' ? slotData.notes : ''}</textarea>
            </div>
        `;
    }
    
    // Confirm Delete Schedule
    window.confirmDeleteSchedule = function(scheduleId) {
        showConfirm({
            type: 'danger',
            title: 'Delete Schedule',
            message: 'Are you sure you want to delete this schedule? This action cannot be undone.',
            confirmText: 'Yes, Delete',
            onConfirm: () => {
                document.getElementById('deleteScheduleForm' + scheduleId).submit();
            }
        });
    };
</script>

@endsection