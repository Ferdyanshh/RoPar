<?php

namespace Database\Seeders;

use App\Enums\LoanStatus;
use App\Enums\RobotUnitStatus;
use App\Enums\UserRole;
use App\Models\RobotUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoLoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin->value)->firstOrFail();
        $member = User::query()->updateOrCreate(
            ['email' => env('DEMO_MEMBER_EMAIL', 'member@robotpacar.test')],
            [
                'name' => 'Anggota Demo',
                'password' => Hash::make(env('DEMO_MEMBER_PASSWORD', 'password')),
                'role' => UserRole::Member,
            ],
        );

        $member->profile()->updateOrCreate(
            ['user_id' => $member->id],
            ['full_name' => 'Anggota Demo', 'phone' => '081234567891'],
        );

        if ($member->loans()->exists()) {
            return;
        }

        $units = RobotUnit::query()->whereIn('unit_code', ['RBT-001', 'RBT-002', 'RBT-003'])->orderBy('unit_code')->get()->keyBy('unit_code');

        if ($units->count() !== 3) {
            throw new RuntimeException('RobotCatalogSeeder must create units RBT-001 through RBT-003 first.');
        }

        $today = CarbonImmutable::today(config('app.timezone'));
        $member->loans()->create([
            'robot_unit_id' => $units['RBT-001']->id,
            'loan_date' => $today,
            'due_date' => $today->addDays(5),
            'status' => LoanStatus::Borrowed,
        ]);
        $units['RBT-001']->update(['status' => RobotUnitStatus::Borrowed]);

        $onTimeReturnDate = $today->subDays(2);
        $member->loans()->create([
            'robot_unit_id' => $units['RBT-002']->id,
            'loan_date' => $onTimeReturnDate->subDays(5),
            'due_date' => $onTimeReturnDate,
            'return_date' => $onTimeReturnDate,
            'returned_by' => $admin->id,
            'status' => LoanStatus::Returned,
        ]);

        $lateReturnDate = $today->subDays(1);
        $member->loans()->create([
            'robot_unit_id' => $units['RBT-003']->id,
            'loan_date' => $lateReturnDate->subDays(7),
            'due_date' => $lateReturnDate->subDays(2),
            'return_date' => $lateReturnDate,
            'returned_by' => $admin->id,
            'late_days' => 2,
            'fine_amount' => 10000,
            'status' => LoanStatus::Returned,
        ]);
    }
}
