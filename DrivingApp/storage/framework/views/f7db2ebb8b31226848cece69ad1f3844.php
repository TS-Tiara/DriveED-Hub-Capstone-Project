<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e($schoolName ?? 'Driving School'); ?></title>
    
    <?php
        // Get school and its color settings
        $currentSchool = $school ?? $currentSchool ?? null;
        $settings = $currentSchool?->schoolSetting;
        
        // Default colors (blue to yellow gradient)
        $primaryColor = $settings->primary_color ?? '#2563eb';
        $secondaryColor = $settings->secondary_color ?? '#fbbf24';
        $accentColor = $settings->accent_color ?? '#1e40af';
        $sidebarBg = $settings->sidebar_bg_color ?? '#ffffff';
        $sidebarText = $settings->sidebar_text_color ?? '#333333';
        $sidebarHover = $settings->sidebar_hover_color ?? '#f5f5f5';
        $useGradient = $settings->use_gradient_header ?? true;
        
        // Background settings
        $backgroundType = $settings->background_type ?? 'color';
        $backgroundColor = $settings->background_color ?? '#f5f5f5';
        $backgroundImage = $settings->background_image ?? null;
        $backgroundOpacity = $settings->background_opacity ?? 100;
        
        // Calculate RGB values for transparency effects
        $primaryRgb = sscanf($primaryColor, "#%02x%02x%02x");
        $accentRgb = sscanf($accentColor, "#%02x%02x%02x");
        $backgroundRgb = sscanf($backgroundColor, "#%02x%02x%02x");
        
        // Generate header background
        $headerBg = $useGradient 
            ? "linear-gradient(135deg, {$primaryColor} 0%, {$secondaryColor} 100%)"
            : $primaryColor;
            
        // Generate body background
        if ($backgroundType === 'image' && $backgroundImage) {
            $bodyBg = "url('" . asset('storage/' . $backgroundImage) . "')";
            $bodyBgSize = "cover";
            $bodyBgPosition = "center";
            $bodyBgAttachment = "fixed";
        } else {
            $bodyBg = "rgba(" . implode(', ', $backgroundRgb) . ", " . ($backgroundOpacity / 100) . ")";
            $bodyBgSize = "auto";
            $bodyBgPosition = "initial";
            $bodyBgAttachment = "scroll";
        }
    ?>
    
    <style>
        :root {
            /* Brand Colors */
            --primary-color: <?php echo e($primaryColor); ?>;
            --secondary-color: <?php echo e($secondaryColor); ?>;
            --accent-color: <?php echo e($accentColor); ?>;
            --primary-rgb: <?php echo e(implode(', ', $primaryRgb)); ?>;
            --accent-rgb: <?php echo e(implode(', ', $accentRgb)); ?>;
            
            /* Sidebar */
            --sidebar-bg: <?php echo e($sidebarBg); ?>;
            --sidebar-text: <?php echo e($sidebarText); ?>;
            --sidebar-hover: <?php echo e($sidebarHover); ?>;
            
            /* Header */
            --header-gradient: <?php echo e($headerBg); ?>;
            --page-header-border: <?php echo e($settings->page_header_border ?? '#667eea'); ?>;
            
            /* Buttons */
            --btn-primary-bg: <?php echo e($settings->button_primary_bg ?? '#667eea'); ?>;
            --btn-primary-text: <?php echo e($settings->button_primary_text ?? '#ffffff'); ?>;
            --btn-secondary-bg: <?php echo e($settings->button_secondary_bg ?? '#6c757d'); ?>;
            --btn-secondary-text: <?php echo e($settings->button_secondary_text ?? '#ffffff'); ?>;
            --btn-success-bg: <?php echo e($settings->button_success_bg ?? '#28a745'); ?>;
            --btn-success-text: <?php echo e($settings->button_success_text ?? '#ffffff'); ?>;
            --btn-danger-bg: <?php echo e($settings->button_danger_bg ?? '#dc3545'); ?>;
            --btn-danger-text: <?php echo e($settings->button_danger_text ?? '#ffffff'); ?>;
            
            /* Borders & Shapes */
            --border-radius: <?php echo e($settings->border_radius ?? 8); ?>px;
            --button-border-radius: <?php echo e($settings->button_border_radius ?? 8); ?>px;
            
            /* Modals */
            --modal-header-bg: <?php echo e($settings->modal_header_bg ?? '#667eea'); ?>;
            --modal-header-text: <?php echo e($settings->modal_header_text ?? '#ffffff'); ?>;
            --modal-border-color: <?php echo e($settings->modal_border_color ?? '#667eea'); ?>;
            
            /* Cards */
            --card-border-color: <?php echo e($settings->card_border_color ?? '#e5e7eb'); ?>;
            --card-header-bg: <?php echo e($settings->card_header_bg ?? '#f9fafb'); ?>;
            
            /* Badges */
            --badge-pending-bg: <?php echo e($settings->badge_pending_bg ?? '#fbbf24'); ?>;
            --badge-pending-text: <?php echo e($settings->badge_pending_text ?? '#78350f'); ?>;
            --badge-approved-bg: <?php echo e($settings->badge_approved_bg ?? '#10b981'); ?>;
            --badge-approved-text: <?php echo e($settings->badge_approved_text ?? '#065f46'); ?>;
            --badge-cancelled-bg: <?php echo e($settings->badge_cancelled_bg ?? '#ef4444'); ?>;
            --badge-cancelled-text: <?php echo e($settings->badge_cancelled_text ?? '#7f1d1d'); ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }
        
        <?php if($backgroundType === 'image' && $backgroundImage): ?>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: <?php echo e($bodyBg); ?>;
            background-size: <?php echo e($bodyBgSize); ?>;
            background-position: <?php echo e($bodyBgPosition); ?>;
            background-attachment: <?php echo e($bodyBgAttachment); ?>;
            background-repeat: no-repeat;
            opacity: <?php echo e($backgroundOpacity / 100); ?>;
            z-index: -1;
        }
        <?php else: ?>
        body {
            background: <?php echo e($bodyBg); ?>;
        }
        <?php endif; ?>
        
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
            width: 100%;
        }
        
        /* Topbar Styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: 60px;
            background: <?php echo e($headerBg); ?>;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 1000;
            box-shadow: 0 3px 20px rgba(0,0,0,0.15);
            box-sizing: border-box;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .topbar-logo {
            font-size: 1.6rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .burger-menu {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .burger-menu:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .notification-icon, .profile-dropdown {
            position: relative;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .notification-icon:hover, .profile-dropdown:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        /* Profile Dropdown Menu Styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .profile-picture {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.2);
        }
        
        .profile-picture-default {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            font-weight: 600;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 180px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            z-index: 10001;
            display: none;
            border: 1px solid #e1e5e9;
            margin-top: 5px;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 12px 16px;
            color: #333;
            text-decoration: none;
            border: none;
            background: none;
            text-align: left;
            font-size: 14px;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        /* Override nav-item styles when used inside dropdown */
        .dropdown-menu .nav-item {
            padding: 12px 16px !important;
            margin: 0 !important;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .dropdown-menu .nav-item:hover {
            padding-left: 16px !important;
            transform: none !important;
            background: #f8f9fa !important;
            color: #333 !important;
        }
        
        .dropdown-menu .nav-item::before {
            display: none !important;
        }
        
        .dropdown-item:first-child {
            border-radius: 8px 8px 0 0;
        }
        
        .dropdown-item:last-child {
            border-radius: 0 0 8px 8px;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #e1e5e9;
            margin: 4px 0;
        }
        
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 60px;
            right: -300px;
            width: 300px;
            height: calc(100vh - 60px);
            background: var(--sidebar-bg);
            transition: right 0.3s ease;
            z-index: 999;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            box-sizing: border-box;
        }
        
        .sidebar.active {
            right: 0;
        }
        
        /* Prevent content shifting - removed duplicate style */
        
        /* Ensure body doesn't shift */
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
            width: 100%;
        }
        
        body {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        /* Modal z-index only - let pages handle their own modal styling */
        .modal {
            z-index: 1001; /* Higher than sidebar */
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .sidebar-header h3 {
            color: #333;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .sidebar-nav {
            padding: 0;
        }
        
        .nav-item {
            display: block;
            padding: 12px 20px;
            margin: 4px 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: <?php echo e($useGradient ? "linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%)" : "var(--primary-color)"); ?>;
            transform: scaleY(0);
            transition: transform 0.3s ease;
            z-index: -1;
        }
        
        .nav-item:hover {
            background: var(--sidebar-hover);
            padding-left: 25px;
            color: var(--primary-color);
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.15);
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }
        
        .nav-item.active {
            background: <?php echo e($headerBg); ?>;
            color: white !important;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
            transform: translateX(5px);
            position: relative;
            z-index: 1;
        }

        .nav-item.active::before {
            width: 4px;
            transform: scaleY(1);
            z-index: -1;
        }

        .nav-item:active {
            transform: translateX(3px) scale(0.98);
        }
        
        /* Main Content Styles */
        .main-content {
            margin-top: 60px;
            padding: 0;
            transition: none;
            min-height: calc(100vh - 60px);
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
            background: #f5f5f5;
        }
        
        /* Global Button Styles */
        .btn, button.btn, input[type="submit"].btn, input[type="button"].btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--button-border-radius);
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary, button.btn-primary, input[type="submit"].btn-primary {
            <?php if(($settings->button_style ?? 'solid') === 'gradient'): ?>
            background: linear-gradient(135deg, var(--btn-primary-bg) 0%, var(--btn-secondary-bg) 100%);
            <?php else: ?>
            background: var(--btn-primary-bg);
            <?php endif; ?>
            color: var(--btn-primary-text);
        }
        
        .btn-primary:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary, button.btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--btn-secondary-text);
        }
        
        .btn-secondary:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
        
        .btn-success, button.btn-success {
            background: var(--btn-success-bg);
            color: var(--btn-success-text);
        }
        
        .btn-success:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
        
        .btn-danger, button.btn-danger, input[type="submit"].btn-danger {
            background: var(--btn-danger-bg);
            color: var(--btn-danger-text);
        }
        
        .btn-danger:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }
        
        /* Global Badge Styles */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: calc(var(--border-radius) / 2);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-pending, .badge.pending {
            background: var(--badge-pending-bg);
            color: var(--badge-pending-text);
        }
        
        .badge-approved, .badge-confirmed, .badge.approved, .badge.confirmed {
            background: var(--badge-approved-bg);
            color: var(--badge-approved-text);
        }
        
        .badge-cancelled, .badge-rejected, .badge.cancelled, .badge.rejected {
            background: var(--badge-cancelled-bg);
            color: var(--badge-cancelled-text);
        }
        
        .badge-scheduled, .badge.scheduled {
            background: #3b82f6;
            color: #ffffff;
        }
        
        .badge-completed, .badge.completed {
            background: #10b981;
            color: #ffffff;
        }
        
        /* Global Card/Modal Styles */
        .card, .settings-card, .user-card, .booking-card, .course-card {
            border-radius: var(--border-radius);
            border: 1px solid var(--card-border-color);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: var(--card-header-bg);
            border-bottom: 1px solid var(--card-border-color);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }
        
        .page-header {
            border-bottom-color: var(--page-header-border) !important;
        }
        
        /* Modal Styles */
        .modal-header, #modal .modal-header {
            background: var(--modal-header-bg);
            color: var(--modal-header-text);
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }
        
        .modal-content, #modal .modal-content {
            border-radius: var(--border-radius);
            border: 2px solid var(--modal-border-color);
        }
        
        /* Only apply sidebar-open margin on desktop */
        @media (min-width: 1025px) {
            .main-content.sidebar-open {
                margin-right: 300px;
                transition: margin-right 0.3s ease;
            }
        }
        
        /* Universal fix: Remove top margin from ANY direct child of main-content */
        .main-content > * {
            margin-top: 0 !important;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Sidebar Overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 998;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Responsive Design */
        
        /* Large tablets and small laptops (768px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .topbar {
                padding: 0 20px;
            }
            
            .topbar-logo {
                font-size: 1.4rem;
            }
            
            .sidebar {
                width: 280px;
                right: -280px;
            }
            
            .main-content {
                padding: 18px;
            }
        }
        
        /* Tablets in portrait mode (600px - 768px) */
        @media (min-width: 601px) and (max-width: 768px) {
            .topbar {
                height: 50px;
                padding: 0 12px;
            }
            
            .topbar-logo {
                font-size: 1rem;
                gap: 6px;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .burger-menu {
                font-size: 1.3rem;
                padding: 7px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 7px;
                font-size: 1.1rem;
            }
            
            .topbar-left, .topbar-right {
                gap: 10px;
            }
            
            .sidebar {
                width: 280px;
                right: -280px;
                top: 55px;
                height: calc(100vh - 55px);
            }
            
            .sidebar-header {
                padding: 14px;
            }
            
            .sidebar-header h3 {
                font-size: 1rem;
            }
            
            .nav-item {
                padding: 10px 14px;
                margin: 3px 7px;
                font-size: 13px;
            }
            
            .main-content {
                margin-top: 55px;
                min-height: calc(100vh - 55px);
                padding: 16px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .dropdown-menu {
                min-width: 170px;
            }
            
            .dropdown-item {
                padding: 10px 13px;
                font-size: 13px;
            }
            
            .dropdown-menu .nav-item {
                padding: 10px 13px !important;
            }
            
            .dropdown-menu .nav-item:hover {
                padding-left: 13px !important;
            }
        }
        
        /* Large phones in landscape / Small tablets in portrait (481px - 600px) */
        @media (min-width: 481px) and (max-width: 600px) {
            .topbar {
                height: 50px;
                padding: 0 12px;
            }
            
            .topbar-logo {
                font-size: 1.1rem;
                gap: 6px;
            }
            
            .burger-menu {
                font-size: 1.2rem;
                padding: 6px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 6px;
                font-size: 1rem;
            }
            
            .topbar-left, .topbar-right {
                gap: 8px;
            }
            
            .sidebar {
                width: 270px;
                right: -270px;
                top: 50px;
                height: calc(100vh - 50px);
            }
            
            .sidebar-header {
                padding: 12px;
            }
            
            .sidebar-header h3 {
                font-size: 0.95rem;
            }
            
            .nav-item {
                padding: 9px 12px;
                margin: 3px 6px;
                font-size: 13px;
            }
            
            .main-content {
                margin-top: 50px;
                min-height: calc(100vh - 50px);
                padding: 14px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .dropdown-menu {
                min-width: 160px;
            }
            
            .dropdown-item {
                padding: 9px 12px;
                font-size: 13px;
            }
            
            .dropdown-menu .nav-item {
                padding: 9px 12px !important;
            }
            
            .dropdown-menu .nav-item:hover {
                padding-left: 12px !important;
            }
        }
        
        /* Standard phones in portrait (361px - 480px) */
        @media (min-width: 361px) and (max-width: 480px) {
            .topbar {
                height: 48px;
                padding: 0 10px;
            }
            
            .topbar-logo {
                font-size: 1rem;
                gap: 5px;
            }
            
            .burger-menu {
                font-size: 1.15rem;
                padding: 5px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 5px;
                font-size: 0.95rem;
            }
            
            .topbar-left, .topbar-right {
                gap: 7px;
            }
            
            .sidebar {
                top: 48px;
                height: calc(100vh - 48px);
                width: 260px;
                right: -260px;
            }
            
            .sidebar-header {
                padding: 11px;
            }
            
            .sidebar-header h3 {
                font-size: 0.92rem;
            }
            
            .nav-item {
                padding: 8px 11px;
                margin: 2px 5px;
                font-size: 12px;
            }
            
            .main-content {
                margin-top: 48px;
                min-height: calc(100vh - 48px);
                padding: 12px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .dropdown-menu {
                min-width: 155px;
            }
            
            .dropdown-item {
                padding: 8px 11px;
                font-size: 12px;
            }
            
            .dropdown-menu .nav-item {
                padding: 8px 11px !important;
            }
            
            .dropdown-menu .nav-item:hover {
                padding-left: 11px !important;
            }
        }
        
        /* Small phones (320px - 360px) */
        @media (max-width: 360px) {
            .topbar {
                height: 44px;
                padding: 0 6px;
            }
            
            .topbar-logo {
                font-size: 0.75rem;
                gap: 3px;
                max-width: 100px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .burger-menu {
                font-size: 1rem;
                padding: 3px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 3px;
                font-size: 0.8rem;
            }
            
            .topbar-left, .topbar-right {
                gap: 5px;
            }
            
            .sidebar {
                top: 45px;
                height: calc(100vh - 45px);
                width: 240px;
                right: -240px;
            }
            
            .sidebar-header {
                padding: 10px;
            }
            
            .sidebar-header h3 {
                font-size: 0.88rem;
            }
            
            .nav-item {
                padding: 7px 10px;
                margin: 2px 4px;
                font-size: 11px;
            }
            
            .main-content {
                margin-top: 45px;
                min-height: calc(100vh - 45px);
                padding: 10px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .dropdown-menu {
                min-width: 140px;
            }
            
            .dropdown-item {
                padding: 7px 10px;
                font-size: 11px;
            }
            
            .dropdown-menu .nav-item {
                padding: 7px 10px !important;
            }
            
            .dropdown-menu .nav-item:hover {
                padding-left: 10px !important;
            }
            
            .notification-badge {
                width: 12px;
                height: 12px;
                font-size: 7px;
                top: -2px;
                right: -2px;
            }
            
            .profile-picture,
            .profile-picture-default {
                width: 26px;
                height: 26px;
                font-size: 12px;
            }
        }
        
        /* Extra optimizations for very small devices (max-width: 320px) */
        @media (max-width: 320px) {
            .topbar {
                height: 40px;
                padding: 0 4px;
            }
            
            .topbar-logo {
                font-size: 0.7rem;
                gap: 2px;
                max-width: 80px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .burger-menu {
                font-size: 0.9rem;
                padding: 2px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 2px;
                font-size: 0.75rem;
            }
            
            .topbar-left, .topbar-right {
                gap: 4px;
            }
            
            .sidebar {
                top: 42px;
                height: calc(100vh - 42px);
                width: 220px;
                right: -220px;
            }
            
            .sidebar-header {
                padding: 8px;
            }
            
            .sidebar-header h3 {
                font-size: 0.85rem;
            }
            
            .nav-item {
                padding: 6px 8px;
                margin: 2px 4px;
                font-size: 10px;
            }
            
            .main-content {
                margin-top: 42px;
                min-height: calc(100vh - 42px);
                padding: 8px;
            }
            
            .dropdown-menu {
                min-width: 130px;
            }
            
            .dropdown-item {
                padding: 6px 8px;
                font-size: 10px;
            }
            
            .dropdown-menu .nav-item {
                padding: 6px 8px !important;
            }
            
            .dropdown-menu .nav-item:hover {
                padding-left: 8px !important;
            }
            
            .notification-badge {
                width: 10px;
                height: 10px;
                font-size: 6px;
                top: -1px;
                right: -1px;
            }
            
            .profile-picture,
            .profile-picture-default {
                width: 22px;
                height: 22px;
                font-size: 10px;
            }
        }
        
        <?php echo $__env->yieldPushContent('styles'); ?>
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav class="topbar">
        <div class="topbar-left">
            <div class="topbar-logo">
                <?php echo e($schoolName ?? 'Driving School'); ?>

            </div>
        </div>
        
        <div class="topbar-right">
            <button class="burger-menu" onclick="toggleSidebar()">
                ☰
            </button>
            
            <div class="notification-icon">
                🔔
                <span class="notification-badge"></span>
            </div>
            
            <div class="profile-dropdown" onclick="toggleProfileDropdown()">
                <?php if(Auth::guard('admin')->check()): ?>
                    <?php $user = Auth::guard('admin')->user(); ?>
                    <?php if($user->profile_picture): ?>
                        <img src="<?php echo e(asset('storage/' . $user->profile_picture)); ?>" alt="Profile" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture-default"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                    <?php endif; ?>
                    <?php echo e($user->name); ?>

                <?php elseif(Auth::guard('instructor')->check()): ?>
                    <?php $user = Auth::guard('instructor')->user(); ?>
                    <?php if($user->profile_picture): ?>
                        <img src="<?php echo e(asset('storage/' . $user->profile_picture)); ?>" alt="Profile" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture-default"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                    <?php endif; ?>
                    <?php echo e($user->name); ?>

                <?php elseif(Auth::guard('student')->check()): ?>
                    <?php $user = Auth::guard('student')->user(); ?>
                    <?php if($user->profile_picture): ?>
                        <img src="<?php echo e(asset('storage/' . $user->profile_picture)); ?>" alt="Profile" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture-default"><?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                    <?php endif; ?>
                    <?php echo e($user->name); ?>

                <?php else: ?>
                    <div class="profile-picture-default">U</div>
                    User
                <?php endif; ?>
                
                <div class="dropdown-menu" id="profileDropdownMenu">
                    <?php if(Auth::guard('admin')->check()): ?>
                        <a href="<?php echo e($schoolRoute('admin.profile')); ?>" class="dropdown-item nav-item" data-page="profile">
                            View Profile
                        </a>
                    <?php elseif(Auth::guard('instructor')->check()): ?>
                        <a href="<?php echo e($schoolRoute('instructor.profile')); ?>" class="dropdown-item nav-item" data-page="profile">
                            View Profile
                        </a>
                    <?php elseif(Auth::guard('student')->check()): ?>
                        <a href="<?php echo e($schoolRoute('student.profile')); ?>" class="dropdown-item nav-item" data-page="profile">
                            View Profile
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <?php if(Auth::guard('admin')->check()): ?>
                        <form method="POST" action="<?php echo e($schoolRoute('admin.logout')); ?>" style="margin: 0;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item">
                                Logout
                            </button>
                        </form>
                    <?php elseif(Auth::guard('instructor')->check()): ?>
                        <form method="POST" action="<?php echo e($schoolRoute('instructor.logout')); ?>" style="margin: 0;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item">
                                Logout
                            </button>
                        </form>
                    <?php elseif(Auth::guard('student')->check()): ?>
                        <form method="POST" action="<?php echo e($schoolRoute('student.logout')); ?>" style="margin: 0;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item">
                                Logout
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>
                <?php if(Auth::guard('admin')->check()): ?>
                    Admin Menu
                <?php elseif(Auth::guard('instructor')->check()): ?>
                    Instructor Menu
                <?php elseif(Auth::guard('student')->check()): ?>
                    Student Menu
                <?php else: ?>
                    Menu
                <?php endif; ?>
            </h3>
        </div>
        
        <nav class="sidebar-nav">
            <?php if(Auth::guard('admin')->check()): ?>
                <a href="<?php echo e($schoolRoute('admin.dashboard')); ?>" class="nav-item" data-page="dashboard">Dashboard</a>
                <a href="<?php echo e($schoolRoute('admin.userManagement')); ?>" class="nav-item" data-page="user-management">User Management</a>
                <a href="<?php echo e($schoolRoute('admin.schedules')); ?>" class="nav-item" data-page="schedules">Schedules</a>
                <a href="<?php echo e($schoolRoute('admin.removalRequests')); ?>" class="nav-item" data-page="removal-requests">Removal Requests</a>
                <a href="<?php echo e($schoolRoute('admin.enrollmentRequests.index')); ?>" class="nav-item" data-page="enrollment-requests">Enrollment Requests</a>
                <a href="<?php echo e($schoolRoute('admin.courses')); ?>" class="nav-item" data-page="courses">Courses</a>
                <a href="<?php echo e($schoolRoute('admin.bookings.index')); ?>" class="nav-item" data-page="bookings">Bookings</a>
                <a href="<?php echo e($schoolRoute('admin.payments.index')); ?>" class="nav-item" data-page="payments">Payments</a>
                <a href="<?php echo e($schoolRoute('admin.reports.index')); ?>" class="nav-item" data-page="reports">Reports & Analytics</a>
                <a href="<?php echo e($schoolRoute('admin.settings')); ?>" class="nav-item" data-page="settings">Settings</a>
            <?php elseif(Auth::guard('instructor')->check()): ?>
                <a href="<?php echo e($schoolRoute('instructor.dashboard')); ?>" class="nav-item" data-page="dashboard">Dashboard</a>
                <a href="<?php echo e($schoolRoute('instructor.schedule')); ?>" class="nav-item" data-page="my-schedule">My Schedule</a>
                <a href="<?php echo e($schoolRoute('instructor.students.index')); ?>" class="nav-item" data-page="students">My Students</a>
                <a href="<?php echo e($schoolRoute('instructor.grades')); ?>" class="nav-item" data-page="grades">Grades</a>
                <a href="<?php echo e($schoolRoute('instructor.reports')); ?>" class="nav-item" data-page="reports">Reports</a>
            <?php elseif(Auth::guard('student')->check()): ?>
                <?php
                    $currentStudent = Auth::guard('student')->user();
                ?>
                
                <?php if($currentStudent->role === 'guest'): ?>
                    
                    <a href="<?php echo e($schoolRoute('guest.dashboard')); ?>" class="nav-item" data-page="dashboard">Dashboard</a>
                    <a href="<?php echo e($schoolRoute('guest.courses')); ?>" class="nav-item" data-page="courses">Browse Courses</a>
                    <a href="<?php echo e($schoolRoute('guest.enrollmentRequests')); ?>" class="nav-item" data-page="enrollment-requests">My Enrollment Requests</a>
                <?php else: ?>
                    
                    <a href="<?php echo e($schoolRoute('student.dashboard')); ?>" class="nav-item" data-page="dashboard">Dashboard</a>
                    <a href="<?php echo e($schoolRoute('student.schedule')); ?>" class="nav-item" data-page="schedule">My Schedule</a>
                    <a href="<?php echo e($schoolRoute('student.courses.index')); ?>" class="nav-item" data-page="courses">Browse Courses</a>
                    <a href="<?php echo e($schoolRoute('student.payments.index')); ?>" class="nav-item" data-page="payments">My Payments</a>
                    <a href="<?php echo e($schoolRoute('student.progress.index')); ?>" class="nav-item" data-page="progress">My Progress</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </div>
    
    <!-- Sidebar Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    
    <script>
        let sidebarOpen = false;
        let profileDropdownOpen = false;
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');
            
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                mainContent.classList.add('sidebar-open');
            } else {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                mainContent.classList.remove('sidebar-open');
            }
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');
            
            sidebarOpen = false;
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
        }
        
        function toggleProfileDropdown() {
            const dropdownMenu = document.getElementById('profileDropdownMenu');
            profileDropdownOpen = !profileDropdownOpen;
            
            if (profileDropdownOpen) {
                dropdownMenu.classList.add('show');
            } else {
                dropdownMenu.classList.remove('show');
            }
        }
        
        function closeProfileDropdown() {
            const dropdownMenu = document.getElementById('profileDropdownMenu');
            profileDropdownOpen = false;
            dropdownMenu.classList.remove('show');
        }
        
        // Function to reinitialize JavaScript for dynamically loaded content
        function reinitializeJavaScript() {
            // Close any open modals first
            closeAllModals();
            
            // Ensure all modals are properly hidden on load
            setTimeout(() => {
                closeAllModals();
            }, 100);
            
            // Reinitialize all interactive elements
            initializeModals();
            initializeForms();
            initializeButtons();
            initializeTabs();
            initializeProfileFunctions();
            initializeInteractiveElements();
        }
        
        function initializeModals() {
            // Only set generic modal functions if page-specific ones don't exist
            // This allows page-specific functions (like createCourse(), editCourse()) to take precedence
            
            if (typeof window.openCreateModal !== 'function') {
                window.openCreateModal = function() {
                    console.log('Generic openCreateModal called');
                    const modal = document.getElementById('createModal');
                    console.log('createModal element:', modal);
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.style.visibility = 'visible';
                        console.log('createModal opened');
                    } else {
                        console.error('createModal element not found');
                    }
                };
            }
            
            if (typeof window.closeCreateModal !== 'function') {
                window.closeCreateModal = function() {
                    console.log('Generic closeCreateModal called');
                    const modal = document.getElementById('createModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.style.visibility = 'hidden';
                        console.log('createModal closed');
                    }
                };
            }
            
            if (typeof window.openEditModal !== 'function') {
                window.openEditModal = function(id, name, email, contact, address) {
                    console.log('Generic openEditModal called with:', id, name, email, contact, address);
                    const modal = document.getElementById('editModal');
                    console.log('editModal element:', modal);
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.style.visibility = 'visible';
                        
                        // Populate form fields if they exist
                        const form = modal.querySelector('form');
                        if (form && id) {
                            // Update form action
                            if (window.location.href.includes('students')) {
                                const baseUrl = document.querySelector('script').textContent.match(/studentBaseUrl = '([^']+)'/);
                                if (baseUrl) {
                                    form.action = `${baseUrl[1]}/${id}`;
                                }
                            }
                            
                            // Populate fields
                            const nameField = document.getElementById('edit_name');
                            const emailField = document.getElementById('edit_email');
                            const contactField = document.getElementById('edit_contact');
                            const addressField = document.getElementById('edit_address');
                            const licenseField = document.getElementById('edit_license_number');
                            
                            if (nameField && name) nameField.value = name;
                        if (emailField && email) emailField.value = email;
                        if (contactField && contact) contactField.value = contact;
                        if (addressField && address) addressField.value = address;
                        if (licenseField && address) licenseField.value = address; // For instructors, address might be license
                    }
                    console.log('editModal opened');
                } else {
                    console.error('editModal element not found');
                }
            };
            }
            
            if (typeof window.closeEditModal !== 'function') {
                window.closeEditModal = function() {
                    console.log('Generic closeEditModal called');
                    const modal = document.getElementById('editModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.style.visibility = 'hidden';
                        console.log('editModal closed');
                    }
                };
            }
            
            if (typeof window.closeModal !== 'function') {
                window.closeModal = function() {
                    console.log('Generic closeModal called - closing all modals');
                    closeAllModals();
                };
            }
            
            if (typeof window.openDeleteModal !== 'function') {
                window.openDeleteModal = function(id) {
                    console.log('Generic openDeleteModal called with:', id);
                    const modal = document.getElementById('deleteModal');
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.style.visibility = 'visible';
                        console.log('deleteModal opened');
                    }
                };
            }
            
            if (typeof window.closeDeleteModal !== 'function') {
                window.closeDeleteModal = function() {
                    console.log('Generic closeDeleteModal called');
                    const modal = document.getElementById('deleteModal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.style.visibility = 'hidden';
                        console.log('deleteModal closed');
                    }
                };
            }
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                    event.target.style.visibility = 'hidden';
                    console.log('Modal closed by clicking outside');
                }
            }
            
            console.log('Modal functions initialized');
        }
        
        // Test function to verify modal functionality
        window.testModals = function() {
            console.log('=== Modal Function Test ===');
            console.log('openCreateModal function:', typeof window.openCreateModal);
            console.log('closeCreateModal function:', typeof window.closeCreateModal);
            console.log('openEditModal function:', typeof window.openEditModal);
            console.log('closeEditModal function:', typeof window.closeEditModal);
            console.log('createModal element:', document.getElementById('createModal'));
            console.log('editModal element:', document.getElementById('editModal'));
            console.log('=== End Test ===');
        };
        
        function initializeForms() {
            // Reinitialize CSRF tokens for new forms
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    let csrfInput = form.querySelector('input[name="_token"]');
                    if (csrfInput) {
                        csrfInput.value = csrfToken;
                    }
                });
            }
            
            // Universal AJAX form handling for ALL forms
            initializeUniversalAjaxForms();
            
            console.log('Forms reinitialized with universal AJAX handling');
        }
        
        // Universal AJAX form system for all current and future pages
        function initializeUniversalAjaxForms() {
            // Remove existing event listeners to prevent duplicates
            document.querySelectorAll('form').forEach(form => {
                form.removeEventListener('submit', handleFormSubmit);
            });
            
            // Add AJAX handling to ALL forms except logout forms
            document.querySelectorAll('form').forEach(form => {
                // Skip logout forms and external forms
                if (isExcludedForm(form)) {
                    return;
                }
                
                // Add universal AJAX submission
                form.addEventListener('submit', handleFormSubmit);
                console.log('AJAX enabled for form:', form.action || 'inline form');
            });
        }
        
        // Check if form should be excluded from AJAX handling
        function isExcludedForm(form) {
            const action = form.action || '';
            const formHTML = form.outerHTML;
            
            // Exclude logout forms
            if (action.includes('logout') || formHTML.includes('logout')) {
                return true;
            }
            
            // Exclude external URLs
            if (action.startsWith('http') && !action.includes(window.location.hostname)) {
                return true;
            }
            
            // Exclude forms with data-no-ajax attribute (for future customization)
            if (form.hasAttribute('data-no-ajax')) {
                return true;
            }
            
            // Exclude forms with native-form class (for direct form submission)
            if (form.classList.contains('native-form')) {
                return true;
            }
            
            // Exclude protected forms (timeslot forms)
            if (form.hasAttribute('data-protected')) {
                return true;
            }
            
            // Exclude timeslot-form class
            if (form.classList.contains('timeslot-form')) {
                return true;
            }
            
            // Exclude timeslot toggle forms
            if (action.includes('timeslots') && action.includes('toggle')) {
                return true;
            }
            
            return false;
        }
        
        // Universal form submission handler
        function handleFormSubmit(e) {
            const form = e.target;
            
            // Double-check exclusion (in case form was dynamically added)
            if (isExcludedForm(form)) {
                return; // Let it submit normally
            }
            
            e.preventDefault();
            
            // Determine form type and handle accordingly
            if (isModalForm(form)) {
                submitModalFormAjax(form);
            } else if (isToggleForm(form)) {
                submitToggleFormAjax(form);
            } else if (isInlineForm(form)) {
                submitInlineFormAjax(form);
            } else {
                submitGenericFormAjax(form);
            }
        }
        
        // Detect modal forms
        function isModalForm(form) {
            return form.closest('.modal') !== null;
        }
        
        // Detect toggle/status forms
        function isToggleForm(form) {
            const action = form.action || '';
            return action.includes('toggle') || 
                   action.includes('status') || 
                   form.style.display === 'inline' ||
                   form.querySelector('button[type="submit"]')?.textContent.toLowerCase().includes('activate') ||
                   form.querySelector('button[type="submit"]')?.textContent.toLowerCase().includes('deactivate') ||
                   form.querySelector('button[type="submit"]')?.textContent.toLowerCase().includes('available') ||
                   form.querySelector('button[type="submit"]')?.textContent.toLowerCase().includes('unavailable');
        }
        
        // Detect inline forms (like profile edit forms)
        function isInlineForm(form) {
            return form.closest('.profile-edit') !== null ||
                   form.closest('[class*="edit"]') !== null ||
                   form.querySelector('button[type="submit"]')?.textContent.toLowerCase().includes('update');
        }
        
        // Modal form submission (create/edit records)
        function submitModalFormAjax(form) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton?.textContent || 'Submit';
            
            // Show loading state
            if (submitButton) {
                submitButton.textContent = 'Saving...';
                submitButton.disabled = true;
            }
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            })
            .then(result => {
                // Close modal and refresh content
                closeAllModals();
                showNotification('Success! Record saved successfully.', 'success');
                reloadCurrentPage();
            })
            .catch(error => {
                console.error('Modal form submission error:', error);
                showNotification('Error! Failed to save record. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                if (submitButton) {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }
        
        // Toggle form submission (status changes, quick actions)
        function submitToggleFormAjax(form) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton?.textContent || 'Submit';
            
            // Show loading state
            if (submitButton) {
                submitButton.textContent = 'Loading...';
                submitButton.disabled = true;
            }
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            })
            .then(result => {
                showNotification('Status updated successfully!', 'success');
                reloadCurrentPage();
            })
            .catch(error => {
                console.error('Toggle form submission error:', error);
                showNotification('Error! Failed to update status. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                if (submitButton) {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }
        
        // Inline form submission (profile updates, settings)
        function submitInlineFormAjax(form) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton?.textContent || 'Submit';
            
            // Show loading state
            if (submitButton) {
                submitButton.textContent = 'Updating...';
                submitButton.disabled = true;
            }
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            })
            .then(result => {
                showNotification('Profile updated successfully!', 'success');
                reloadCurrentPage();
            })
            .catch(error => {
                console.error('Inline form submission error:', error);
                showNotification('Error! Failed to update. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                if (submitButton) {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }
        
        // Generic form submission (fallback for any other forms)
        function submitGenericFormAjax(form) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton?.textContent || 'Submit';
            
            // Show loading state
            if (submitButton) {
                submitButton.textContent = 'Processing...';
                submitButton.disabled = true;
            }
            
            fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            })
            .then(result => {
                showNotification('Action completed successfully!', 'success');
                reloadCurrentPage();
            })
            .catch(error => {
                console.error('Generic form submission error:', error);
                showNotification('Error! Failed to process request. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                if (submitButton) {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }
        
        // Helper function to reload current page content
        function reloadCurrentPage() {
            setTimeout(() => {
                loadContent(window.location.pathname);
            }, 500); // Small delay to show notification
        }
        
        // Enhanced notification system with multiple types
        function showNotification(message, type = 'info', duration = 3000) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            
            // Set styles based on type
            const colors = {
                success: { bg: '#4CAF50', icon: '✓' },
                error: { bg: '#f44336', icon: '✗' },
                warning: { bg: '#ff9800', icon: '⚠' },
                info: { bg: '#2196F3', icon: 'ℹ' }
            };
            
            const color = colors[type] || colors.info;
            
            notification.style.cssText = `
                position: fixed;
                top: 80px;
                right: 20px;
                background: ${color.bg};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: bold;
                max-width: 350px;
                font-family: Arial, sans-serif;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                transition: all 0.3s ease;
            `;
            
            notification.innerHTML = `
                <span style="font-size: 16px;">${color.icon}</span>
                <span>${message}</span>
                <span style="margin-left: auto; opacity: 0.7; font-size: 12px;">×</span>
            `;
            
            // Add click to dismiss
            notification.addEventListener('click', () => {
                notification.remove();
            });
            
            // Add to page
            document.body.appendChild(notification);
            
            // Auto remove after specified duration
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        }
        
        // Test function for the universal AJAX system
        window.testUniversalAjax = function() {
            console.log('=== Universal AJAX System Test ===');
            
            const forms = document.querySelectorAll('form');
            console.log(`Total forms found: ${forms.length}`);
            
            let ajaxForms = 0;
            let excludedForms = 0;
            
            forms.forEach((form, index) => {
                const isExcluded = isExcludedForm(form);
                console.log(`Form ${index + 1}:`, {
                    action: form.action || 'inline',
                    method: form.method || 'GET',
                    isModal: isModalForm(form),
                    isToggle: isToggleForm(form),
                    isInline: isInlineForm(form),
                    excluded: isExcluded
                });
                
                if (isExcluded) {
                    excludedForms++;
                } else {
                    ajaxForms++;
                }
            });
            
            console.log(`AJAX-enabled forms: ${ajaxForms}`);
            console.log(`Excluded forms: ${excludedForms}`);
            console.log('=== End Test ===');
            
            showNotification(`Universal AJAX: ${ajaxForms} forms enabled, ${excludedForms} excluded`, 'info', 5000);
        };
        
        function initializeButtons() {
            // Don't modify onclick attributes - just ensure our global functions exist
            // The onclick attributes will call our global functions directly
            console.log('Buttons reinitialized - global functions available');
        }
        
        function initializeTabs() {
            // Tab switching functionality
            window.switchTab = function(tabName) {
                // Hide all tab contents
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });
                
                // Remove active class from all tabs
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Show selected tab content
                const selectedContent = document.getElementById(tabName);
                if (selectedContent) {
                    selectedContent.style.display = 'block';
                }
                
                // Add active class to selected tab
                const selectedTab = document.querySelector(`[onclick="switchTab('${tabName}')"]`);
                if (selectedTab) {
                    selectedTab.classList.add('active');
                }
            };
            
            console.log('Tabs reinitialized');
        }
        
        function initializeProfileFunctions() {
            // Profile edit functionality
            window.showEditForm = function() {
                const viewProfile = document.querySelector('.profile-view');
                const editProfile = document.querySelector('.profile-edit');
                if (viewProfile) viewProfile.style.display = 'none';
                if (editProfile) editProfile.style.display = 'block';
            };
            
            window.cancelEdit = function() {
                const viewProfile = document.querySelector('.profile-view');
                const editProfile = document.querySelector('.profile-edit');
                if (viewProfile) viewProfile.style.display = 'block';
                if (editProfile) editProfile.style.display = 'none';
            };
            
            console.log('Profile functions reinitialized');
        }
        
        function initializeInteractiveElements() {
            // Initialize other interactive elements
            window.toggleInstructorSelection = function() {
                const instructorField = document.getElementById('instructor_id');
                const instructorSelect = document.getElementById('instructor_select');
                if (instructorField && instructorSelect) {
                    const isSpecific = instructorField.value === 'specific';
                    instructorSelect.style.display = isSpecific ? 'block' : 'none';
                }
            };
            
            // Reinitialize any dropdown or interactive components
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (toggle && menu) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    });
                }
            });
            
            console.log('Interactive elements reinitialized');
        }
        
        // Function to close all open modals
        function closeAllModals() {
            // Find all modal elements and hide them
            const modals = document.querySelectorAll('.modal, [id*="Modal"], [id*="modal"]');
            modals.forEach(modal => {
                modal.style.display = 'none';
                modal.style.visibility = 'hidden';
                // Remove any inline styles that might force display
                if (modal.style.display !== 'none') {
                    modal.style.setProperty('display', 'none', 'important');
                }
            });
            
            // Also call specific close functions if they exist
            if (typeof closeCreateModal === 'function') {
                try { closeCreateModal(); } catch(e) { console.warn('closeCreateModal error:', e); }
            }
            if (typeof closeEditModal === 'function') {
                try { closeEditModal(); } catch(e) { console.warn('closeEditModal error:', e); }
            }
            if (typeof closeDeleteModal === 'function') {
                try { closeDeleteModal(); } catch(e) { console.warn('closeDeleteModal error:', e); }
            }
            if (typeof closeAssignModal === 'function') {
                try { closeAssignModal(); } catch(e) { console.warn('closeAssignModal error:', e); }
            }
            
            console.log('All modals closed');
        }
        
        // AJAX Navigation System
        function loadContent(url, pushState = true) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            const mainContent = document.getElementById('mainContent');
            
            // Close sidebar before loading new content
            closeSidebar();
            
            // Close all open modals before loading new content
            closeAllModals();
            
            // Show loading overlay
            loadingOverlay.style.display = 'flex';
            
            // Make AJAX request
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                // Update main content
                mainContent.innerHTML = html;
                
                // Execute any script tags in the loaded content
                const scripts = mainContent.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.src) {
                        // External script - create and append
                        const newScript = document.createElement('script');
                        newScript.src = script.src;
                        document.head.appendChild(newScript);
                    } else {
                        // Inline script - create a new script element and append to ensure proper global execution
                        const newScript = document.createElement('script');
                        newScript.textContent = script.textContent;
                        document.body.appendChild(newScript);
                        // Remove it after execution to keep DOM clean
                        setTimeout(() => document.body.removeChild(newScript), 100);
                    }
                });
                
                // NO generic initialization - pages handle their own
                
                // Re-initialize forms for AJAX handling (respects data-no-ajax)
                initializeForms();
                
                // Update browser URL if requested
                if (pushState) {
                    history.pushState({url: url}, '', url);
                }
                
                // Update active sidebar item
                updateActiveNavItem(url);
                
                // Hide loading overlay
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 200);
            })
            .catch(error => {
                console.error('Error loading content:', error);
                loadingOverlay.style.display = 'none';
                
                // On error, redirect to the page directly instead of showing error
                window.location.href = url;
            });
        }
        
        function updateActiveNavItem(url) {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to current nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.getAttribute('href') === url) {
                    item.classList.add('active');
                }
            });
        }
        
        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadContent(e.state.url, false);
            }
        });
        
        // Initialize AJAX navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar navigation clicks
            document.addEventListener('click', function(e) {
                const navItem = e.target.closest('.nav-item');
                if (navItem && navItem.getAttribute('href') && !navItem.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const url = navItem.getAttribute('href');
                    loadContent(url);
                    
                    // Close sidebar on mobile after navigation
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                }
            });
            
            // Set initial state
            const currentUrl = window.location.pathname;
            history.replaceState({url: currentUrl}, '', currentUrl);
            updateActiveNavItem(currentUrl);
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            // Close sidebar
            if (sidebarOpen && !e.target.closest('.sidebar') && !e.target.closest('.burger-menu')) {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            }
            
            // Close profile dropdown
            if (profileDropdownOpen && !e.target.closest('.profile-dropdown')) {
                closeProfileDropdown();
            }
        });
    </script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\layouts\app.blade.php ENDPATH**/ ?>