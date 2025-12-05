<?php
    $school = $school ?? $currentSchool ?? null;
    $settings = $school?->schoolSetting;
    $primaryColor = $settings?->primary_color ?? '#667eea';
    $secondaryColor = $settings?->secondary_color ?? '#764ba2';
    $accentColor = $settings?->accent_color ?? '#1e40af';
    $useGradient = $settings?->use_gradient_header ?? true;
    $headerTextColor = $settings?->header_text_color ?? '#ffffff';
?>

<style>
    /* ============================================
       SHARED ADMIN STYLES
       Consistent styling across all admin pages
       ============================================ */

    /* Container */
    .admin-container {
        padding: 20px;
        margin: 20px auto;
        max-width: 1600px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid <?php echo e($primaryColor); ?>;
    }

    .page-header-left {
        flex: 1;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    /* Predefined stat card colors */
    .stat-card.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .stat-card.danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .stat-card.info {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .stat-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-detail {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    /* Content Cards/Sections */
    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .content-card-header {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: #ffffff;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 1rem;
    }

    .content-card-body {
        padding: 20px;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: white;
    }

    .btn-primary:hover {
        opacity: 0.9;
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-success:hover {
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-warning:hover {
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-danger:hover {
        color: white;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
        color: white;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid <?php echo e($primaryColor); ?>;
        color: <?php echo e($primaryColor); ?>;
    }

    .btn-outline:hover {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: white;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .btn-lg {
        padding: 14px 28px;
        font-size: 1rem;
    }

    /* Tables */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table th {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: #ffffff;
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .admin-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.9rem;
        color: #374151;
    }

    .admin-table tbody tr:hover {
        background: #f9fafb;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    /* Alerts */
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger, .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert .close-btn {
        margin-left: auto;
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.6;
        padding: 0;
    }

    .alert .close-btn:hover {
        opacity: 1;
    }

    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: #374151;
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: <?php echo e($primaryColor); ?>;
        box-shadow: 0 0 0 3px <?php echo e($primaryColor); ?>20;
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    /* Filter Buttons */
    .filter-group {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 0.9rem;
        color: #374151;
    }

    .filter-btn:hover {
        border-color: <?php echo e($primaryColor); ?>;
        color: <?php echo e($primaryColor); ?>;
    }

    .filter-btn.active {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: white;
        border-color: transparent;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .empty-state-text {
        font-size: 0.9rem;
        color: #9ca3af;
    }

    /* Modals */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        transform: scale(0.9);
        transition: transform 0.3s;
    }

    .modal-overlay.active .modal-content {
        transform: scale(1);
    }

    .modal-header {
        <?php if($useGradient): ?>
            background: linear-gradient(135deg, <?php echo e($primaryColor); ?> 0%, <?php echo e($secondaryColor); ?> 100%);
        <?php else: ?>
            background: <?php echo e($primaryColor); ?>;
        <?php endif; ?>
        color: #ffffff;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.8;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Action Buttons Group */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 6px 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.8rem;
    }

    .action-btn-view {
        background: #dbeafe;
        color: #1e40af;
    }

    .action-btn-view:hover {
        background: #bfdbfe;
    }

    .action-btn-edit {
        background: #fef3c7;
        color: #92400e;
    }

    .action-btn-edit:hover {
        background: #fde68a;
    }

    .action-btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-btn-delete:hover {
        background: #fecaca;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-group {
            flex-direction: column;
        }

        .filter-btn {
            width: 100%;
        }
    }

    /* ============================================
       TOAST NOTIFICATIONS
       Beautiful feedback for user actions
       ============================================ */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    .toast {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        background: white;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        min-width: 320px;
        max-width: 420px;
        pointer-events: auto;
        transform: translateX(120%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }

    .toast.hide {
        transform: translateX(120%);
        opacity: 0;
    }

    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.25rem;
    }

    .toast-content {
        flex: 1;
    }

    .toast-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
        color: #1f2937;
    }

    .toast-message {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .toast-close {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        color: #9ca3af;
        font-size: 1.25rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .toast-close:hover {
        color: #374151;
    }

    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        border-radius: 0 0 12px 12px;
        transition: width linear;
    }

    /* Toast Types */
    .toast.success .toast-icon {
        background: #d1fae5;
        color: #059669;
    }
    .toast.success .toast-progress {
        background: linear-gradient(90deg, #10b981, #059669);
    }

    .toast.error .toast-icon {
        background: #fee2e2;
        color: #dc2626;
    }
    .toast.error .toast-progress {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    .toast.warning .toast-icon {
        background: #fef3c7;
        color: #d97706;
    }
    .toast.warning .toast-progress {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .toast.info .toast-icon {
        background: #dbeafe;
        color: #2563eb;
    }
    .toast.info .toast-progress {
        background: linear-gradient(90deg, #3b82f6, #2563eb);
    }

    /* Session Flash Messages */
    .flash-message {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .flash-message.success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-left: 4px solid #10b981;
    }

    .flash-message.error {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-left: 4px solid #ef4444;
    }

    .flash-message.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-left: 4px solid #f59e0b;
    }

    .flash-message.info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
    }

    .flash-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .flash-message.success .flash-icon {
        background: #10b981;
        color: white;
    }

    .flash-message.error .flash-icon {
        background: #ef4444;
        color: white;
    }

    .flash-message.warning .flash-icon {
        background: #f59e0b;
        color: white;
    }

    .flash-message.info .flash-icon {
        background: #3b82f6;
        color: white;
    }

    .flash-content {
        flex: 1;
    }

    .flash-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
    }

    .flash-message.success .flash-title { color: #065f46; }
    .flash-message.error .flash-title { color: #991b1b; }
    .flash-message.warning .flash-title { color: #92400e; }
    .flash-message.info .flash-title { color: #1e40af; }

    .flash-text {
        font-size: 0.85rem;
    }

    .flash-message.success .flash-text { color: #047857; }
    .flash-message.error .flash-text { color: #b91c1c; }
    .flash-message.warning .flash-text { color: #b45309; }
    .flash-message.info .flash-text { color: #1d4ed8; }

    .flash-close {
        background: none;
        border: none;
        padding: 4px;
        cursor: pointer;
        font-size: 1.25rem;
        line-height: 1;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

    .flash-close:hover {
        opacity: 1;
    }

    .flash-message.success .flash-close { color: #065f46; }
    .flash-message.error .flash-close { color: #991b1b; }
    .flash-message.warning .flash-close { color: #92400e; }
    .flash-message.info .flash-close { color: #1e40af; }

    /* Confirmation Modal */
    .confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }

    .confirm-modal.active {
        opacity: 1;
        visibility: visible;
    }

    .confirm-dialog {
        background: white;
        border-radius: 16px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        transform: scale(0.9);
        transition: transform 0.3s;
        overflow: hidden;
    }

    .confirm-modal.active .confirm-dialog {
        transform: scale(1);
    }

    .confirm-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        margin: 30px auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .confirm-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .confirm-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .confirm-icon.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .confirm-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
        padding: 0 30px;
    }

    .confirm-message {
        color: #6b7280;
        font-size: 0.95rem;
        padding: 0 30px;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .confirm-actions {
        display: flex;
        border-top: 1px solid #e5e7eb;
    }

    .confirm-btn {
        flex: 1;
        padding: 16px;
        border: none;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .confirm-btn-cancel {
        background: #f9fafb;
        color: #6b7280;
    }

    .confirm-btn-cancel:hover {
        background: #f3f4f6;
    }

    .confirm-btn-confirm {
        color: white;
    }

    .confirm-btn-confirm.warning {
        background: #f59e0b;
    }

    .confirm-btn-confirm.warning:hover {
        background: #d97706;
    }

    .confirm-btn-confirm.danger {
        background: #ef4444;
    }

    .confirm-btn-confirm.danger:hover {
        background: #dc2626;
    }

    .confirm-btn-confirm.success {
        background: #10b981;
    }

    .confirm-btn-confirm.success:hover {
        background: #059669;
    }

    @media (max-width: 480px) {
        .toast-container {
            left: 10px;
            right: 10px;
            top: 10px;
        }
        
        .toast {
            min-width: auto;
            max-width: none;
        }
    }
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Confirmation Modal -->
<div class="confirm-modal" id="confirmModal">
    <div class="confirm-dialog">
        <div class="confirm-icon warning" id="confirmIcon">
            <span id="confirmIconText">!</span>
        </div>
        <h3 class="confirm-title" id="confirmTitle">Are you sure?</h3>
        <p class="confirm-message" id="confirmMessage">This action cannot be undone.</p>
        <div class="confirm-actions">
            <button class="confirm-btn confirm-btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button class="confirm-btn confirm-btn-confirm warning" id="confirmBtn" onclick="executeConfirm()">Confirm</button>
        </div>
    </div>
</div>

<script>
// Toast Notification System
const Toast = {
    container: null,
    
    init() {
        this.container = document.getElementById('toastContainer');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            this.container.id = 'toastContainer';
            document.body.appendChild(this.container);
        }
    },
    
    show(type, title, message, duration = 4000) {
        if (!this.container) this.init();
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '!',
            info: 'i'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || 'i'}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            <div class="toast-progress" style="width: 100%;"></div>
        `;
        
        this.container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Progress bar animation
        const progress = toast.querySelector('.toast-progress');
        progress.style.transition = `width ${duration}ms linear`;
        requestAnimationFrame(() => {
            progress.style.width = '0%';
        });
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 400);
        }, duration);
        
        return toast;
    },
    
    success(message, title = 'Success!') {
        return this.show('success', title, message);
    },
    
    error(message, title = 'Error!') {
        return this.show('error', title, message);
    },
    
    warning(message, title = 'Warning') {
        return this.show('warning', title, message);
    },
    
    info(message, title = 'Info') {
        return this.show('info', title, message);
    }
};

// Initialize Toast on page load
document.addEventListener('DOMContentLoaded', () => Toast.init());

// Confirmation Modal System
let confirmCallback = null;

function showConfirm(options) {
    const modal = document.getElementById('confirmModal');
    const icon = document.getElementById('confirmIcon');
    const iconText = document.getElementById('confirmIconText');
    const title = document.getElementById('confirmTitle');
    const message = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmBtn');
    
    // Set content
    title.textContent = options.title || 'Are you sure?';
    message.textContent = options.message || 'This action cannot be undone.';
    btn.textContent = options.confirmText || 'Confirm';
    
    // Set type styling
    const type = options.type || 'warning';
    icon.className = `confirm-icon ${type}`;
    btn.className = `confirm-btn confirm-btn-confirm ${type}`;
    
    const icons = {
        warning: '!',
        danger: '✕',
        info: 'i',
        success: '✓'
    };
    iconText.textContent = icons[type] || '!';
    
    // Store callback
    confirmCallback = options.onConfirm;
    
    // Show modal
    modal.classList.add('active');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
    confirmCallback = null;
}

function executeConfirm() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
}

// Close modal on backdrop click
document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});
</script>
<?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/partials/admin-styles.blade.php ENDPATH**/ ?>