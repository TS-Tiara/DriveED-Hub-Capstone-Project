<div id="toast-container" class="toast-container"></div>

<style>
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001; /* Higher than sidebar and modals */
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 400px;
        width: calc(100% - 40px);
        pointer-events: none;
    }

    .toast {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-left: 5px solid #ddd;
        animation: toast-slide-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        pointer-events: auto;
        overflow: hidden;
        position: relative;
    }

    .toast-success { border-left-color: #10b981; }
    .toast-error { border-left-color: #ef4444; }
    .toast-warning { border-left-color: #f59e0b; }
    .toast-info { border-left-color: #3b82f6; }

    .toast-icon-wrap {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .toast-success .toast-icon-wrap { background: #d1fae5; color: #065f46; }
    .toast-error .toast-icon-wrap { background: #fee2e2; color: #991b1b; }
    .toast-warning .toast-icon-wrap { background: #fef3c7; color: #92400e; }
    .toast-info .toast-icon-wrap { background: #dbeafe; color: #1e40af; }

    .toast-content {
        flex: 1;
        font-size: 14px;
        line-height: 1.5;
        color: #1f2937;
        font-weight: 600;
        padding-right: 8px;
    }

    .toast-close {
        flex-shrink: 0;
        background: #f3f4f6;
        border: none;
        color: #6b7280;
        cursor: pointer;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s;
        line-height: 1;
    }

    .toast-close:hover {
        background: #e5e7eb;
        color: #111827;
        transform: rotate(90deg);
    }

    @keyframes toast-slide-in {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .toast-fade-out {
        animation: toast-fade-out 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes toast-fade-out {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }

    /* Mobile adjustments */
    @media (max-width: 480px) {
        .toast-container {
            top: 15px;
            right: 15px;
            max-width: 100%;
        }
        .toast {
            padding: 12px;
        }
        .toast-content {
            font-size: 13px;
        }
    }
</style>

<script>
    (function() {
        // Universal notification function
        window.showToast = function(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            // Icons mapping
            const icons = {
                success: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>',
                warning: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };

            const iconHtml = icons[type] || icons.info;

            toast.innerHTML = `
                <div class="toast-icon-wrap">${iconHtml}</div>
                <div class="toast-content">${message}</div>
                <button class="toast-close" title="Dismiss" onclick="this.parentElement.remove()">&times;</button>
            `;

            // Auto-remove on click
            toast.addEventListener('click', function(e) {
                if (!e.target.closest('.toast-close')) {
                    this.classList.add('toast-fade-out');
                    setTimeout(() => this.remove(), 400);
                }
            });

            container.appendChild(toast);

            // Auto-remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.classList.add('toast-fade-out');
                        setTimeout(() => {
                            if (toast.parentNode) toast.remove();
                        }, 400);
                    }
                }, duration);
            }
        };

        // Compatibility alias
        window.showNotification = function(message, type, duration) {
            window.showToast(message, type, duration);
        };

        // Initialize from session on load
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
                showToast("{{ session('error') }}", 'error', 7000);
            @endif

            @if(session('status'))
                showToast("{{ session('status') }}", 'info');
            @endif

            @if(session('info'))
                showToast("{{ session('info') }}", 'info');
            @endif

            @if(session('warning'))
                showToast("{{ session('warning') }}", 'warning', 7000);
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast("{{ $error }}", 'error', 8000);
                @endforeach
            @endif
        });
    })();
</script>
