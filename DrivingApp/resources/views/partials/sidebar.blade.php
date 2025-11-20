<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h3>{{ $sidebarTitle ?? 'Menu' }}</h3>
    </div>
    
    <nav class="sidebar-nav">
        @if(Auth::guard('admin')->check())
            <a href="{{ $schoolRoute('admin.dashboard') }}" class="nav-item {{ request()->routeIs('schools.admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ $schoolRoute('admin.userManagement') }}" class="nav-item {{ request()->routeIs('schools.admin.userManagement') || request()->routeIs('schools.admin.students*') || request()->routeIs('schools.admin.instructors*') ? 'active' : '' }}">
                User Management
            </a>
            <a href="{{ $schoolRoute('admin.schedules') }}" class="nav-item {{ request()->routeIs('schools.admin.schedules*') ? 'active' : '' }}">
                Schedules
            </a>
            <a href="{{ $schoolRoute('admin.reports.students') }}" class="nav-item {{ request()->routeIs('schools.admin.reports*') ? 'active' : '' }}">
                Reports
            </a>
        @elseif(Auth::guard('instructor')->check())
            <a href="{{ $schoolRoute('instructor.dashboard') }}" class="nav-item {{ request()->routeIs('schools.instructor.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ $schoolRoute('instructor.timeslots.index') }}" class="nav-item {{ request()->routeIs('schools.instructor.timeslots*') ? 'active' : '' }}">
                Schedule
            </a>
            <a href="{{ $schoolRoute('instructor.schedule') }}" class="nav-item {{ request()->routeIs('schools.instructor.schedule') ? 'active' : '' }}">
                My Schedule
            </a>
            </a>
            <a href="#" class="nav-item">
                Notification
            </a>
        @elseif(Auth::guard('student')->check())
            <a href="{{ $schoolRoute('student.dashboard') }}" class="nav-item {{ request()->routeIs('schools.student.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
            <a href="{{ $schoolRoute('student.schedule') }}" class="nav-item {{ request()->routeIs('schools.student.schedule') ? 'active' : '' }}">
                My Schedule
            </a>
        @endif
    </nav>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 60px;
        right: -300px;
        width: 300px;
        height: calc(100% - 60px);
        background: #ffe073;
        z-index: 999;
        transition: right 0.3s ease;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    .sidebar.open {
        right: 0;
    }

    .sidebar-header {
        padding: 15px 20px;
        background-color: rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: center;
        align-items: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.2);
    }

    .sidebar-header h3 {
        color: #333;
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
    }

    .sidebar-nav {
        flex: 1;
        padding: 20px 0;
        overflow: hidden;
    }

    .nav-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 16px;
    }

    .nav-item:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .nav-item.active {
        background-color: rgba(0, 0, 0, 0.2);
        border-right: 3px solid #333;
    }

    .nav-icon {
        margin-right: 12px;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.2);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        color: white;
        background: rgba(220, 38, 38, 0.8);
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
        font-size: 16px;
    }

    .logout-btn:hover {
        background: rgba(220, 38, 38, 1);
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 280px;
            right: -280px;
        }
    }
</style>

<script>
    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const topbar = document.querySelector('.topbar');
        
        if (!sidebar.contains(event.target) && !topbar.contains(event.target)) {
            sidebar.classList.remove('open');
            document.body.style.overflow = 'auto';
            
            // Also remove active class from burger if it exists
            const burger = document.getElementById('topbarBurger');
            if (burger) {
                burger.classList.remove('active');
            }
        }
    });

    // Close sidebar on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('sidebar').classList.remove('open');
            document.body.style.overflow = 'auto';
            
            // Also remove active class from burger if it exists
            const burger = document.getElementById('topbarBurger');
            if (burger) {
                burger.classList.remove('active');
            }
        }
    });
</script>