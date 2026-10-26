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
            ['full_name' => 'Anggota Demo', 'phone' => '081234567891', 'address' => 'Alamat Anggota Demo'],
        );

        $secondMember = User::query()->updateOrCreate(
            ['email' => 'member2@robotpacar.test'],
            [
                'name' => 'Andi Demo',
                'password' => Hash::make(env('DEMO_MEMBER_PASSWORD', 'password')),
                'role' => UserRole::Member,
            ],
        );

        $secondMember->profile()->updateOrCreate(
            ['user_id' => $secondMember->id],
            ['full_name' => 'Andi Demo', 'phone' => '081234567892', 'address' => 'Alamat Andi Demo'],
        );

        $today = CarbonImmutable::today(config('app.timezone'));
        $this->seedAndiLoan($secondMember, $today);

        if ($member->loans()->exists()) {
            return;
        }

        $units = RobotUnit::query()->whereIn('unit_code', ['AYA-001', 'AYA-002', 'RIO-001', 'RIO-002'])->orderBy('unit_code')->get()->keyBy('unit_code');

        if ($units->count() !== 4) {
            throw new RuntimeException('RobotCatalogSeeder must create units AYA-001, AYA-002, RIO-001, and RIO-002 first.');
        }

        $member->loans()->create([
            'robot_unit_id' => $units['AYA-001']->id,
            'loan_date' => $today,
            'due_date' => $today->addDays(5),
            'status' => LoanStatus::Borrowed,
        ]);
        $units['AYA-001']->update(['status' => RobotUnitStatus::Borrowed]);

        $onTimeReturnDate = $today->subDays(2);
        $member->loans()->create([
            'robot_unit_id' => $units['AYA-002']->id,
            'loan_date' => $onTimeReturnDate->subDays(5),
            'due_date' => $onTimeReturnDate,
            'return_date' => $onTimeReturnDate,
            'returned_by' => $admin->id,
            'status' => LoanStatus::Returned,
        ]);

        $overdueDueDate = $today->subDays(2);
        $member->loans()->create([
            'robot_unit_id' => $units['RIO-001']->id,
            'loan_date' => $overdueDueDate->subDays(5),
            'due_date' => $overdueDueDate,
            'status' => LoanStatus::Borrowed,
        ]);
        $units['RIO-001']->update(['status' => RobotUnitStatus::Borrowed]);

        $lateReturnDate = $today->subDays(1);
        $member->loans()->create([
            'robot_unit_id' => $units['RIO-002']->id,
            'loan_date' => $lateReturnDate->subDays(7),
            'due_date' => $lateReturnDate->subDays(2),
            'return_date' => $lateReturnDate,
            'returned_by' => $admin->id,
            'late_days' => 2,
            'fine_amount' => 10000,
            'status' => LoanStatus::Returned,
        ]);
    }

    private function seedAndiLoan(User $member, CarbonImmutable $today): void
    {
        if ($member->loans()->exists()) {
            return;
        }

        $unit = RobotUnit::query()
            ->whereNotIn('unit_code', ['AYA-001', 'AYA-002', 'RIO-001', 'RIO-002'])
            ->where('status', RobotUnitStatus::Available->value)
            ->orderBy('unit_code')
            ->first();

        if ($unit === null) {
            throw new RuntimeException('DemoLoanSeeder membutuhkan satu unit robot tambahan yang tersedia untuk Andi Demo.');
        }

        $member->loans()->create([
            'robot_unit_id' => $unit->id,
            'loan_date' => $today,
            'due_date' => $today->addDays(5),
            'status' => LoanStatus::Borrowed,
        ]);

        $unit->update(['status' => RobotUnitStatus::Borrowed]);
    }
}
