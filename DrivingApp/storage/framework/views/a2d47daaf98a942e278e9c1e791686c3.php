

<?php $__env->startSection('title', 'School Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $school = $school ?? $currentSchool ?? null;
    $schoolName = $school->name ?? 'Driving School';
?>

<style>
    .settings-container {
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
        border-bottom: 2px solid #667eea;
    }
    
    .page-title {
        font-size: 2rem;
        color: #333;
        margin: 0 0 10px 0;
    }
    
    .page-subtitle {
        color: #666;
        font-size: 0.95rem;
    }
    
    /* Alert Styles */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }
    
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    
    /* Settings Card */
    .settings-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .settings-section {
        padding: 25px;
    }
    
    .section-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 5px 0;
    }
    
    .section-description {
        color: #6b7280;
        font-size: 0.85rem;
        margin: 0;
    }
    
    .subsection-title {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin: 20px 0 15px 0;
        padding: 8px 12px;
        background: #f3f4f6;
        border-left: 3px solid #667eea;
        border-radius: 4px;
    }
    
    /* Collapsible Section */
    .collapsible-section {
        margin-bottom: 15px;
    }
    
    .section-toggle {
        width: 100%;
        padding: 12px 15px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        color: #374151;
        transition: all 0.3s ease;
    }
    
    .section-toggle:hover {
        background: #f3f4f6;
        border-color: #667eea;
    }
    
    .section-toggle .icon {
        transition: transform 0.3s ease;
    }
    
    .section-toggle.active .icon {
        transform: rotate(180deg);
    }
    
    .section-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        padding: 0 15px;
    }
    
    .section-content.active {
        max-height: 2000px;
        padding: 20px 15px;
    }
    
    /* Color Picker Styles */
    .color-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .color-picker-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .color-input-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .color-input-wrapper:hover {
        border-color: #667eea;
        box-shadow: 0 2px 6px rgba(102, 126, 234, 0.1);
    }
    
    .color-preview-container {
        position: relative;
        flex-shrink: 0;
    }
    
    .color-preview {
        width: 45px;
        height: 45px;
        border-radius: 6px;
        border: 2px solid #d1d5db;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .color-preview::before {
        content: '🎨';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .color-preview:hover {
        transform: scale(1.08);
        box-shadow: 0 3px 10px rgba(0,0,0,0.25);
        border-color: #667eea;
    }
    
    .color-preview:hover::before {
        opacity: 0.7;
    }
    
    .color-picker-label {
        display: none;
    }
    
    .color-input {
        flex: 1;
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        background: #f9fafb;
    }
    
    .color-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
    }
    
    .color-input.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .color-input-wrapper.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .color-input-wrapper.is-invalid .color-preview {
        border-color: #ef4444;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #2563eb;
    }
    
    .checkbox-group label {
        margin: 0;
        cursor: pointer;
    }
    
    .divider {
        height: 2px;
        background: linear-gradient(to right, transparent, #e5e7eb, transparent);
        margin: 40px 0;
    }
    
    /* Form Styles */
    .settings-form {
        max-width: 800px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .input-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-control {
        padding: 12px 15px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
        width: 150px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-control.is-invalid {
        border-color: #ef4444;
    }
    
    .input-suffix {
        color: #6b7280;
        font-weight: 500;
    }
    
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 5px;
        display: block;
    }
    
    .form-text {
        display: block;
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 8px;
        line-height: 1.5;
    }
    
    /* Info Box */
    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 20px;
        margin: 25px 0;
        display: flex;
        gap: 15px;
    }
    
    .info-icon {
        width: 24px;
        height: 24px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    
    .info-content {
        flex: 1;
    }
    
    .info-content strong {
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
    }
    
    .info-content ul {
        margin: 0;
        padding-left: 20px;
        color: #1e40af;
    }
    
    .info-content li {
        margin-bottom: 5px;
    }
    
    /* Form Actions */
    .form-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    }

    /* Range Slider */
    .form-range {
        width: 200px;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        outline: none;
        -webkit-appearance: none;
        margin-right: 15px;
    }

    .form-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #667eea;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .form-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #667eea;
        border-radius: 50%;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .form-range:hover::-webkit-slider-thumb {
        background: #5568d3;
    }

    .form-range:hover::-moz-range-thumb {
        background: #5568d3;
    }
</style>

<div class="settings-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">School Settings</h1>
            <p class="page-subtitle">Configure and customize settings for <?php echo e($schoolName); ?></p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>⚠️ There were some problems with your input:</strong>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="settings-card">
        <div class="settings-section">
            <div class="section-header">
                <h2 class="section-title">Appearance & Branding</h2>
                <p class="section-description">Customize the color scheme and branding for your school's interface</p>
            </div>

            <form method="POST" action="<?php echo e(route('schools.admin.settings.update', ['school' => $school->slug])); ?>" class="settings-form" id="settingsForm">
                <?php echo csrf_field(); ?>
                
                <div class="checkbox-group">
                    <input 
                        type="checkbox" 
                        id="use_gradient_header" 
                        name="use_gradient_header" 
                        value="1"
                        <?php echo e(old('use_gradient_header', $school->schoolSetting->use_gradient_header ?? true) ? 'checked' : ''); ?>

                    >
                    <label for="use_gradient_header" class="form-label">
                        Use Gradient Header (Primary → Secondary colors)
                    </label>
                </div>

                <h3 class="subsection-title">🎨 Brand Colors</h3>
                <div class="color-grid">
                    <div class="color-picker-group">
                        <label for="primary_color" class="form-label">Primary Color</label>
                        <div class="color-input-wrapper <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <div class="color-preview-container">
                                <input 
                                    type="color" 
                                    id="primary_color_picker" 
                                    value="<?php echo e(old('primary_color', $school->schoolSetting->primary_color ?? '#2563eb')); ?>"
                                    class="color-preview"
                                    onchange="document.getElementById('primary_color').value = this.value"
                                    title="Click to pick a color"
                                >
                                <span class="color-picker-label">Click me</span>
                            </div>
                            <input 
                                type="text" 
                                id="primary_color" 
                                name="primary_color" 
                                class="color-input <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('primary_color', $school->schoolSetting->primary_color ?? '#2563eb')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('primary_color_picker').value = this.value"
                                placeholder="#2563eb"
                            >
                        </div>
                        <?php $__errorArgs = ['primary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="form-text">Main brand color</small>
                    </div>

                    <div class="color-picker-group">
                        <label for="secondary_color" class="form-label">Secondary Color</label>
                        <div class="color-input-wrapper <?php $__errorArgs = ['secondary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <input 
                                type="color" 
                                id="secondary_color_picker" 
                                value="<?php echo e(old('secondary_color', $school->schoolSetting->secondary_color ?? '#fbbf24')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('secondary_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="secondary_color" 
                                name="secondary_color" 
                                class="color-input <?php $__errorArgs = ['secondary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('secondary_color', $school->schoolSetting->secondary_color ?? '#fbbf24')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('secondary_color_picker').value = this.value"
                            >
                        </div>
                        <?php $__errorArgs = ['secondary_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="form-text">Accent color (header end, highlights)</small>
                    </div>

                    <div class="color-picker-group">
                        <label for="accent_color" class="form-label">Accent Color</label>
                        <div class="color-input-wrapper <?php $__errorArgs = ['accent_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <input 
                                type="color" 
                                id="accent_color_picker" 
                                value="<?php echo e(old('accent_color', $school->schoolSetting->accent_color ?? '#1e40af')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('accent_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="accent_color" 
                                name="accent_color" 
                                class="color-input <?php $__errorArgs = ['accent_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('accent_color', $school->schoolSetting->accent_color ?? '#1e40af')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('accent_color_picker').value = this.value"
                            >
                        </div>
                        <?php $__errorArgs = ['accent_color'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="form-text">Links and active states</small>
                    </div>

                    <div class="color-picker-group">
                        <label for="sidebar_bg_color" class="form-label">Sidebar Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="sidebar_bg_color_picker" 
                                value="<?php echo e(old('sidebar_bg_color', $school->schoolSetting->sidebar_bg_color ?? '#ffffff')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('sidebar_bg_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="sidebar_bg_color" 
                                name="sidebar_bg_color" 
                                class="color-input"
                                value="<?php echo e(old('sidebar_bg_color', $school->schoolSetting->sidebar_bg_color ?? '#ffffff')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('sidebar_bg_color_picker').value = this.value"
                            >
                        </div>
                        <small class="form-text">Navigation sidebar background</small>
                    </div>

                    <div class="color-picker-group">
                        <label for="sidebar_text_color" class="form-label">Sidebar Text Color</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="sidebar_text_color_picker" 
                                value="<?php echo e(old('sidebar_text_color', $school->schoolSetting->sidebar_text_color ?? '#333333')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('sidebar_text_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="sidebar_text_color" 
                                name="sidebar_text_color" 
                                class="color-input"
                                value="<?php echo e(old('sidebar_text_color', $school->schoolSetting->sidebar_text_color ?? '#333333')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('sidebar_text_color_picker').value = this.value"
                            >
                        </div>
                        <small class="form-text">Navigation menu text color</small>
                    </div>

                    <div class="color-picker-group">
                        <label for="sidebar_hover_color" class="form-label">Sidebar Hover Color</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="sidebar_hover_color_picker" 
                                value="<?php echo e(old('sidebar_hover_color', $school->schoolSetting->sidebar_hover_color ?? '#f5f5f5')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('sidebar_hover_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="sidebar_hover_color" 
                                name="sidebar_hover_color" 
                                class="color-input"
                                value="<?php echo e(old('sidebar_hover_color', $school->schoolSetting->sidebar_hover_color ?? '#f5f5f5')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('sidebar_hover_color_picker').value = this.value"
                            >
                        </div>
                        <small class="form-text">Menu item hover background</small>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Button Colors - Collapsible -->
                <div class="collapsible-section">
                    <button type="button" class="section-toggle" data-section="button-colors-section" onclick="toggleSection('button-colors-section')">
                        <span>🔘 Button Colors (Primary, Success, Danger)</span>
                        <span class="icon">▼</span>
                    </button>
                    <div class="section-content" id="button-colors-section">
                        <div class="color-grid">
                    <div class="color-picker-group">
                        <label for="button_primary_bg" class="form-label">Primary Button Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_primary_bg_picker" 
                                value="<?php echo e(old('button_primary_bg', $school->schoolSetting->button_primary_bg ?? '#667eea')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_primary_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_primary_bg" 
                                name="button_primary_bg" 
                                class="color-input"
                                value="<?php echo e(old('button_primary_bg', $school->schoolSetting->button_primary_bg ?? '#667eea')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_primary_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="button_primary_text" class="form-label">Primary Button Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_primary_text_picker" 
                                value="<?php echo e(old('button_primary_text', $school->schoolSetting->button_primary_text ?? '#ffffff')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_primary_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_primary_text" 
                                name="button_primary_text" 
                                class="color-input"
                                value="<?php echo e(old('button_primary_text', $school->schoolSetting->button_primary_text ?? '#ffffff')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_primary_text_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="button_success_bg" class="form-label">Success Button Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_success_bg_picker" 
                                value="<?php echo e(old('button_success_bg', $school->schoolSetting->button_success_bg ?? '#28a745')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_success_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_success_bg" 
                                name="button_success_bg" 
                                class="color-input"
                                value="<?php echo e(old('button_success_bg', $school->schoolSetting->button_success_bg ?? '#28a745')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_success_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="button_success_text" class="form-label">Success Button Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_success_text_picker" 
                                value="<?php echo e(old('button_success_text', $school->schoolSetting->button_success_text ?? '#ffffff')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_success_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_success_text" 
                                name="button_success_text" 
                                class="color-input"
                                value="<?php echo e(old('button_success_text', $school->schoolSetting->button_success_text ?? '#ffffff')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_success_text_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="button_danger_bg" class="form-label">Danger Button Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_danger_bg_picker" 
                                value="<?php echo e(old('button_danger_bg', $school->schoolSetting->button_danger_bg ?? '#dc3545')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_danger_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_danger_bg" 
                                name="button_danger_bg" 
                                class="color-input"
                                value="<?php echo e(old('button_danger_bg', $school->schoolSetting->button_danger_bg ?? '#dc3545')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_danger_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="button_danger_text" class="form-label">Danger Button Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="button_danger_text_picker" 
                                value="<?php echo e(old('button_danger_text', $school->schoolSetting->button_danger_text ?? '#ffffff')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('button_danger_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="button_danger_text" 
                                name="button_danger_text" 
                                class="color-input"
                                value="<?php echo e(old('button_danger_text', $school->schoolSetting->button_danger_text ?? '#ffffff')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('button_danger_text_picker').value = this.value"
                            >
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Modal & Card Styles - Collapsible -->
                <div class="collapsible-section">
                    <button type="button" class="section-toggle" data-section="modal-cards-section" onclick="toggleSection('modal-cards-section')">
                        <span>📦 Modal & Card Styles (Headers, Borders, Backgrounds)</span>
                        <span class="icon">▼</span>
                    </button>
                    <div class="section-content" id="modal-cards-section">
                        <div class="color-grid">
                    <div class="color-picker-group">
                        <label for="modal_header_bg" class="form-label">Modal Header Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="modal_header_bg_picker" 
                                value="<?php echo e(old('modal_header_bg', $school->schoolSetting->modal_header_bg ?? '#667eea')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('modal_header_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="modal_header_bg" 
                                name="modal_header_bg" 
                                class="color-input"
                                value="<?php echo e(old('modal_header_bg', $school->schoolSetting->modal_header_bg ?? '#667eea')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('modal_header_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="modal_header_text" class="form-label">Modal Header Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="modal_header_text_picker" 
                                value="<?php echo e(old('modal_header_text', $school->schoolSetting->modal_header_text ?? '#ffffff')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('modal_header_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="modal_header_text" 
                                name="modal_header_text" 
                                class="color-input"
                                value="<?php echo e(old('modal_header_text', $school->schoolSetting->modal_header_text ?? '#ffffff')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('modal_header_text_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="modal_border_color" class="form-label">Modal Border Color</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="modal_border_color_picker" 
                                value="<?php echo e(old('modal_border_color', $school->schoolSetting->modal_border_color ?? '#667eea')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('modal_border_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="modal_border_color" 
                                name="modal_border_color" 
                                class="color-input"
                                value="<?php echo e(old('modal_border_color', $school->schoolSetting->modal_border_color ?? '#667eea')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('modal_border_color_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="card_border_color" class="form-label">Card Border Color</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="card_border_color_picker" 
                                value="<?php echo e(old('card_border_color', $school->schoolSetting->card_border_color ?? '#e5e7eb')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('card_border_color').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="card_border_color" 
                                name="card_border_color" 
                                class="color-input"
                                value="<?php echo e(old('card_border_color', $school->schoolSetting->card_border_color ?? '#e5e7eb')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('card_border_color_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="card_header_bg" class="form-label">Card Header Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="card_header_bg_picker" 
                                value="<?php echo e(old('card_header_bg', $school->schoolSetting->card_header_bg ?? '#f9fafb')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('card_header_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="card_header_bg" 
                                name="card_header_bg" 
                                class="color-input"
                                value="<?php echo e(old('card_header_bg', $school->schoolSetting->card_header_bg ?? '#f9fafb')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('card_header_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="page_header_border" class="form-label">Page Header Border</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="page_header_border_picker" 
                                value="<?php echo e(old('page_header_border', $school->schoolSetting->page_header_border ?? '#667eea')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('page_header_border').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="page_header_border" 
                                name="page_header_border" 
                                class="color-input"
                                value="<?php echo e(old('page_header_border', $school->schoolSetting->page_header_border ?? '#667eea')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('page_header_border_picker').value = this.value"
                            >
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Badge & Status Colors - Collapsible -->
                <div class="collapsible-section">
                    <button type="button" class="section-toggle" data-section="badge-colors-section" onclick="toggleSection('badge-colors-section')">
                        <span>🏷️ Badge & Status Colors (Pending, Approved, Cancelled)</span>
                        <span class="icon">▼</span>
                    </button>
                    <div class="section-content" id="badge-colors-section">
                        <div class="color-grid">
                    <div class="color-picker-group">
                        <label for="badge_pending_bg" class="form-label">Pending Badge Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_pending_bg_picker" 
                                value="<?php echo e(old('badge_pending_bg', $school->schoolSetting->badge_pending_bg ?? '#fbbf24')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_pending_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_pending_bg" 
                                name="badge_pending_bg" 
                                class="color-input"
                                value="<?php echo e(old('badge_pending_bg', $school->schoolSetting->badge_pending_bg ?? '#fbbf24')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_pending_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="badge_pending_text" class="form-label">Pending Badge Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_pending_text_picker" 
                                value="<?php echo e(old('badge_pending_text', $school->schoolSetting->badge_pending_text ?? '#78350f')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_pending_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_pending_text" 
                                name="badge_pending_text" 
                                class="color-input"
                                value="<?php echo e(old('badge_pending_text', $school->schoolSetting->badge_pending_text ?? '#78350f')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_pending_text_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="badge_approved_bg" class="form-label">Approved Badge Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_approved_bg_picker" 
                                value="<?php echo e(old('badge_approved_bg', $school->schoolSetting->badge_approved_bg ?? '#10b981')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_approved_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_approved_bg" 
                                name="badge_approved_bg" 
                                class="color-input"
                                value="<?php echo e(old('badge_approved_bg', $school->schoolSetting->badge_approved_bg ?? '#10b981')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_approved_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="badge_approved_text" class="form-label">Approved Badge Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_approved_text_picker" 
                                value="<?php echo e(old('badge_approved_text', $school->schoolSetting->badge_approved_text ?? '#065f46')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_approved_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_approved_text" 
                                name="badge_approved_text" 
                                class="color-input"
                                value="<?php echo e(old('badge_approved_text', $school->schoolSetting->badge_approved_text ?? '#065f46')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_approved_text_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="badge_cancelled_bg" class="form-label">Cancelled Badge Background</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_cancelled_bg_picker" 
                                value="<?php echo e(old('badge_cancelled_bg', $school->schoolSetting->badge_cancelled_bg ?? '#ef4444')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_cancelled_bg').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_cancelled_bg" 
                                name="badge_cancelled_bg" 
                                class="color-input"
                                value="<?php echo e(old('badge_cancelled_bg', $school->schoolSetting->badge_cancelled_bg ?? '#ef4444')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_cancelled_bg_picker').value = this.value"
                            >
                        </div>
                    </div>

                    <div class="color-picker-group">
                        <label for="badge_cancelled_text" class="form-label">Cancelled Badge Text</label>
                        <div class="color-input-wrapper">
                            <input 
                                type="color" 
                                id="badge_cancelled_text_picker" 
                                value="<?php echo e(old('badge_cancelled_text', $school->schoolSetting->badge_cancelled_text ?? '#7f1d1d')); ?>"
                                class="color-preview"
                                onchange="document.getElementById('badge_cancelled_text').value = this.value"
                            >
                            <input 
                                type="text" 
                                id="badge_cancelled_text" 
                                name="badge_cancelled_text" 
                                class="color-input"
                                value="<?php echo e(old('badge_cancelled_text', $school->schoolSetting->badge_cancelled_text ?? '#7f1d1d')); ?>"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                onchange="document.getElementById('badge_cancelled_text_picker').value = this.value"
                            >
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <h3 class="subsection-title">⚙️ Border Radius & Shapes</h3>
                <div class="form-group">
                    <label for="border_radius" class="form-label">Card/Modal Border Radius</label>
                    <div class="input-group">
                        <input 
                            type="range" 
                            id="border_radius" 
                            name="border_radius" 
                            class="form-range"
                            value="<?php echo e(old('border_radius', $school->schoolSetting->border_radius ?? 8)); ?>"
                            min="0"
                            max="30"
                            oninput="document.getElementById('border_radius_value').textContent = this.value"
                        >
                        <span class="input-suffix"><span id="border_radius_value"><?php echo e(old('border_radius', $school->schoolSetting->border_radius ?? 8)); ?></span> px</span>
                    </div>
                    <small class="form-text">Controls the roundness of cards, modals, and panels (0 = square, 30 = very rounded)</small>
                </div>

                <div class="form-group">
                    <label for="button_border_radius" class="form-label">Button Border Radius</label>
                    <div class="input-group">
                        <input 
                            type="range" 
                            id="button_border_radius" 
                            name="button_border_radius" 
                            class="form-range"
                            value="<?php echo e(old('button_border_radius', $school->schoolSetting->button_border_radius ?? 8)); ?>"
                            min="0"
                            max="30"
                            oninput="document.getElementById('button_border_radius_value').textContent = this.value"
                        >
                        <span class="input-suffix"><span id="button_border_radius_value"><?php echo e(old('button_border_radius', $school->schoolSetting->button_border_radius ?? 8)); ?></span> px</span>
                    </div>
                    <small class="form-text">Controls the roundness of buttons (0 = square, 30 = pill-shaped)</small>
                </div>

                <div class="divider"></div>

                <div class="section-header">
                    <h2 class="section-title">General Settings</h2>
                    <p class="section-description">Configure operational settings for your school</p>
                </div>

                <div class="form-group">
                    <label for="instructor_removal_notice_days" class="form-label">
                        Minimum Notice Period (Days)
                    </label>
                    <div class="input-group">
                        <input 
                            type="number" 
                            id="instructor_removal_notice_days" 
                            name="instructor_removal_notice_days" 
                            class="form-control <?php $__errorArgs = ['instructor_removal_notice_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('instructor_removal_notice_days', $school->instructor_removal_notice_days ?? 7)); ?>"
                            min="0"
                            max="30"
                            required
                        >
                        <span class="input-suffix">days</span>
                    </div>
                    <?php $__errorArgs = ['instructor_removal_notice_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small class="form-text">
                        Instructors must submit removal requests at least this many days before the scheduled time slot. 
                        Set to 0 to allow removal requests at any time. Maximum is 30 days.
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Save All Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle collapsible sections
function toggleSection(sectionId) {
    const toggle = document.querySelector(`[data-section="${sectionId}"]`);
    const content = document.getElementById(sectionId);
    
    if (content.classList.contains('active')) {
        content.classList.remove('active');
        toggle.classList.remove('active');
    } else {
        content.classList.add('active');
        toggle.classList.add('active');
    }
}

// Initialize - expand brand colors by default
document.addEventListener('DOMContentLoaded', function() {
    const brandSection = document.getElementById('brand-colors-section');
    if (brandSection) {
        brandSection.classList.add('active');
        document.querySelector('[data-section="brand-colors-section"]')?.classList.add('active');
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make($isAjax ?? false ? 'layouts.ajax' : 'layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views/school/admin/settings.blade.php ENDPATH**/ ?>