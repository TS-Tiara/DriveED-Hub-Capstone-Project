<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

// List all schools and their slugs
$schools = School::all(['id', 'name', 'slug']);
echo "=== SCHOOLS ===\n";
foreach ($schools as $school) {
    echo "  ID={$school->id}  slug={$school->slug}  name={$school->name}\n";
}

echo "\n=== BRANCH SECRETARIES ===\n";
$secretaries = Admin::where('role', 'branch_secretary')->get(['id', 'name', 'email', 'school_id', 'branch_id', 'is_active']);
foreach ($secretaries as $s) {
    $pwOk = Hash::check('P@ssw0rd123', $s->password);
    echo "  {$s->email}  school_id={$s->school_id}  branch_id={$s->branch_id}  is_active={$s->is_active}  pw=" . ($pwOk ? 'OK' : 'FAIL') . "\n";
}

echo "\n=== SCHOOL ADMINS ===\n";
$admins = Admin::where('role', 'school_admin')->get(['id', 'name', 'email', 'school_id', 'is_active']);
foreach ($admins as $a) {
    $pwOk = Hash::check('P@ssw0rd123', $a->password);
    echo "  {$a->email}  school_id={$a->school_id}  is_active={$a->is_active}  pw=" . ($pwOk ? 'OK' : 'FAIL') . "\n";
}

// Simulate the login query for "smart-driving" school + secretary@gmail.com
echo "\n=== LOGIN SIMULATION ===\n";
$school = School::where('slug', 'smart-driving')->first();
if ($school) {
    $admin = Admin::where('school_id', $school->id)->where('email', 'secretary@gmail.com')->first();
    echo "Login query result for secretary@gmail.com on smart-driving:\n";
    echo "  Found: " . ($admin ? 'YES' : 'NO') . "\n";
    if ($admin) {
        echo "  role: {$admin->role}\n";
        echo "  is_active: {$admin->is_active}\n";
        echo "  pw check: " . (Hash::check('P@ssw0rd123', $admin->password) ? 'PASS' : 'FAIL') . "\n";
    }
}
