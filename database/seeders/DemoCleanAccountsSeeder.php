<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoCleanAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = (string) env('DEMO_SEED_PASSWORD', 'DriveDemo123');
        $hashedPassword = Hash::make($demoPassword);

        $demoSchools = [
            'lyspeed-driving' => ['domain' => 'lyspeed.test', 'label' => 'LySpeed'],
            'drived-hub' => ['domain' => 'drivedhub.test', 'label' => 'DriveED Hub'],
        ];

        $schools = School::whereIn('slug', array_keys($demoSchools))->get()->keyBy('slug');

        if ($schools->isEmpty()) {
            $this->command->warn('No demo schools found. Skipping clean demo account seeding.');
            return;
        }

        foreach ($demoSchools as $slug => $meta) {
            $school = $schools->get($slug);

            if (!$school) {
                $this->command->warn("Demo school {$slug} not found. Skipping.");
                continue;
            }

            $branchId = DB::table('branches')
                ->where('school_id', '=', $school->id)
                ->orderBy('id')
                ->value('id');

            if (!$branchId) {
                $this->command->warn("No branch found for {$slug}. Skipping clean account creation.");
                continue;
            }

            for ($i = 1; $i <= 15; $i++) {
                $this->seedLearnerAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "guest{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Guest {$i}",
                    role: 'guest',
                    password: $hashedPassword,
                    contactSuffix: $i
                );

                $this->seedLearnerAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "student{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Student {$i}",
                    role: 'student',
                    password: $hashedPassword,
                    contactSuffix: $i + 100
                );

                $this->seedInstructorAccount(
                    schoolId: $school->id,
                    branchId: $branchId,
                    email: "instructor{$i}@{$meta['domain']}",
                    name: "{$meta['label']} Instructor {$i}",
                    password: $hashedPassword,
                    sequence: $i
                );
            }
        }

        $this->command->info('   ✓ Clean demo accounts ready: guest1-15, student1-15, instructor1-15 per demo school');
    }

    private function seedLearnerAccount(
        int $schoolId,
        int $branchId,
        string $email,
        string $name,
        string $role,
        string $password,
        int $contactSuffix
    ): void {
        $account = Student::updateOrCreate(
            ['school_id' => $schoolId, 'email' => $email],
            [
                'branch_id' => $branchId,
                'name' => $name,
                'contact' => '+63-900-000-' . str_pad((string) $contactSuffix, 4, '0', STR_PAD_LEFT),
                'password' => $password,
                'status' => 'active',
                'student_license_status' => 'none',
                'enrollment_date' => now(),
            ]
        );

        $account->role = $role;
        $account->email_verified_at = $account->email_verified_at ?? now();
        $account->verification_code = null;
        $account->verification_code_expires_at = null;
        $account->verification_attempts = 0;
        $account->last_verification_attempt_at = null;
        $account->save();

        $this->purgeLearnerData($account->id);
        $account->unlockFromCourse();
    }

    private function seedInstructorAccount(
        int $schoolId,
        int $branchId,
        string $email,
        string $name,
        string $password,
        int $sequence
    ): void {
        $instructor = Instructor::updateOrCreate(
            ['school_id' => $schoolId, 'email' => $email],
            [
                'branch_id' => $branchId,
                'name' => $name,
                'contact' => '+63-901-000-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'password' => $password,
                'status' => 'active',
                'availability' => 'available',
                'license_number' => 'DEMO-INST-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                'bio' => 'Clean demo instructor account with no seeded activities.',
            ]
        );

        $this->purgeInstructorData($instructor->id);
    }

    private function purgeLearnerData(int $studentId): void
    {
        DB::table('notifications')
            ->where('notifiable_type', '=', Student::class)
            ->where('notifiable_id', '=', $studentId)
            ->delete();

        DB::table('payments')->where('payer_user_id', '=', $studentId)->delete();
        DB::table('bookings')->where('student_id', '=', $studentId)->delete();
        DB::table('progresses')->where('student_id', '=', $studentId)->delete();
        DB::table('enrollments')->where('student_id', '=', $studentId)->delete();
        DB::table('enrollment_requests')->where('learner_id', '=', $studentId)->delete();
    }

    private function purgeInstructorData(int $instructorId): void
    {
        DB::table('notifications')
            ->where('notifiable_type', '=', Instructor::class)
            ->where('notifiable_id', '=', $instructorId)
            ->delete();

        DB::table('instructor_removal_requests')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('schedule_instructors')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('session_completions')->where('instructor_id', '=', $instructorId)->delete();
        DB::table('session_completions')->where('logged_by', '=', $instructorId)->delete();
        DB::table('bookings')->where('instructor_id', '=', $instructorId)->update(['instructor_id' => null]);
    }
}