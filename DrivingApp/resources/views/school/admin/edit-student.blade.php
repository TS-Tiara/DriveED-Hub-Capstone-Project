<!DOCTYPE html>
<html>
<head>
    @php
        $school = $school ?? $currentSchool ?? null;
        $schoolName = $school->name ?? 'Driving School';
    @endphp
    <title>Edit Student - {{ $schoolName }}</title>
    <style>
        .error { color: red; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Edit Student</h1>

    <form id="editStudentForm" action="{{ $schoolRoute('admin.students.update', ['id' => $student->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Name:</label><br>
        <input type="text" name="name" required maxlength="255" value="{{ old('name', $student->name) }}">
        @error('name') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <label>Email:</label><br>
        <input type="email" name="email" id="email" required value="{{ old('email', $student->email) }}">
        <small>Only Gmail or Yahoo</small>
        @error('email') <div class="error">{{ $message }}</div> @enderror
        <div id="emailError" class="error"></div>
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" minlength="6" placeholder="Leave blank to keep current password">
        <small>Minimum 6 characters if changing</small>
        @error('password') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact" id="contact" maxlength="13" value="{{ old('contact', $student->contact) }}">
        <small>Philippine mobile number (09xxxxxxxxx or +639xxxxxxxxx)</small>
        @error('contact') <div class="error">{{ $message }}</div> @enderror
        <div id="contactError" class="error"></div>
        <br><br>

        <label>Address:</label><br>
        <input type="text" name="address" maxlength="255" value="{{ old('address', $student->address) }}">
        @error('address') <div class="error">{{ $message }}</div> @enderror
        <br><br>

        <button type="submit">Update Student</button>
    </form>

    <script>
        const emailInput = document.getElementById('email');
        const contactInput = document.getElementById('contact');
        const form = document.getElementById('editStudentForm');
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
    <a href="{{ $schoolRoute('admin.students') }}">Back to Students</a>
</body>
</html>
