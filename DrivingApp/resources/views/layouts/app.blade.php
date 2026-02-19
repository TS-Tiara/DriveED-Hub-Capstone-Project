<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $schoolName ?? 'Driving School' }}</title>
    
    @php
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
    @endphp
    
    <style>
        :root {
            /* Brand Colors */
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --accent-color: {{ $accentColor }};
            --primary-rgb: {{ implode(', ', $primaryRgb) }};
            --accent-rgb: {{ implode(', ', $accentRgb) }};
            
            /* Sidebar */
            --sidebar-bg: {{ $sidebarBg }};
            --sidebar-text: {{ $sidebarText }};
            --sidebar-hover: {{ $sidebarHover }};
            
            /* Header */
            --header-gradient: {{ $headerBg }};
            --page-header-border: {{ $settings->page_header_border ?? '#667eea' }};
            
            /* Buttons */
            --btn-primary-bg: {{ $settings->button_primary_bg ?? '#667eea' }};
            --btn-primary-text: {{ $settings->button_primary_text ?? '#ffffff' }};
            --btn-secondary-bg: {{ $settings->button_secondary_bg ?? '#6c757d' }};
            --btn-secondary-text: {{ $settings->button_secondary_text ?? '#ffffff' }};
            --btn-success-bg: {{ $settings->button_success_bg ?? '#28a745' }};
            --btn-success-text: {{ $settings->button_success_text ?? '#ffffff' }};
            --btn-danger-bg: {{ $settings->button_danger_bg ?? '#dc3545' }};
            --btn-danger-text: {{ $settings->button_danger_text ?? '#ffffff' }};
            
            /* Borders & Shapes */
            --border-radius: {{ $settings->border_radius ?? 8 }}px;
            --button-border-radius: {{ $settings->button_border_radius ?? 8 }}px;
            
            /* Modals */
            --modal-header-bg: {{ $settings->modal_header_bg ?? '#667eea' }};
            --modal-header-text: {{ $settings->modal_header_text ?? '#ffffff' }};
            --modal-border-color: {{ $settings->modal_border_color ?? '#667eea' }};
            
            /* Cards */
            --card-border-color: {{ $settings->card_border_color ?? '#e5e7eb' }};
            --card-header-bg: {{ $settings->card_header_bg ?? '#f9fafb' }};
            
            /* Badges */
            --badge-pending-bg: {{ $settings->badge_pending_bg ?? '#fbbf24' }};
            --badge-pending-text: {{ $settings->badge_pending_text ?? '#78350f' }};
            --badge-approved-bg: {{ $settings->badge_approved_bg ?? '#10b981' }};
            --badge-approved-text: {{ $settings->badge_approved_text ?? '#065f46' }};
            --badge-cancelled-bg: {{ $settings->badge_cancelled_bg ?? '#ef4444' }};
            --badge-cancelled-text: {{ $settings->badge_cancelled_text ?? '#7f1d1d' }};
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
        
        @if($backgroundType === 'image' && $backgroundImage)
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: {{ $bodyBg }};
            background-size: {{ $bodyBgSize }};
            background-position: {{ $bodyBgPosition }};
            background-attachment: {{ $bodyBgAttachment }};
            background-repeat: no-repeat;
            opacity: {{ $backgroundOpacity / 100 }};
            z-index: -1;
        }
        @else
        body {
            background: {{ $bodyBg }};
        }
        @endif
        
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
            background: {{ $headerBg }};
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
        
        /* Floating Sidebar Toggle Tab */
        .sidebar-toggle-tab {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
            background: var(--primary-color, #667eea);
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            width: 28px;
            height: 56px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .sidebar-toggle-tab:hover {
            width: 34px;
            background: var(--primary-color, #5a6fd6);
            box-shadow: 3px 2px 14px rgba(0,0,0,0.2);
        }
        
        .sidebar-toggle-tab svg {
            display: block;
            transition: transform 0.3s ease;
        }
        
        .sidebar-toggle-tab.active {
            left: 300px;
        }
        
        .sidebar-toggle-tab.active svg {
            transform: rotate(180deg);
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
            min-width: 18px;
            height: 18px;
            font-size: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }
        
        .notification-badge.has-notifications {
            display: flex;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 340px;
            max-width: 380px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            border-radius: 12px;
            z-index: 10002;
            display: none;
            border: 1px solid #e1e5e9;
            margin-top: 8px;
            max-height: 420px;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
        }

        .notification-dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .notification-dropdown-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .notification-mark-all {
            font-size: 12px;
            color: #667eea;
            cursor: pointer;
            border: none;
            background: none;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .notification-mark-all:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .notification-dropdown-body {
            overflow-y: auto;
            max-height: 340px;
        }

        .notification-item {
            display: flex;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #eef2ff;
        }

        .notification-item.unread:hover {
            background: #e0e7ff;
        }

        .notification-item-icon {
            font-size: 20px;
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-item-content {
            flex: 1;
            min-width: 0;
        }

        .notification-item-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-item-message {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notification-item-time {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 3px;
        }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
        }

        .notification-empty-icon {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .notification-empty-text {
            font-size: 13px;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 60px;
            left: -300px;
            width: 300px;
            height: calc(100vh - 60px);
            background: var(--sidebar-bg);
            transition: left 0.3s ease;
            z-index: 999;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            box-sizing: border-box;
        }
        
        .sidebar.active {
            left: 0;
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
            padding: 10px 0;
        }

        /* Sidebar Category Styles */
        .nav-category {
            margin-bottom: 4px;
        }

        .nav-category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            margin: 2px 10px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--primary-color);
            opacity: 1;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
            user-select: none;
            position: relative;
            overflow: hidden;
        }

        .nav-category-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: {{ $useGradient ? "linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%)" : "var(--primary-color)" }};
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-category-header:hover::before {
            transform: scaleY(1);
        }

        .nav-category-header:hover {
            background: var(--sidebar-hover);
            color: var(--primary-color);
            transform: translateX(5px);
            opacity: 1;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.15);
        }

        .nav-category-arrow {
            font-size: 10px;
            transition: transform 0.25s ease;
            color: var(--primary-color);
            opacity: 0.7;
        }

        .nav-category.collapsed .nav-category-arrow {
            transform: rotate(-90deg);
        }

        .nav-category-items {
            overflow: hidden;
            max-height: 500px;
            transition: max-height 0.3s ease, opacity 0.25s ease;
            opacity: 1;
        }

        .nav-category.collapsed .nav-category-items {
            max-height: 0;
            opacity: 0;
        }

        .nav-divider {
            height: 1px;
            background: rgba(0,0,0,0.08);
            margin: 8px 20px;
        }
        
        /* Sub-items inside categories: indented and slightly subdued */
        .nav-category-items .nav-item {
            padding: 10px 20px 10px 30px;
            font-size: 13px;
            opacity: 0.8;
        }

        .nav-category-items .nav-item:hover,
        .nav-category-items .nav-item.active {
            opacity: 1;
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
            background: {{ $useGradient ? "linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%)" : "var(--primary-color)" }};
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
            background: {{ $headerBg }};
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
            margin-top: 100px;
            padding: 0;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 100px);
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
            @if(($settings->button_style ?? 'solid') === 'gradient')
            background: linear-gradient(135deg, var(--btn-primary-bg) 0%, var(--btn-secondary-bg) 100%);
            @else
            background: var(--btn-primary-bg);
            @endif
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
        
        /* NEW Badge for Sidebar */
        .badge-new {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            color: #ffffff;
            border-radius: 12px;
            margin-left: 8px;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            }
            50% {
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.6);
            }
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        
        /* Sidebar-open class only needed on mobile (desktop sidebar is always visible) */
        @media (max-width: 1024px) {
            .main-content.sidebar-open {
                /* No content push on mobile - sidebar overlays */
            }
        }
        
        /* Breadcrumb Navigation */
        .breadcrumb-bar {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 8px 24px;
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.1);
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #6b7280;
            min-height: 20px;
            z-index: 998;
            transition: left 0.3s ease;
        }
        
        .breadcrumb-bar a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb-bar a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            margin: 0 8px;
            color: rgba(var(--primary-rgb), 0.35);
            font-size: 11px;
        }
        
        .breadcrumb-current {
            color: #374151;
            font-weight: 600;
        }
        
        @media (max-width: 600px) {
            .breadcrumb-bar {
                padding: 8px 14px;
                font-size: 12px;
            }
            .breadcrumb-separator {
                margin: 0 5px;
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
        
        /* ====== Empty State Styling ====== */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
        }
        .empty-state svg {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            color: #d1d5db;
            display: block;
        }
        .empty-state .empty-state-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .empty-state .empty-state-text {
            font-size: 0.9rem;
            color: #9ca3af;
        }

        /* Responsive Design */
        
        /* ====== Mobile Touch Target & Table Improvements ====== */
        @media (max-width: 768px) {
            /* Ensure min 44px touch targets for interactive elements */
            .btn, button.btn, .btn-action, .btn-sm,
            input[type="submit"], input[type="button"] {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* Table container always scrollable */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Better badge touch targets */
            .badge, .status-badge, .payment-badge, .license-badge {
                padding: 6px 12px;
            }
        }
        
        @media (max-width: 480px) {
            /* Stack page headers vertically */
            .page-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }
            
            /* Full-width buttons on small screens */
            .btn-action {
                padding: 10px 14px;
            }
        }
        
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
                left: -280px;
            }
            
            .sidebar-toggle-tab.active {
                left: 280px;
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
            
            .sidebar-toggle-tab {
                width: 24px;
                height: 48px;
            }
            
            .sidebar-toggle-tab.active {
                left: 280px;
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
                left: -280px;
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
            
            .sidebar-toggle-tab {
                width: 22px;
                height: 44px;
            }
            
            .sidebar-toggle-tab.active {
                left: 270px;
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
                left: -270px;
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
                margin-top: 90px;
                min-height: calc(100vh - 90px);
                padding: 14px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .breadcrumb-bar {
                top: 50px;
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
            
            .sidebar-toggle-tab {
                width: 22px;
                height: 44px;
            }
            
            .sidebar-toggle-tab.active {
                left: 260px;
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
                left: -260px;
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
                margin-top: 88px;
                min-height: calc(100vh - 88px);
                padding: 12px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .breadcrumb-bar {
                top: 48px;
            }
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
            
            .sidebar-toggle-tab {
                width: 20px;
                height: 40px;
            }
            
            .sidebar-toggle-tab.active {
                left: 240px;
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
                left: -240px;
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
                margin-top: 85px;
                min-height: calc(100vh - 85px);
                padding: 10px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-left: 0;
                margin-right: 0;
            }
            
            .breadcrumb-bar {
                top: 45px;
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
            
            .sidebar-toggle-tab {
                width: 20px;
                height: 38px;
            }
            
            .sidebar-toggle-tab.active {
                left: 220px;
            }
            
            .notification-icon, .profile-dropdown {
                padding: 4px;
                font-size: 0.75rem;
                min-height: 36px;
                min-width: 36px;
            }
            
            .topbar-left, .topbar-right {
                gap: 4px;
            }
            
            .sidebar {
                top: 42px;
                height: calc(100vh - 42px);
                width: 220px;
                left: -220px;
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
                margin-top: 82px;
                min-height: calc(100vh - 82px);
                padding: 8px;
            }
            
            .breadcrumb-bar {
                top: 42px;
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
        
        /* ====== Print Styles ====== */
        @media print {
            .topbar, .sidebar, .sidebar-overlay, .sidebar-toggle-tab,
            .breadcrumb-bar, .btn, button, .dropdown,
            .notification-badge, .profile-dropdown { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background: white !important; }
            .table-container { overflow: visible !important; }
            table { width: 100% !important; border-collapse: collapse; }
            th, td { border: 1px solid #ddd !important; padding: 6px 8px !important; font-size: 11pt; }
            .badge, .status-badge { border: 1px solid #999 !important; background: white !important; color: #333 !important; }
            a { text-decoration: none !important; color: #333 !important; }
            .card, .dashboard-card { box-shadow: none !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
        }

        @stack('styles')
    </style>
</head>
<body>
    <!-- Floating Sidebar Toggle Tab -->
    <button class="sidebar-toggle-tab" id="sidebarToggleTab" onclick="toggleSidebar()" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="sidebar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
    </button>
    
    <!-- Topbar -->
    <nav class="topbar" role="banner">
        <div class="topbar-left">
            <div class="topbar-logo" role="heading" aria-level="1">
                {{ $schoolName ?? 'Driving School' }}
            </div>
        </div>
        
        <div class="topbar-right">
            
            <div class="notification-icon" onclick="toggleNotificationDropdown(event)" role="button" aria-label="Notifications" aria-expanded="false" aria-haspopup="true" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleNotificationDropdown(event)}">
                🔔
                <span class="notification-badge" id="notificationBadge"></span>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <h4>Notifications</h4>
                        <button class="notification-mark-all" onclick="markAllNotificationsRead(event)">Mark all read</button>
                    </div>
                    <div class="notification-dropdown-body" id="notificationList">
                        <div class="notification-empty">
                            <div class="notification-empty-icon">🔔</div>
                            <div class="notification-empty-text">No notifications yet</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-dropdown" onclick="toggleProfileDropdown()" role="button" aria-label="User menu" aria-expanded="false" aria-haspopup="true" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleProfileDropdown()}">
                @if(Auth::guard('admin')->check())
                    @php $user = Auth::guard('admin')->user(); @endphp
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" class="profile-picture">
                    @else
                        <div class="profile-picture-default">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    {{ $user->name }}
                @elseif(Auth::guard('instructor')->check())
                    @php $user = Auth::guard('instructor')->user(); @endphp
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" class="profile-picture">
                    @else
                        <div class="profile-picture-default">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    {{ $user->name }}
                @elseif(Auth::guard('student')->check())
                    @php $user = Auth::guard('student')->user(); @endphp
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" class="profile-picture">
                    @else
                        <div class="profile-picture-default">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    {{ $user->name }}
                @else
                    <div class="profile-picture-default">U</div>
                    User
                @endif
                
                <div class="dropdown-menu" id="profileDropdownMenu">
                    @if(Auth::guard('admin')->check())
                        <a href="{{ $schoolRoute('admin.profile') }}" class="dropdown-item nav-item" data-page="profile">
                            View Profile
                        </a>
                    @elseif(Auth::guard('instructor')->check())
                        <a href="{{ $schoolRoute('instructor.profile') }}" class="dropdown-item nav-item" data-page="profile">
                            View Profile
                        </a>
                    @elseif(Auth::guard('student')->check())
                        @php $studentUser = Auth::guard('student')->user(); @endphp
                        @if($studentUser->role !== 'guest')
                            <a href="{{ $schoolRoute('student.profile') }}" class="dropdown-item nav-item" data-page="profile">
                                View Profile
                            </a>
                        @endif
                    @endif
                    <div class="dropdown-divider"></div>
                    @if(Auth::guard('admin')->check())
                        <form method="POST" action="{{ $schoolRoute('admin.logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                Logout
                            </button>
                        </form>
                    @elseif(Auth::guard('instructor')->check())
                        <form method="POST" action="{{ $schoolRoute('instructor.logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                Logout
                            </button>
                        </form>
                    @elseif(Auth::guard('student')->check())
                        @if($studentUser->role === 'guest')
                            <form method="POST" action="{{ $schoolRoute('logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    Logout
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ $schoolRoute('student.logout') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    Logout
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
        <div class="sidebar-header">
            <h3>
                @if(Auth::guard('admin')->check())
                    Admin Menu
                @elseif(Auth::guard('instructor')->check())
                    Instructor Menu
                @elseif(Auth::guard('student')->check())
                    Student Menu
                @else
                    Menu
                @endif
            </h3>
        </div>
        
        <nav class="sidebar-nav" role="menubar">
            @if(Auth::guard('admin')->check())
                {{-- Dashboard (standalone) --}}
                <a href="{{ $schoolRoute('admin.dashboard') }}" class="nav-item" data-page="dashboard">Dashboard</a>

                <div class="nav-divider"></div>

                {{-- Users --}}
                <div class="nav-category" data-category="admin-users">
                    <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                        <span>Users</span>
                        <span class="nav-category-arrow">&#9660;</span>
                    </div>
                    <div class="nav-category-items">
                        <a href="{{ $schoolRoute('admin.userManagement') }}" class="nav-item" data-page="user-management">User Management</a>
                        <a href="{{ $schoolRoute('admin.removalRequests') }}" class="nav-item" data-page="removal-requests">Removal Requests</a>
                    </div>
                </div>

                {{-- Theoretical Training --}}
                <div class="nav-category" data-category="admin-courses">
                    <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                        <span>Courses & Training</span>
                        <span class="nav-category-arrow">&#9660;</span>
                    </div>
                    <div class="nav-category-items">
                        <a href="{{ $schoolRoute('admin.courses') }}" class="nav-item" data-page="courses">Courses</a>
                        <a href="{{ $schoolRoute('admin.enrollments.index') }}" class="nav-item" data-page="enrollments">Enrollments</a>
                        <a href="{{ $schoolRoute('admin.theoretical.index') }}" class="nav-item" data-page="theoretical">Theoretical Training</a>
                    </div>
                </div>

                {{-- Sessions --}}
                <div class="nav-category" data-category="admin-sessions">
                    <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                        <span>Sessions</span>
                        <span class="nav-category-arrow">&#9660;</span>
                    </div>
                    <div class="nav-category-items">
                        <a href="{{ $schoolRoute('admin.schedules') }}" class="nav-item" data-page="schedules">Schedules</a>
                        <a href="{{ $schoolRoute('admin.bookings.index') }}" class="nav-item" data-page="bookings">Student Sessions</a>
                        <a href="{{ $schoolRoute('admin.sessions.index') }}" class="nav-item" data-page="session-completions">Session Completions</a>
                        <a href="{{ $schoolRoute('admin.phase-progressions.index') }}" class="nav-item" data-page="phase-progressions">Phase Progressions</a>
                    </div>
                </div>

                {{-- Payments & Reports --}}
                <div class="nav-category" data-category="admin-finance">
                    <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                        <span>Payments & Reports</span>
                        <span class="nav-category-arrow">&#9660;</span>
                    </div>
                    <div class="nav-category-items">
                        <a href="{{ $schoolRoute('admin.payments.index') }}" class="nav-item" data-page="payments">Payments</a>
                        <a href="{{ $schoolRoute('admin.reports.index') }}" class="nav-item" data-page="reports">Reports & Analytics</a>
                    </div>
                </div>

                <div class="nav-divider"></div>

                {{-- Settings & Branch Management (standalone bottom) --}}
                <a href="{{ $schoolRoute('admin.branches.index') }}" class="nav-item" data-page="branches">Branches</a>
                <a href="{{ $schoolRoute('admin.settings') }}" class="nav-item" data-page="settings">Settings</a>
            @elseif(Auth::guard('instructor')->check())
                <a href="{{ $schoolRoute('instructor.dashboard') }}" class="nav-item" data-page="dashboard">Dashboard</a>
                <a href="{{ $schoolRoute('instructor.schedule') }}" class="nav-item" data-page="my-schedule">My Schedule</a>
                <a href="{{ $schoolRoute('instructor.students.index') }}" class="nav-item" data-page="students">My Students</a>
                <a href="{{ $schoolRoute('instructor.sessions.index') }}" class="nav-item" data-page="sessions">
                    Session Logging
                </a>
                <a href="{{ $schoolRoute('instructor.grades') }}" class="nav-item" data-page="grades">Grades</a>

                <div class="nav-divider"></div>

                {{-- Theoretical Training --}}
                <a href="{{ $schoolRoute('instructor.theoretical.index') }}" class="nav-item" data-page="theoretical">Theoretical Training</a>
            @elseif(Auth::guard('student')->check())
                @php
                    $currentStudent = Auth::guard('student')->user();
                @endphp
                
                @if($currentStudent->role === 'guest')
                    {{-- Guest Navigation --}}
                    <a href="{{ $schoolRoute('guest.dashboard') }}" class="nav-item" data-page="dashboard">Dashboard</a>
                    <a href="{{ $schoolRoute('guest.courses') }}" class="nav-item" data-page="courses">Browse Courses</a>
                    <a href="{{ $schoolRoute('guest.enrollmentRequests') }}" class="nav-item" data-page="enrollment-requests">My Enrollments</a>
                @else
                    {{-- Student Navigation --}}
                    <a href="{{ $schoolRoute('student.dashboard') }}" class="nav-item" data-page="dashboard">Dashboard</a>

                    <div class="nav-divider"></div>

                    {{-- My Courses --}}
                    <div class="nav-category" data-category="student-courses">
                        <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                            <span>My Courses</span>
                            <span class="nav-category-arrow">&#9660;</span>
                        </div>
                        <div class="nav-category-items">
                            <a href="{{ $schoolRoute('student.my-course') }}" class="nav-item" data-page="my-course">Enrolled Course</a>
                            <a href="{{ $schoolRoute('student.progress.index') }}" class="nav-item" data-page="progress">My Progress</a>
                            <a href="{{ $schoolRoute('student.courses.index') }}" class="nav-item" data-page="courses">Browse Courses</a>
                        </div>
                    </div>

                    {{-- My Sessions & Payments --}}
                    <div class="nav-category" data-category="student-sessions">
                        <div class="nav-category-header" onclick="toggleCategory(this)" role="button" aria-expanded="false" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleCategory(this)}">
                            <span>Sessions & Payments</span>
                            <span class="nav-category-arrow">&#9660;</span>
                        </div>
                        <div class="nav-category-items">
                            <a href="{{ $schoolRoute('student.schedule') }}" class="nav-item" data-page="schedule">My Schedule</a>
                            <a href="{{ $schoolRoute('student.payments.index') }}" class="nav-item" data-page="payments">My Payments</a>
                        </div>
                    </div>
                @endif
            @endif
        </nav>
    </div>
    
    <!-- Sidebar Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()" role="presentation" aria-hidden="true"></div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" role="status" aria-label="Loading">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-bar" id="breadcrumbBar" role="navigation" aria-label="Breadcrumb">
        <span id="breadcrumbContent">
            <a href="#" onclick="loadContent(getDashboardUrl()); return false;">Dashboard</a>
        </span>
    </div>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent" role="main">
        @yield('content')
    </main>
    
    <script>
        let sidebarOpen = false;
        let profileDropdownOpen = false;

        // Sidebar category toggle with localStorage persistence
        function toggleCategory(header) {
            const category = header.closest('.nav-category');
            if (!category) return;
            category.classList.toggle('collapsed');
            // Save state
            const key = category.getAttribute('data-category');
            if (key) {
                const collapsed = JSON.parse(localStorage.getItem('sidebarCollapsed') || '{}');
                collapsed[key] = category.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', JSON.stringify(collapsed));
            }
        }

        // Restore collapsed state & auto-expand category of active page
        (function initSidebarCategories() {
            const collapsed = JSON.parse(localStorage.getItem('sidebarCollapsed') || '{}');
            document.querySelectorAll('.nav-category').forEach(function(cat) {
                const key = cat.getAttribute('data-category');
                // If this category contains the active nav-item, always expand it
                const hasActive = cat.querySelector('.nav-item.active');
                if (hasActive) {
                    cat.classList.remove('collapsed');
                    // Clear saved collapsed state for this category
                    if (key && collapsed[key]) {
                        collapsed[key] = false;
                        localStorage.setItem('sidebarCollapsed', JSON.stringify(collapsed));
                    }
                } else if (key && collapsed[key]) {
                    cat.classList.add('collapsed');
                }
            });
        })();
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleTab = document.getElementById('sidebarToggleTab');
            
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                if (toggleTab) toggleTab.classList.add('active');
            } else {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                if (toggleTab) toggleTab.classList.remove('active');
            }
            if (toggleTab) toggleTab.setAttribute('aria-expanded', sidebarOpen);
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleTab = document.getElementById('sidebarToggleTab');
            
            sidebarOpen = false;
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            if (toggleTab) {
                toggleTab.classList.remove('active');
                toggleTab.setAttribute('aria-expanded', 'false');
            }
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
        
        // ========================================
        // Notification Bell System
        // ========================================
        let notificationDropdownOpen = false;
        const notificationsUrl = @json($schoolRoute('notifications.index'));
        const markAllReadUrl = @json($schoolRoute('notifications.markAllAsRead'));
        
        function toggleNotificationDropdown(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('notificationDropdown');
            notificationDropdownOpen = !notificationDropdownOpen;
            
            if (notificationDropdownOpen) {
                dropdown.classList.add('show');
                closeProfileDropdown();
                fetchNotifications();
            } else {
                dropdown.classList.remove('show');
            }
        }
        
        function closeNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            notificationDropdownOpen = false;
            if (dropdown) dropdown.classList.remove('show');
        }
        
        function fetchNotifications() {
            fetch(notificationsUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                renderNotifications(data.notifications, data.unread_count);
            })
            .catch(err => console.warn('Failed to fetch notifications:', err));
        }
        
        function renderNotifications(notifications, unreadCount) {
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            
            // Update badge
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.classList.add('has-notifications');
            } else {
                badge.textContent = '';
                badge.classList.remove('has-notifications');
            }
            
            // Render list
            if (!notifications || notifications.length === 0) {
                list.innerHTML = `
                    <div class="notification-empty">
                        <div class="notification-empty-icon">🔔</div>
                        <div class="notification-empty-text">No notifications yet</div>
                    </div>`;
                return;
            }
            
            list.innerHTML = notifications.map(n => `
                <div class="notification-item ${n.is_read ? '' : 'unread'}" 
                     onclick="handleNotificationClick(event, ${n.id}, '${n.action_url || ''}')">
                    <div class="notification-item-icon">${n.icon}</div>
                    <div class="notification-item-content">
                        <div class="notification-item-title">${escapeHtml(n.title)}</div>
                        <div class="notification-item-message">${escapeHtml(n.message)}</div>
                        <div class="notification-item-time">${n.time_ago}</div>
                    </div>
                </div>
            `).join('');
        }
        
        function handleNotificationClick(e, notificationId, actionUrl) {
            e.stopPropagation();
            
            // Mark as read
            const markReadUrl = notificationsUrl.replace(/\/$/, '') + '/' + notificationId + '/read';
            fetch(markReadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                // Update UI
                const item = e.currentTarget;
                item.classList.remove('unread');
                updateBadgeCount(-1);
            }).catch(err => console.warn('Failed to mark notification read:', err));
            
            // Navigate if action URL exists
            if (actionUrl) {
                closeNotificationDropdown();
                loadContent(actionUrl);
            }
        }
        
        function markAllNotificationsRead(e) {
            e.stopPropagation();
            
            fetch(markAllReadUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                const badge = document.getElementById('notificationBadge');
                badge.textContent = '';
                badge.classList.remove('has-notifications');
            }).catch(err => console.warn('Failed to mark all read:', err));
        }
        
        function updateBadgeCount(delta) {
            const badge = document.getElementById('notificationBadge');
            let current = parseInt(badge.textContent) || 0;
            current = Math.max(0, current + delta);
            if (current > 0) {
                badge.textContent = current > 99 ? '99+' : current;
                badge.classList.add('has-notifications');
            } else {
                badge.textContent = '';
                badge.classList.remove('has-notifications');
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Poll for new notifications every 60 seconds
        setInterval(() => {
            fetch(notificationsUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                    badge.classList.add('has-notifications');
                } else {
                    badge.textContent = '';
                    badge.classList.remove('has-notifications');
                }
            })
            .catch(() => {});
        }, 60000);
        
        // Load initial badge count on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(fetchNotifications, 1000);
            updateBreadcrumbs(window.location.pathname);
        });
        
        // ========================================
        // Breadcrumb Navigation System
        // ========================================
        const breadcrumbMap = {
            // Admin pages
            'admin': 'Dashboard',
            'admin/dashboard': 'Dashboard',
            'admin/user-management': 'User Management',
            'admin/removal-requests': 'Removal Requests',
            'admin/courses': 'Courses',
            'admin/enrollments': 'Enrollments',
            'admin/theoretical': 'Theoretical Training',
            'admin/schedules': 'Schedules',
            'admin/bookings': 'Student Sessions',
            'admin/payments': 'Payments',
            'admin/reports': 'Reports & Analytics',
            'admin/settings': 'Settings',
            // Instructor pages
            'instructor': 'Dashboard',
            'instructor/dashboard': 'Dashboard',
            'instructor/my-schedule': 'My Schedule',
            'instructor/students': 'My Students',
            'instructor/sessions': 'Session Logging',
            'instructor/grades': 'Grades',
            'instructor/theoretical': 'Theoretical Training',
            // Student pages
            'student': 'Dashboard',
            'student/dashboard': 'Dashboard',
            'student/my-course': 'Enrolled Course',
            'student/progress': 'My Progress',
            'student/courses': 'Browse Courses',
            'student/schedule': 'My Schedule',
            'student/payments': 'My Payments',
            // Guest pages
            'guest': 'Dashboard',
            'guest/dashboard': 'Dashboard',
            'guest/courses': 'Browse Courses',
            'guest/enrollment-requests': 'My Enrollments',
        };
        
        function getDashboardUrl() {
            @if(Auth::guard('admin')->check())
                return @json($schoolRoute('admin.dashboard'));
            @elseif(Auth::guard('instructor')->check())
                return @json($schoolRoute('instructor.dashboard'));
            @elseif(Auth::guard('student')->check())
                @php $currentStudent = Auth::guard('student')->user(); @endphp
                @if($currentStudent && $currentStudent->role === 'guest')
                    return @json($schoolRoute('guest.dashboard'));
                @else
                    return @json($schoolRoute('student.dashboard'));
                @endif
            @else
                return '/';
            @endif
        }
        
        function getRoleLabel() {
            @if(Auth::guard('admin')->check())
                return 'Admin';
            @elseif(Auth::guard('instructor')->check())
                return 'Instructor';
            @elseif(Auth::guard('student')->check())
                @php $currentStudent = Auth::guard('student')->user(); @endphp
                @if($currentStudent && $currentStudent->role === 'guest')
                    return 'Guest';
                @else
                    return 'Student';
                @endif
            @else
                return '';
            @endif
        }
        
        function updateBreadcrumbs(url) {
            const container = document.getElementById('breadcrumbContent');
            if (!container) return;
            
            // Extract path segments after school slug
            const parts = url.replace(/^\//, '').split('/');
            // Pattern: school-slug/role/page or school-slug/role/page/subpage
            if (parts.length < 2) {
                container.innerHTML = '<span class="breadcrumb-current">Dashboard</span>';
                return;
            }
            
            const schoolSlug = parts[0];
            const rolePath = parts.slice(1).join('/');
            const dashUrl = getDashboardUrl();
            const roleLabel = getRoleLabel();
            
            // Check if this is a dashboard page
            const isDashboard = rolePath.match(/^(admin|instructor|student|guest)(\/dashboard)?$/);
            if (isDashboard) {
                container.innerHTML = '<span class="breadcrumb-current">' + roleLabel + ' Dashboard</span>';
                return;
            }
            
            // Build breadcrumb trail
            let html = '<a href="#" onclick="loadContent(\'' + dashUrl + '\'); return false;">Dashboard</a>';
            
            // Find the best matching breadcrumb
            let matched = false;
            const keys = Object.keys(breadcrumbMap).sort((a, b) => b.length - a.length);
            for (const key of keys) {
                if (rolePath === key || rolePath.startsWith(key + '/')) {
                    html += '<span class="breadcrumb-separator">›</span>';
                    
                    // If there's a deeper path, make the parent clickable
                    if (rolePath !== key && rolePath.startsWith(key + '/')) {
                        html += '<a href="#" onclick="loadContent(\'/' + schoolSlug + '/' + key + '\'); return false;">' + breadcrumbMap[key] + '</a>';
                        // Add the subpage
                        const subPath = rolePath.slice(key.length + 1);
                        const subLabel = subPath.split('/').map(s => s.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())).join(' > ');
                        html += '<span class="breadcrumb-separator">›</span>';
                        html += '<span class="breadcrumb-current">' + subLabel + '</span>';
                    } else {
                        html += '<span class="breadcrumb-current">' + breadcrumbMap[key] + '</span>';
                    }
                    matched = true;
                    break;
                }
            }
            
            if (!matched) {
                // Fallback: use the last segment
                const lastSegment = parts[parts.length - 1]
                    .replace(/-/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());
                html += '<span class="breadcrumb-separator">›</span>';
                html += '<span class="breadcrumb-current">' + lastSegment + '</span>';
            }
            
            container.innerHTML = html;
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
                
                // Update breadcrumbs
                updateBreadcrumbs(url);
                
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
            // Set initial state
            const currentUrl = window.location.pathname;
            history.replaceState({url: currentUrl}, '', currentUrl);
            updateActiveNavItem(currentUrl);
        });
        
        // Handle sidebar navigation clicks - Use capture phase to ensure it runs first
        document.addEventListener('click', function(e) {
            const navItem = e.target.closest('.nav-item');
            if (navItem && navItem.getAttribute('href') && !navItem.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                e.stopPropagation(); // Stop event from bubbling to prevent sidebar close
                const url = navItem.getAttribute('href');
                loadContent(url);
                
                // Close sidebar after navigation
                setTimeout(() => {
                    closeSidebar();
                }, 100);
            }
        }, true); // Use capture phase
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            // Don't close if clicking on a nav-item (handled above)
            if (e.target.closest('.nav-item')) {
                return;
            }
            
            // Close sidebar if clicking outside
            if (sidebarOpen && !e.target.closest('.sidebar') && !e.target.closest('.sidebar-toggle-tab')) {
                closeSidebar();
            }
            
            // Close profile dropdown
            if (profileDropdownOpen && !e.target.closest('.profile-dropdown')) {
                closeProfileDropdown();
            }
            
            // Close notification dropdown
            if (notificationDropdownOpen && !e.target.closest('.notification-icon')) {
                closeNotificationDropdown();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>