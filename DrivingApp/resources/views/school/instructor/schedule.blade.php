@extends($isAjax ?? false ? 'layouts.ajax' : 'layouts.app')

@section('title', 'My Schedule')

@section('content')
@php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
    $instructorId = Auth::guard('instructor')->id();
@endphp

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .schedule-container {
        padding: 20px;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        margin: 20px auto;
        max-width: 1400px;
        width: 100%;
        box-sizing: border-box;
        overflow-x: hidden;
    }
    
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 3px solid #FFC107;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 400;
        color: #333;
        margin: 0;
    }

    .page-subtitle {
        color: #666;
        font-size: 1rem;
        margin-top: 8px;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .schedule-container {
            padding: 12px;
            margin: 10px auto;
            width: calc(100% - 20px);
        }

        .page-title {
            font-size: 1.2rem;
        }

        .page-subtitle {
            font-size: 0.8rem;
        }

        .page-header {
            margin-bottom: 15px;
            padding-bottom: 12px;
        }
    }

    @media (max-width: 480px) {
        .schedule-container {
            padding: 10px;
            margin: 8px auto;
            width: calc(100% - 16px);
        }

        .page-title {
            font-size: 1.1rem;
        }

        .page-subtitle {
            font-size: 0.75rem;
        }
    }

    .view-toggle {
        display: flex;
        gap: 0;
        margin-bottom: 30px;
        background: #6c757d;
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
    }

    @media (max-width: 768px) {
        .view-toggle {
            width: 100%;
            margin-bottom: 15px;
        }
    }

    .view-btn {
        padding: 12px 28px;
        background: #6c757d;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        transition: background 0.3s;
    }

    @media (max-width: 768px) {
        .view-btn {
            flex: 1;
            padding: 10px 16px;
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .view-btn {
            padding: 8px 12px;
            font-size: 13px;
        }
    }

    .view-btn.active {
        background: #5a6268;
    }

    .view-btn:hover {
        background: #5a6268;
    }

    .view-content {
        display: none;
    }

    .view-content.active {
        display: block;
    }

    /* Calendar View Styles */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    @media (max-width: 768px) {
        .calendar-header {
            flex-direction: row;
            gap: 10px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .calendar-nav {
            gap: 6px;
        }

        .nav-btn {
            padding: 6px 10px;
            font-size: 0.75rem;
        }

        .current-month {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .calendar-header {
            padding: 10px;
            border-radius: 6px;
            gap: 6px;
        }

        .nav-btn {
            padding: 5px 8px;
            font-size: 0.7rem;
        }

        .current-month {
            font-size: 0.9rem;
        }
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-top: 20px;
        transition: opacity 0.15s ease;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .calendar-grid {
            gap: 6px;
            margin-top: 12px;
        }
    }

    @media (max-width: 480px) {
        .calendar-grid {
            gap: 4px;
            margin-top: 10px;
        }
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

    @media (max-width: 768px) {
        .calendar-day-header {
            padding: 8px 4px;
            font-size: 0.7rem;
            border-radius: 4px;
        }
    }

    @media (max-width: 480px) {
        .calendar-day-header {
            padding: 6px 3px;
            font-size: 0.65rem;
        }
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
    }

    .calendar-day:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .calendar-day {
            min-height: 70px;
            padding: 5px;
            border-radius: 4px;
            border-width: 1px;
        }

        .calendar-day:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.1);
        }
    }

    @media (max-width: 480px) {
        .calendar-day {
            min-height: 55px;
            padding: 3px;
            border-width: 1px;
        }
    }

    .calendar-day.other-month {
        background: #f8f9fa;
        opacity: 0.5;
    }

    .calendar-day.today {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    }

    .calendar-day.has-schedule {
        border-color: #4CAF50;
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.05) 0%, rgba(76, 175, 80, 0.02) 100%);
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

    @media (max-width: 768px) {
        .day-number {
            font-size: 0.7rem;
            margin-bottom: 3px;
        }

        .day-slots {
            font-size: 0.55rem;
        }

        .slot-badge {
            font-size: 0.5rem;
            padding: 1px 2px;
            margin: 1px 0;
        }
    }

    @media (max-width: 480px) {
        .day-number {
            font-size: 0.6rem;
            margin-bottom: 2px;
        }

        .day-slots {
            font-size: 0.5rem;
        }

        .slot-badge {
            font-size: 0.45rem;
            padding: 1px 2px;
            margin: 1px 0;
        }
    }

    .slot-badge.assigned {
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
    }

    .slot-badge.available {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
    }

    /* List View Styles */
    .week-section {
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .week-section {
            margin-bottom: 20px;
            border-width: 1px;
            border-radius: 8px;
        }
    }

    @media (max-width: 480px) {
        .week-section {
            margin-bottom: 15px;
            border-radius: 6px;
        }
    }

    .week-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .day-section {
        border-bottom: 1px solid #dee2e6;
        padding: 0;
        transition: all 0.3s ease;
    }

    .day-section:last-child {
        border-bottom: none;
    }

    .day-section:hover {
        background: transparent;
    }

    @media (max-width: 768px) {
        .day-section {
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        .day-section {
            padding: 12px 10px;
        }
    }

    .day-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 0;
        padding: 15px 20px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .day-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .day-badge.today {
        background: #FFC107;
        color: #000;
    }

    @media (max-width: 768px) {
        .day-title {
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        .day-badge {
            padding: 3px 10px;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .day-title {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .day-badge {
            padding: 2px 8px;
            font-size: 0.7rem;
        }
    }

    .schedule-card {
        margin: 0;
        padding: 20px;
        border-left: 4px solid transparent;
        border-radius: 0;
        background: white;
        box-shadow: none;
        transition: all 0.3s ease;
        border-bottom: none;
    }

    .schedule-card:hover {
        box-shadow: none;
        transform: none;
        background: #f8f9fa;
    }

    @media (max-width: 768px) {
        .schedule-card {
            padding: 12px;
            margin: 8px 0;
        }

        .schedule-card:hover {
            transform: translateX(2px);
        }
    }

    @media (max-width: 480px) {
        .schedule-card {
            padding: 10px;
            margin: 6px 0;
            border-left-width: 3px;
        }

        .schedule-card:hover {
            transform: translateX(1px);
        }
    }

    .schedule-card.my-schedule {
        border-left-color: #28a745;
        background: white;
    }

    .schedule-card.available {
        border-left-color: #28a745;
        background: white;
    }

    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .schedule-time {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .schedule-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .schedule-time {
            font-size: 0.9rem;
            gap: 5px;
        }

        .schedule-status {
            padding: 4px 9px;
            font-size: 0.7rem;
        }
    }

    @media (max-width: 480px) {
        .schedule-header {
            gap: 6px;
        }

        .schedule-time {
            font-size: 0.85rem;
        }

        .schedule-status {
            padding: 3px 8px;
            font-size: 0.65rem;
        }
    }

    .schedule-status.assigned {
        background: #d4edda;
        color: #155724;
    }

    .schedule-status.available {
        background: #d4edda;
        color: #155724;
    }

    .schedule-status.admin {
        background: #FFC107;
        color: #000;
    }

    .schedule-details {
        font-size: 0.95rem;
        color: #666;
        margin-top: 8px;
    }

    .schedule-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 6px 0;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .schedule-details {
            font-size: 0.9rem;
        }

        .schedule-detail-item {
            gap: 6px;
            margin: 5px 0;
        }
    }

    @media (max-width: 480px) {
        .schedule-details {
            font-size: 0.85rem;
        }

        .schedule-detail-item {
            gap: 5px;
            margin: 4px 0;
        }
    }

    .schedule-action {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e9ecef;
    }

    @media (max-width: 768px) {
        .schedule-action {
            margin-top: 10px;
            padding-top: 10px;
        }
    }

    @media (max-width: 480px) {
        .schedule-action {
            margin-top: 8px;
            padding-top: 8px;
        }
    }

    .btn {
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        width: 100%;
        max-width: 200px;
    }

    @media (max-width: 768px) {
        .btn {
            padding: 10px 16px;
            font-size: 0.9rem;
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .btn {
            padding: 8px 12px;
            font-size: 0.85rem;
        }
    }

    .btn-select {
        background: #FFC107;
        color: #000;
    }

    .btn-select:hover {
        background: #FFB300;
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 480px) {
        .btn-select:hover {
            transform: none;
        }
    }

    .btn-leave {
        background: #FFC107;
        color: #000;
    }

    .btn-leave:hover {
        background: #FFB300;
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 480px) {
        .btn-leave:hover {
            transform: none;
        }
    }

    .btn-request {
        background: #FFC107;
        color: #000;
    }

    .btn-request:hover {
        background: #FFB300;
        transform: none;
        box-shadow: none;
    }

    .btn-disabled {
        background: #e0e0e0;
        color: #999;
        cursor: not-allowed;
    }

    .schedule-status.pending {
        background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
    }

    @media (max-width: 768px) {
        .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
            max-width: 180px;
        }

        .btn-select:hover,
        .btn-leave:hover {
            transform: none;
        }
    }

    @media (max-width: 480px) {
        .btn {
            padding: 8px 12px;
            font-size: 0.85rem;
            max-width: 100%;
        }
    }

    .no-schedule {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .no-schedule h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #666;
    }

    @media (max-width: 768px) {
        .no-schedule {
            padding: 40px 15px;
        }

        .no-schedule h3 {
            font-size: 1.3rem;
        }
    }

    @media (max-width: 480px) {
        .no-schedule {
            padding: 30px 10px;
        }

        .no-schedule h3 {
            font-size: 1.1rem;
        }
    }

    .success-alert {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .success-alert {
            padding: 12px 15px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
    }

    @media (max-width: 480px) {
        .success-alert {
            padding: 10px 12px;
            font-size: 0.85rem;
            margin-bottom: 12px;
            border-left-width: 3px;
        }
    }

    .error-alert {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .error-alert {
            padding: 12px 15px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
    }

    @media (max-width: 480px) {
        .error-alert {
            padding: 10px 12px;
            font-size: 0.85rem;
            margin-bottom: 12px;
            border-left-width: 3px;
        }
    }

    .legend {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .legend {
            gap: 12px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .legend-item {
            font-size: 0.85rem;
            gap: 6px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
        }
    }

    @media (max-width: 480px) {
        .legend {
            gap: 10px;
            padding: 10px;
        }

        .legend-item {
            font-size: 0.8rem;
            gap: 5px;
        }

        .legend-color {
            width: 14px;
            height: 14px;
        }
    }

        .legend-color {
            width: 16px;
            height: 16px;
        }
    }

    @media (max-width: 480px) {
        .legend {
            gap: 10px;
            padding: 10px;
            flex-direction: column;
        }

        .legend-item {
            font-size: 0.8rem;
        }

        .legend-color {
            width: 14px;
            height: 14px;
        }
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        padding: 20px;
        border-radius: 8px;
        background: white;
        border: 1px solid #dee2e6;
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        border-color: #0d6efd;
        transform: none;
        box-shadow: none;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #0d6efd;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 15px 10px;
        }

        .stat-number {
            font-size: 1.6rem;
        }

        .stat-label {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .stats-row {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            padding: 12px 8px;
        }

        .stat-number {
            font-size: 1.4rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }
    }

    /* Collapsible Sections */
    .collapsible-header {
        cursor: pointer;
        user-select: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .collapsible-header:hover {
        opacity: 0.8;
    }

    .collapse-icon {
        margin-left: auto;
        font-size: 1.2rem;
        transition: transform 0.3s ease;
        display: inline-block;
    }

    .collapse-icon.collapsed {
        transform: rotate(-90deg);
    }

    .collapsible-content {
        max-height: 10000px;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.3s ease, margin 0.3s ease;
        opacity: 1;
    }

    .collapsible-content.collapsed {
        max-height: 0;
        opacity: 0;
        margin: 0;
    }

    .week-header {
        background: #0d6efd;
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .week-stats {
        font-size: 0.9rem;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .week-stat-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    /* Day Details Modal */
    .day-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        z-index: 9999;
        overflow-y: auto;
        padding: 20px;
    }

    .day-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .day-modal-content {
        background: white;
        border-radius: 16px;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .day-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .day-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
    }

    .day-modal-date {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-top: 5px;
    }

    .modal-close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        padding: 0;
        flex-shrink: 0;
    }

    .modal-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .day-modal-body {
        padding: 30px;
    }

    .modal-schedule-card {
        margin-bottom: 15px;
        padding: 16px;
        border-left: 4px solid;
        border-radius: 8px;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .modal-schedule-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateX(4px);
    }

    .modal-schedule-card.my-schedule {
        border-left-color: #4CAF50;
        background: linear-gradient(to right, rgba(76, 175, 80, 0.05), white);
    }

    .modal-schedule-card.available {
        border-left-color: #2196F3;
        background: linear-gradient(to right, rgba(33, 150, 243, 0.05), white);
    }

    .modal-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .modal-empty-state h4 {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 10px;
    }

    .calendar-day.clickable {
        cursor: pointer;
    }

    .calendar-day.clickable:hover {
        border-color: #667eea;
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.25);
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .day-modal {
            padding: 10px;
        }

        .day-modal-content {
            max-height: 95vh;
            border-radius: 12px;
        }

        .day-modal-header {
            padding: 20px;
            border-radius: 12px 12px 0 0;
        }

        .day-modal-title {
            font-size: 1.3rem;
        }

        .day-modal-date {
            font-size: 0.85rem;
        }

        .modal-close-btn {
            width: 35px;
            height: 35px;
            font-size: 1.3rem;
        }

        .day-modal-body {
            padding: 20px;
        }

        .modal-schedule-card {
            padding: 12px;
        }
    }

    @media (max-width: 480px) {
        .day-modal {
            padding: 5px;
        }

        .day-modal-content {
            border-radius: 10px;
        }

        .day-modal-header {
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }

        .day-modal-title {
            font-size: 1.1rem;
        }

        .day-modal-date {
            font-size: 0.8rem;
        }

        .modal-close-btn {
            width: 30px;
            height: 30px;
            font-size: 1.1rem;
        }

        .day-modal-body {
            padding: 15px;
        }

        .modal-empty-state {
            padding: 30px 15px;
        }
    }

    @media (max-width: 768px) {
        .week-header {
            padding: 12px 15px;
            font-size: 1rem;
        }

        .week-stats {
            gap: 10px;
            font-size: 0.85rem;
        }

        .week-stat-badge {
            padding: 3px 8px;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .week-header {
            padding: 10px 12px;
            font-size: 0.9rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .week-stats {
            gap: 8px;
            font-size: 0.8rem;
        }

        .week-stat-badge {
            padding: 2px 6px;
            font-size: 0.7rem;
        }
    }
</style>

<div class="schedule-container">
    <div class="page-header">
        <h1 class="page-title">📅 My Schedule</h1>
        <p class="page-subtitle">View your assigned time slots and select available ones</p>
    </div>

    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error-alert">{{ session('error') }}</div>
    @endif

    @php
        // Get all time slots (both assigned and available)
        $allTimeSlots = \App\Models\TimeSlot::with('instructors')
            ->where('school_id', $school->id)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        
        // Separate my slots and available slots
        $mySlots = $allTimeSlots->filter(function($slot) use ($instructorId) {
            return $slot->hasInstructor($instructorId);
        });
        
        $availableSlots = $allTimeSlots->filter(function($slot) {
            return $slot->status === 'open' && !$slot->isFull();
        });
        
        // Calculate stats
        $totalMySlots = $mySlots->count();
        $adminAssigned = $mySlots->filter(function($slot) use ($instructorId) {
            $instructor = $slot->instructors->firstWhere('id', $instructorId);
            return $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
        })->count();
        $selfSelected = $totalMySlots - $adminAssigned;
        $availableCount = $availableSlots->count();
    @endphp

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number">{{ $totalMySlots }}</div>
            <div class="stat-label">My Scheduled Slots</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $adminAssigned }}</div>
            <div class="stat-label">Admin Assigned</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $selfSelected }}</div>
            <div class="stat-label">Self Selected</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $availableCount }}</div>
            <div class="stat-label">Available to Select</div>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="view-toggle">
        <button class="view-btn active" onclick="switchView('list')">📋 List View</button>
        <button class="view-btn" onclick="switchView('calendar')">📅 Calendar View</button>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);"></div>
            <span>My Scheduled Slots</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);"></div>
            <span>Available Slots</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);"></div>
            <span>Admin Assigned</span>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="view-content active">
        @php
            $groupedSlots = $allTimeSlots->groupBy(function($slot) {
                return \Carbon\Carbon::parse($slot->date)->startOfWeek()->format('Y-m-d');
            });
        @endphp

        @if($allTimeSlots->isEmpty())
            <div class="no-schedule">
                <h3>No Schedule Available</h3>
                <p>There are currently no time slots available</p>
            </div>
        @else
            @foreach($groupedSlots as $weekStart => $weekSlots)
                @php
                    $weekEnd = \Carbon\Carbon::parse($weekStart)->endOfWeek();
                    $weekId = 'week-' . str_replace('-', '', $weekStart);
                    
                    // Calculate week stats
                    $weekMySlots = $weekSlots->filter(function($slot) use ($instructorId) {
                        return $slot->hasInstructor($instructorId);
                    })->count();
                    
                    $weekAvailableSlots = $weekSlots->filter(function($slot) {
                        return $slot->status === 'open' && !$slot->isFull();
                    })->count();
                @endphp
                <div class="week-section">
                    <div class="week-header collapsible-header" onclick="toggleWeek('{{ $weekId }}')">
                        <span style="font-size: 1.2rem;">📅</span>
                        <span>Week of {{ \Carbon\Carbon::parse($weekStart)->format('M j') }} - {{ $weekEnd->format('M j, Y') }}</span>
                        <div class="week-stats">
                            <span class="week-stat-badge">{{ $weekMySlots }} My Slots</span>
                            <span class="week-stat-badge">{{ $weekAvailableSlots }} Available</span>
                        </div>
                        <span class="collapse-icon" id="{{ $weekId }}-icon">▼</span>
                    </div>
                    
                    <div class="collapsible-content" id="{{ $weekId }}-content">
                    @foreach($weekSlots->groupBy('date') as $date => $daySlots)
                        <div class="day-section">
                            <div class="day-title">
                                {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                @if(\Carbon\Carbon::parse($date)->isToday())
                                    <span class="day-badge today">TODAY</span>
                                @endif
                            </div>

                            @foreach($daySlots as $slot)
                                @php
                                    $isMySlot = $slot->hasInstructor($instructorId);
                                    $isFull = $slot->isFull();
                                    $isAvailable = $slot->status === 'open';
                                    
                                    $assignmentType = null;
                                    $hasPendingRequest = false;
                                    $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
                                    $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
                                    $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
                                    
                                    if ($isMySlot) {
                                        $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                        $assignmentType = $instructor ? $instructor->pivot->assignment_type : null;
                                        $hasPendingRequest = $instructor && $instructor->pivot->has_pending_removal_request;
                                    }
                                @endphp

                                <div class="schedule-card {{ $isMySlot ? 'my-schedule' : 'available' }}">
                                    <div class="schedule-header">
                                        <div class="schedule-time">
                                            <span style="color: #6c757d;">🕐</span>
                                            <span>{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                            - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}</span>
                                        </div>
                                        @if($isMySlot)
                                            @if($hasPendingRequest)
                                                <span class="schedule-status pending">
                                                    REMOVAL REQUESTED
                                                </span>
                                            @else
                                                <span class="schedule-status {{ $assignmentType === 'admin_assigned' ? 'admin' : 'assigned' }}">
                                                {{ $assignmentType === 'admin_assigned' ? 'ADMIN ASSIGNED' : 'MY SLOT' }}
                                            @endif
                                        @else
                                            <span class="schedule-status available">
                                                AVAILABLE
                                            </span>
                                        @endif
                                    </div>

                                    <div class="schedule-details">
                                        <div class="schedule-detail-item">
                                            <span>Instructors:</span>
                                            <span>{{ $slot->instructors->count() }} / {{ $slot->max_instructors }}</span>
                                        </div>
                                        @if($slot->notes)
                                            <div class="schedule-detail-item">
                                                <span>Notes:</span>
                                                <span>{{ $slot->notes }}</span>
                                            </div>
                                        @endif
                                        @if($isMySlot && $assignmentType === 'admin_assigned' && !$hasPendingRequest)
                                            <div class="schedule-detail-item" style="color: #FF9800;">
                                                <span>Info:</span>
                                                <span>This slot was assigned to you by an administrator</span>
                                            </div>
                                            @if(!$canRequestRemoval)
                                                <div class="schedule-detail-item" style="color: #f44336;">
                                                    <span>Warning:</span>
                                                    <span>Must request removal at least {{ $minimumNoticeDays }} days in advance ({{ $daysUntilSlot }} day(s) remaining)</span>
                                                </div>
                                            @endif
                                        @endif
                                        @if($hasPendingRequest)
                                            <div class="schedule-detail-item" style="color: #FF6B6B;">
                                                <span>Status:</span>
                                                <span>Pending removal request - waiting for admin approval</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($isMySlot && $assignmentType === 'admin_assigned' && !$hasPendingRequest)
                                        <div class="schedule-action">
                                            <button type="button" class="btn btn-request" 
                                                    onclick="showRemovalRequestModal({{ $slot->id }}, {{ $canRequestRemoval ? 'true' : 'false' }}, {{ $minimumNoticeDays }}, {{ $daysUntilSlot }})"
                                                    @if(!$canRequestRemoval) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                                Request Removal
                                            </button>
                                        </div>
                                    @elseif(!$isMySlot || ($isMySlot && $assignmentType === 'self_selected'))
                                        <div class="schedule-action">
                                            <form method="POST" action="{{ $schoolRoute('instructor.timeslots.toggle', ['id' => $slot->id]) }}">
                                                @csrf
                                                @if($isMySlot)
                                                    <button type="submit" class="btn btn-leave">
                                                        Leave This Slot
                                                    </button>
                                                @elseif(!$isAvailable)
                                                    <button type="button" class="btn btn-disabled" disabled>
                                                        Slot Closed
                                                    </button>
                                                @elseif($isFull)
                                                    <button type="button" class="btn btn-disabled" disabled>
                                                        Slot Full
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-select">
                                                        Select This Slot
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Calendar View -->
    <div id="calendar-view" class="view-content">
        @php
            $currentMonth = request('month', now()->format('Y-m'));
            $calendar = \Carbon\Carbon::parse($currentMonth . '-01');
            $monthStart = $calendar->copy()->startOfMonth();
            $monthEnd = $calendar->copy()->endOfMonth();
            $calendarStart = $monthStart->copy()->startOfWeek();
            $calendarEnd = $monthEnd->copy()->endOfWeek();
            
            $slotsByDate = $allTimeSlots->groupBy(function($slot) {
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
                    
                    $myDaySlots = $daySlots->filter(function($slot) use ($instructorId) {
                        return $slot->hasInstructor($instructorId);
                    });
                    
                    $availableDaySlots = $daySlots->filter(function($slot) {
                        return $slot->status === 'open' && !$slot->isFull();
                    });
                    
                    $isOtherMonth = $currentDate->month !== $calendar->month;
                    $isToday = $currentDate->isToday();
                    $hasSchedule = $daySlots->isNotEmpty();
                @endphp

                <div class="calendar-day {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $hasSchedule && !$isOtherMonth ? 'has-schedule clickable' : '' }}"
                     data-date="{{ $dateStr }}"
                     @if($hasSchedule && !$isOtherMonth)
                     onclick="showDayModal('{{ $dateStr }}', '{{ $currentDate->format('l, F j, Y') }}')"
                     @endif>
                    <div class="day-number">{{ $currentDate->day }}</div>
                    
                    @if(!$isOtherMonth && $hasSchedule)
                        <div class="day-slots">
                            @foreach($myDaySlots as $slot)
                                @php
                                    $instructor = $slot->instructors->firstWhere('id', $instructorId);
                                    $isAdminAssigned = $instructor && $instructor->pivot->assignment_type === 'admin_assigned';
                                @endphp
                                <div class="slot-badge assigned" title="{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }} {{ $isAdminAssigned ? '(Admin)' : '' }}">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    {{ $isAdminAssigned ? '(A)' : '' }}
                                </div>
                            @endforeach
                            
                            @foreach($availableDaySlots->take(2) as $slot)
                                @if(!$slot->hasInstructor($instructorId))
                                    <div class="slot-badge available" title="Available: {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    </div>
                                @endif
                            @endforeach
                            
                            @if($availableDaySlots->count() > 2)
                                <div class="slot-badge available">
                                    +{{ $availableDaySlots->count() - 2 }} more
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

        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666;">
            💡 <strong>Tip:</strong> Click on dates in the calendar to see detailed information about available slots and manage your schedule.
        </div>
    </div>
</div>

<!-- Day Details Modal -->
<div class="day-modal" id="dayModal" onclick="if(event.target === this) closeDayModal()">
    <div class="day-modal-content" onclick="event.stopPropagation()">
        <div class="day-modal-header">
            <div>
                <h2 class="day-modal-title" id="modalDayTitle">📅 Schedule Details</h2>
                <div class="day-modal-date" id="modalDayDate"></div>
            </div>
            <button class="modal-close-btn" onclick="closeDayModal()" aria-label="Close">×</button>
        </div>
        <div class="day-modal-body" id="modalDayBody">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Removal Request Modal -->
<div class="day-modal" id="removalRequestModal" onclick="if(event.target === this) closeRemovalRequestModal()">
    <div class="day-modal-content" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="day-modal-header">
            <h2 class="day-modal-title">Request Removal from Time Slot</h2>
            <button class="modal-close-btn" onclick="closeRemovalRequestModal()" aria-label="Close">×</button>
        </div>
        <div class="day-modal-body">
            <form id="removalRequestForm" method="POST" style="padding: 20px;">
                @csrf
                <div id="removalWarning"></div>
                <div style="margin-bottom: 20px;">
                    <p style="color: #666; margin-bottom: 15px;">
                        Please provide a reason for requesting removal from this admin-assigned time slot. Your request will be reviewed by an administrator.
                    </p>
                    <label for="removal_reason" style="display: block; font-weight: 600; margin-bottom: 8px;">
                        Reason for Removal Request: <span style="color: #f44336;">*</span>
                    </label>
                    <textarea 
                        id="removal_reason" 
                        name="reason" 
                        required 
                        maxlength="500"
                        style="width: 100%; min-height: 120px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 14px; resize: vertical;"
                        placeholder="E.g., I have a conflicting appointment, personal emergency, etc."
                    ></textarea>
                    <small style="color: #999; display: block; margin-top: 5px;">Maximum 500 characters</small>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeRemovalRequestModal()" class="btn" style="background: #e0e0e0; color: #666;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-request">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<div id="schedulesData" style="display: none;">
    @php
        $schedulesJson = [];
        $minimumNoticeDays = $school->instructor_removal_notice_days ?? 7;
        
        foreach($allTimeSlots as $slot) {
            $dateKey = $slot->date->format('Y-m-d');
            if (!isset($schedulesJson[$dateKey])) {
                $schedulesJson[$dateKey] = [];
            }
            
            $isMySlot = $slot->hasInstructor($instructorId);
            $assignmentType = null;
            $hasPendingRequest = false;
            $daysUntilSlot = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($slot->date)->startOfDay(), false);
            $canRequestRemoval = $daysUntilSlot >= $minimumNoticeDays;
            
            if ($isMySlot) {
                $instructor = $slot->instructors->firstWhere('id', $instructorId);
                $assignmentType = $instructor ? $instructor->pivot->assignment_type : null;
                $hasPendingRequest = $instructor && $instructor->pivot->has_pending_removal_request;
            }
            
            $schedulesJson[$dateKey][] = [
                'id' => $slot->id,
                'start_time' => $slot->start_time->format('g:i A'),
                'end_time' => $slot->end_time->format('g:i A'),
                'status' => $slot->status,
                'is_my_slot' => $isMySlot,
                'is_full' => $slot->isFull(),
                'assignment_type' => $assignmentType,
                'has_pending_request' => $hasPendingRequest,
                'can_request_removal' => $canRequestRemoval,
                'minimum_notice_days' => $minimumNoticeDays,
                'days_until_slot' => $daysUntilSlot,
                'instructors_count' => $slot->instructors->count(),
                'max_instructors' => $slot->max_instructors,
                'notes' => $slot->notes,
                'toggle_url' => route('schools.instructor.timeslots.toggle', ['school' => $school->slug, 'id' => $slot->id]),
                'request_removal_url' => route('schools.instructor.timeslots.requestRemoval', ['school' => $school->slug, 'id' => $slot->id])
            ];
        }
    @endphp
    <script>
        window.schedulesData = @json($schedulesJson);
    </script>
</div>

<script>
    // Current month tracking
    let currentMonthDate = new Date('{{ $calendar->format('Y-m-01') }}');
    const instructorId = {{ $instructorId }};
    const schoolSlug = '{{ $school->slug }}';

    function switchView(viewName) {
        // Remove active class from all view buttons and contents
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.view-content').forEach(content => content.classList.remove('active'));

        // Add active class to selected view
        if (viewName === 'list') {
            document.querySelectorAll('.view-btn')[0].classList.add('active');
            document.getElementById('list-view').classList.add('active');
        } else {
            document.querySelectorAll('.view-btn')[1].classList.add('active');
            document.getElementById('calendar-view').classList.add('active');
        }
    }

    function toggleWeek(weekId) {
        const content = document.getElementById(weekId + '-content');
        const icon = document.getElementById(weekId + '-icon');
        
        if (content.classList.contains('collapsed')) {
            content.classList.remove('collapsed');
            icon.classList.remove('collapsed');
        } else {
            content.classList.add('collapsed');
            icon.classList.add('collapsed');
        }
    }

    function changeMonth(direction) {
        const calendarGrid = document.getElementById('calendarGrid');
        const currentMonthEl = document.getElementById('currentMonth');
        const navButtons = document.querySelectorAll('.calendar-nav button');
        
        // Add minimal loading state
        calendarGrid.style.opacity = '0.6';
        calendarGrid.style.pointerEvents = 'none';
        navButtons.forEach(btn => btn.disabled = true);
        
        // Calculate new month
        currentMonthDate.setMonth(currentMonthDate.getMonth() + direction);
        const year = currentMonthDate.getFullYear();
        const month = String(currentMonthDate.getMonth() + 1).padStart(2, '0');
        const monthStr = `${year}-${month}`;
        
        // Fetch new calendar data
        fetch(`{{ route('schools.instructor.schedule', ['school' => $school->slug]) }}?month=${monthStr}&ajax=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response to extract calendar data
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update month title
            const newMonthTitle = doc.querySelector('#currentMonth');
            if (newMonthTitle) {
                currentMonthEl.textContent = newMonthTitle.textContent;
            } else {
                // Fallback: format month name from date
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                   'July', 'August', 'September', 'October', 'November', 'December'];
                currentMonthEl.textContent = `${monthNames[currentMonthDate.getMonth()]} ${year}`;
            }
            
            // Update calendar grid
            const newGrid = doc.querySelector('#calendarGrid');
            if (newGrid) {
                calendarGrid.innerHTML = newGrid.innerHTML;
            }
            
            // Update schedules data
            const newSchedulesScript = doc.querySelector('#schedulesData script');
            if (newSchedulesScript) {
                eval(newSchedulesScript.textContent);
            }
            
            // Remove loading state
            calendarGrid.style.opacity = '1';
            calendarGrid.style.pointerEvents = 'auto';
            navButtons.forEach(btn => btn.disabled = false);
        })
        .catch(error => {
            console.error('Error loading calendar:', error);
            alert('Failed to load calendar. Please try again.');
            
            // Remove loading state even on error
            calendarGrid.style.opacity = '1';
            calendarGrid.style.pointerEvents = 'auto';
            navButtons.forEach(btn => btn.disabled = false);
        });
    }

    function showDayModal(dateStr, formattedDate) {
        const modal = document.getElementById('dayModal');
        const modalTitle = document.getElementById('modalDayTitle');
        const modalDate = document.getElementById('modalDayDate');
        const modalBody = document.getElementById('modalDayBody');
        
        modalDate.textContent = formattedDate;
        
        const daySchedules = window.schedulesData[dateStr] || [];
        
        if (daySchedules.length === 0) {
            modalBody.innerHTML = `
                <div class="modal-empty-state">
                    <h4>No Schedules</h4>
                    <p>There are no time slots scheduled for this date.</p>
                </div>
            `;
        } else {
            let html = '';
            const mySlots = daySchedules.filter(s => s.is_my_slot);
            const availableSlots = daySchedules.filter(s => !s.is_my_slot && s.status === 'open' && !s.is_full);
            
            if (mySlots.length > 0) {
                html += '<h3 style="margin-bottom: 15px; color: #4CAF50; font-size: 1.1rem;">My Scheduled Slots</h3>';
                mySlots.forEach(slot => {
                    html += generateScheduleCard(slot, true);
                });
            }
            
            if (availableSlots.length > 0) {
                html += `<h3 style="margin-top: ${mySlots.length > 0 ? '25px' : '0'}; margin-bottom: 15px; color: #2196F3; font-size: 1.1rem;">Available Slots</h3>`;
                availableSlots.forEach(slot => {
                    html += generateScheduleCard(slot, false);
                });
            }
            
            modalBody.innerHTML = html;
        }
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeDayModal() {
        const modal = document.getElementById('dayModal');
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }

    function showRemovalRequestModal(slotId, canRequest, minimumDays, daysRemaining) {
        const modal = document.getElementById('removalRequestModal');
        const form = document.getElementById('removalRequestForm');
        const textarea = document.getElementById('removal_reason');
        const submitBtn = modal.querySelector('button[type="submit"]');
        const warningDiv = document.getElementById('removalWarning');
        
        // Set form action with the correct slot ID
        form.action = `{{ route('schools.instructor.timeslots.requestRemoval', ['school' => $school->slug, 'id' => '__SLOT_ID__']) }}`.replace('__SLOT_ID__', slotId);
        
        // Clear previous input
        textarea.value = '';
        
        // Show warning if cannot request
        if (!canRequest) {
            warningDiv.innerHTML = `
                <div style="background: #ffebee; border-left: 4px solid #f44336; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <strong style="color: #c62828;">Cannot Submit Request</strong><br>
                    <span style="color: #666;">You must request removal at least ${minimumDays} days in advance. This slot is in ${daysRemaining} day(s).</span>
                </div>
            `;
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            warningDiv.innerHTML = `
                <div style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <strong style="color: #1976D2;">Minimum Notice Period</strong><br>
                    <span style="color: #666;">Requests must be submitted at least ${minimumDays} days before the scheduled time slot.</span>
                </div>
            `;
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus on textarea if enabled
        if (canRequest) {
            setTimeout(() => textarea.focus(), 100);
        }
    }

    function closeRemovalRequestModal() {
        const modal = document.getElementById('removalRequestModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function generateScheduleCard(slot, isMySlot) {
        const cardClass = isMySlot ? 'my-schedule' : 'available';
        
        let statusBadge, statusColor;
        if (isMySlot && slot.has_pending_request) {
            statusBadge = 'REMOVAL REQUESTED';
            statusColor = 'schedule-status pending';
        } else if (isMySlot) {
            statusBadge = slot.assignment_type === 'admin_assigned' ? 'ADMIN ASSIGNED' : 'MY SLOT';
            statusColor = slot.assignment_type === 'admin_assigned' ? 'schedule-status admin' : 'schedule-status assigned';
        } else {
            statusBadge = 'AVAILABLE';
            statusColor = 'schedule-status available';
        }
        
        let actionButton = '';
        let extraInfo = '';
        
        if (isMySlot && slot.has_pending_request) {
            extraInfo = `
                <div class="schedule-detail-item" style="color: #FF6B6B;">
                    <span>Status:</span>
                    <span>Pending removal request - waiting for admin approval</span>
                </div>
            `;
        } else if (isMySlot && slot.assignment_type === 'admin_assigned') {
            extraInfo = `
                <div class="schedule-detail-item" style="color: #FF9800;">
                    <span>Info:</span>
                    <span>This slot was assigned to you by an administrator</span>
                </div>
            `;
            if (!slot.can_request_removal) {
                extraInfo += `
                    <div class="schedule-detail-item" style="color: #f44336;">
                        <span>Warning:</span>
                        <span>Must request removal at least ${slot.minimum_notice_days} days in advance (${slot.days_until_slot} day(s) remaining)</span>
                    </div>
                `;
            }
            actionButton = `
                <div class="schedule-action">
                    <button type="button" class="btn btn-request" 
                            onclick="showRemovalRequestModal(${slot.id}, ${slot.can_request_removal}, ${slot.minimum_notice_days}, ${slot.days_until_slot}); closeDayModal();"
                            ${!slot.can_request_removal ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                        Request Removal
                    </button>
                </div>
            `;
        } else if (isMySlot && slot.assignment_type === 'self_selected') {
            actionButton = `
                <div class="schedule-action">
                    <form method="POST" action="${slot.toggle_url}" style="margin: 0;">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                        <button type="submit" class="btn btn-leave">Leave This Slot</button>
                    </form>
                </div>
            `;
        } else if (!isMySlot) {
            if (slot.status !== 'open') {
                actionButton = `
                    <div class="schedule-action">
                        <button type="button" class="btn btn-disabled" disabled>Slot Closed</button>
                    </div>
                `;
            } else if (slot.is_full) {
                actionButton = `
                    <div class="schedule-action">
                        <button type="button" class="btn btn-disabled" disabled>Slot Full</button>
                    </div>
                `;
            } else {
                actionButton = `
                    <div class="schedule-action">
                        <form method="POST" action="${slot.toggle_url}" style="margin: 0;">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                            <button type="submit" class="btn btn-select">Select This Slot</button>
                        </form>
                    </div>
                `;
            }
        }
        
        return `
            <div class="modal-schedule-card ${cardClass}">
                <div class="schedule-header">
                    <div class="schedule-time">
                        ${slot.start_time} - ${slot.end_time}
                    </div>
                    <span class="${statusColor}">
                        ${statusBadge}
                    </span>
                </div>
                <div class="schedule-details">
                    <div class="schedule-detail-item">
                        <span>Instructors:</span>
                        <span>${slot.instructors_count} / ${slot.max_instructors}</span>
                    </div>
                    ${slot.notes ? `
                        <div class="schedule-detail-item">
                            <span>Notes:</span>
                            <span>${slot.notes}</span>
                        </div>
                    ` : ''}
                    ${extraInfo}
                </div>
                ${actionButton}
            </div>
        `;
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDayModal();
            closeRemovalRequestModal();
        }
    });

    // Auto-submit month navigation forms
    document.addEventListener('DOMContentLoaded', function() {
        const monthInputs = document.querySelectorAll('input[name="month"]');
        monthInputs.forEach(input => {
            input.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Initialize calendar grid with current data
        renderCalendar();
    });
    
    // Render calendar function
    function renderCalendar() {
        const calendarGrid = document.getElementById('calendarGrid');
        if (!calendarGrid) return;
        
        // Calendar is already rendered by PHP on page load
        // This function is for future AJAX updates
    }
</script>

@endsection
