<!-- Top Navigation Bar -->
<div class="topbar">
    <div class="topbar-left">
        <div class="school-logo">
            <!-- Placeholder logo with CSS -->
            <div class="logo-placeholder">
                🚗
            </div>
            <span class="school-name">{{ $schoolName ?? 'Driving School' }}</span>
        </div>
    </div>
    
    <div class="topbar-right">
        <button onclick="toggleSidebar()" class="topbar-burger" id="topbarBurger">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <button class="topbar-notification" onclick="toggleNotifications()">
            <span class="icon">Notifications</span>
            <span class="notification-badge">3</span>
        </button>
        
        <div class="topbar-profile" onclick="toggleProfileMenu()">
            <div class="profile-avatar">
                @if(Auth::guard('admin')->check())
                    {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                @elseif(Auth::guard('instructor')->check())
                    {{ strtoupper(substr(Auth::guard('instructor')->user()->name, 0, 1)) }}
                @elseif(Auth::guard('student')->check())
                    {{ strtoupper(substr(Auth::guard('student')->user()->name, 0, 1)) }}
                @endif
            </div>
            <span class="profile-name">
                @if(Auth::guard('admin')->check())
                    {{ Auth::guard('admin')->user()->name }}
                @elseif(Auth::guard('instructor')->check())
                    {{ Auth::guard('instructor')->user()->name }}
                @elseif(Auth::guard('student')->check())
                    {{ Auth::guard('student')->user()->name }}
                @endif
            </span>
            <span class="dropdown-arrow">▼</span>
        </div>
        
        <!-- Profile Dropdown Menu -->
        <div id="profileDropdown" class="profile-dropdown">
            @if(Auth::guard('admin')->check())
                <a href="{{ $schoolRoute('admin.profile') }}" class="dropdown-item">
                    Profile
                </a>
                {{-- <a href="{{ $schoolRoute('admin.settings') }}" class="dropdown-item">
                    Settings
                </a> --}}
            @elseif(Auth::guard('instructor')->check())
                <a href="{{ $schoolRoute('instructor.profile') }}" class="dropdown-item">
                    Profile
                </a>
                {{-- <a href="{{ $schoolRoute('instructor.settings') }}" class="dropdown-item">
                    Settings
                </a> --}}
            @elseif(Auth::guard('student')->check())
                <a href="{{ $schoolRoute('student.profile') }}" class="dropdown-item">
                    Profile
                </a>
                {{-- <a href="{{ $schoolRoute('student.settings') }}" class="dropdown-item">
                    Settings
                </a> --}}
            @endif
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ $schoolRoute('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item logout-item">
                    Logout
                </button>
            </form>
        </div>
        
        <!-- Notification Dropdown -->
        <div id="notificationDropdown" class="notification-dropdown">
            <div class="notification-header">
                <h4>Notifications</h4>
                <span class="mark-all-read">Mark all as read</span>
            </div>
            <div class="notification-list">
                <div class="notification-item unread">
                    <div class="notification-content">
                        <strong>New Schedule Available</strong>
                        <p>Your driving lesson has been scheduled for tomorrow at 2:00 PM</p>
                        <span class="notification-time">2 hours ago</span>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="notification-content">
                        <strong>Payment Reminder</strong>
                        <p>Your next payment is due in 3 days</p>
                        <span class="notification-time">1 day ago</span>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="notification-content">
                        <strong>Welcome!</strong>
                        <p>Welcome to the driving school portal</p>
                        <span class="notification-time">3 days ago</span>
                    </div>
                </div>
            </div>
            <div class="notification-footer">
                <a href="#" class="view-all-notifications">View All Notifications</a>
            </div>
        </div>
    </div>
</div>

<style>
    .topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .topbar-left {
        display: flex;
        align-items: center;
    }

    .school-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .school-name {
        color: white;
        font-size: 20px;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
    }

    .topbar-burger {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 8px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    .topbar-burger:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .topbar-burger span {
        width: 20px;
        height: 2px;
        background: white;
        border-radius: 1px;
        transition: all 0.3s ease;
    }

    .topbar-burger.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .topbar-burger.active span:nth-child(2) {
        opacity: 0;
    }

    .topbar-burger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -6px);
    }

    .topbar-notification {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        position: relative;
        transition: background-color 0.3s ease;
    }

    .topbar-notification:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .notification-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #ff4757;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 10px;
        min-width: 16px;
        text-align: center;
    }

    .topbar-profile {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 25px;
        transition: background-color 0.3s ease;
        color: white;
    }

    .topbar-profile:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .profile-avatar {
        width: 32px;
        height: 32px;
        background: #ffe073;
        color: #333;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }

    .profile-name {
        font-size: 14px;
        font-weight: 500;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dropdown-arrow {
        font-size: 10px;
        transition: transform 0.3s ease;
    }

    .topbar-profile.active .dropdown-arrow {
        transform: rotate(180deg);
    }

    .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1001;
        margin-top: 8px;
    }

    .profile-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-item {
        display: block;
        padding: 12px 16px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 14px;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
    }

    .dropdown-item:first-child {
        border-radius: 8px 8px 0 0;
    }

    .dropdown-item:last-child {
        border-radius: 0 0 8px 8px;
    }

    .dropdown-divider {
        height: 1px;
        background: #e9ecef;
        margin: 4px 0;
    }

    .logout-item {
        color: #dc3545;
        font-weight: 500;
    }

    .logout-item:hover {
        background: #f8f9fa;
    }

    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 80px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        width: 350px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1001;
        margin-top: 8px;
    }

    .notification-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .notification-header {
        padding: 16px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .notification-header h4 {
        margin: 0;
        color: #333;
        font-size: 16px;
    }

    .mark-all-read {
        color: #667eea;
        font-size: 12px;
        cursor: pointer;
        text-decoration: underline;
    }

    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item.unread {
        background: #f0f8ff;
        border-left: 3px solid #667eea;
    }

    .notification-content strong {
        display: block;
        color: #333;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .notification-content p {
        margin: 0;
        color: #666;
        font-size: 13px;
        line-height: 1.4;
    }

    .notification-time {
        color: #999;
        font-size: 11px;
        margin-top: 4px;
        display: block;
    }

    .notification-footer {
        padding: 12px 16px;
        text-align: center;
        border-top: 1px solid #e9ecef;
    }

    .view-all-notifications {
        color: #667eea;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .view-all-notifications:hover {
        text-decoration: underline;
    }

    /* Adjust main content to account for topbar */
    body {
        padding-top: 60px;
    }

    .container {
        padding-top: 20px;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .topbar {
            padding: 0 15px;
        }

        .school-name {
            font-size: 16px;
        }

        .profile-name {
            display: none;
        }

        .notification-dropdown {
            width: 280px;
            right: 0;
        }
    }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const burger = document.getElementById('topbarBurger');
        
        sidebar.classList.toggle('open');
        burger.classList.toggle('active');
        
        if (sidebar.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }

    function toggleProfileMenu() {
        const dropdown = document.getElementById('profileDropdown');
        const profile = document.querySelector('.topbar-profile');
        
        dropdown.classList.toggle('show');
        profile.classList.toggle('active');
        
        // Close notification dropdown if open
        document.getElementById('notificationDropdown').classList.remove('show');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        
        dropdown.classList.toggle('show');
        
        // Close profile dropdown if open
        document.getElementById('profileDropdown').classList.remove('show');
        document.querySelector('.topbar-profile').classList.remove('active');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const profileDropdown = document.getElementById('profileDropdown');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const profileButton = document.querySelector('.topbar-profile');
        const notificationButton = document.querySelector('.topbar-notification');
        
        if (!profileButton.contains(event.target)) {
            profileDropdown.classList.remove('show');
            profileButton.classList.remove('active');
        }
        
        if (!notificationButton.contains(event.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
</script>