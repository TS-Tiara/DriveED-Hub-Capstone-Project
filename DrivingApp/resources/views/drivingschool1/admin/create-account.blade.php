<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Create Account - {{ $schoolName }}</title>
    <style>
        .error { color: red; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Create Account</h1>

    <form id="createAccountForm" action="{{ $schoolRoute('admin.storeAccount') }}" method="POST">
        @csrf

        <label>Name:</label><br>
        <input type="text" name="name" required maxlength="255" value="{{ old('name') }}">
        @error('name') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <label>Email:</label><br>
        <input type="email" name="email" id="email" required value="{{ old('email') }}">
        <small>Only Gmail or Yahoo</small>
        @error('email') <div class="error">{{ $message }}</div> @enderror
        <div id="emailError" class="error"></div>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required minlength="6">
        <small>Minimum 6 characters</small>
        @error('password') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact" id="contact" maxlength="13" value="{{ old('contact') }}">
        <small>Philippine mobile number (09xxxxxxxxx or +639xxxxxxxxx)</small>
        @error('contact') <div class="error">{{ $message }}</div> @enderror
        <div id="contactError" class="error"></div>
        <br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student" {{ old('role')=='student' ? 'selected' : '' }}>Student</option>
            <option value="instructor" {{ old('role')=='instructor' ? 'selected' : '' }}>Instructor</option>
        </select>
        @error('role') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <button type="submit">Create Account</button>
    </form>

    <script>
        const emailInput = document.getElementById('email');
        const contactInput = document.getElementById('contact');
        const form = document.getElementById('createAccountForm');
        const emailError = document.getElementById('emailError');
        const contactError = document.getElementById('contactError');

        form.addEventListener('submit', function(e) {
            let valid = true;
            emailError.textContent = '';
            contactError.textContent = '';

            // Email validation: Gmail or Yahoo
            if (!/@(gmail\.com|yahoo\.com)$/i.test(emailInput.value)) {
                emailError.textContent = 'Email must be Gmail or Yahoo.';
                valid = false;
            }

            // Contact validation: Philippine mobile number
            if (contactInput.value && !/^(09\d{9}|\+639\d{9})$/.test(contactInput.value)) {
                contactError.textContent = 'Contact must be a valid Philippine number.';
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    </script>

    <br>
    <a href="{{ $schoolRoute('admin.dashboard') }}">Back to Dashboard</a>
</body>
</html>
