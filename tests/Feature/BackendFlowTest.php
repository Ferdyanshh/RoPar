<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\RobotUnitStatus;
use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\RobotUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BackendFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register_and_profile_is_created(): void
    {
        $response = $this->postJson('/backend/register', [
            'name' => 'Ferdy',
            'email' => 'ferdy@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'full_name' => 'Ferdy Example',
            'phone' => '081234567890',
            'address' => 'Bandung',
            'birth_date' => '2000-01-01',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.role', UserRole::Member->value)
            ->assertJsonPath('data.profile.full_name', 'Ferdy Example');

        $this->assertDatabaseHas('users', ['email' => 'ferdy@example.test']);
        $this->assertDatabaseHas('profiles', ['phone' => '081234567890']);
        $this->assertTrue(Hash::check('password123', User::query()->where('email', 'ferdy@example.test')->firstOrFail()->password));
    }

    public function test_backend_requires_auth_and_members_cannot_access_admin_routes(): void
    {
        $this->getJson('/backend/profile')->assertUnauthorized();

        $member = User::factory()->create(['role' => UserRole::Member]);
        $this->actingAs($member)->getJson('/backend/admin/categories')->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)->getJson('/backend/admin/categories')->assertOk();
    }

    public function test_member_can_borrow_available_unit_and_is_limited_to_two_active_loans(): void
    {
        $member = User::factory()->create();
        $firstUnit = RobotUnit::factory()->create();

        $this->actingAs($member)->postJson('/backend/loans', [
            'robot_unit_id' => $firstUnit->id,
        ])->assertCreated()
            ->assertJsonPath('data.status', LoanStatus::Borrowed->value)
            ->assertJsonPath('data.due_date', CarbonImmutable::today(config('app.timezone'))->addDays(5)->toJSON());

        $this->assertDatabaseHas('robot_units', [
            'id' => $firstUnit->id,
            'status' => RobotUnitStatus::Borrowed->value,
        ]);

        $secondUnit = RobotUnit::factory()->create();
        $this->postJson('/backend/loans', ['robot_unit_id' => $secondUnit->id])->assertCreated();

        $thirdUnit = RobotUnit::factory()->create();
        $this->postJson('/backend/loans', ['robot_unit_id' => $thirdUnit->id])->assertUnprocessable();
        $this->assertDatabaseMissing('loans', ['robot_unit_id' => $thirdUnit->id]);
    }

    public function test_member_cannot_borrow_a_unit_that_is_already_borrowed(): void
    {
        $member = User::factory()->create();
        $unit = RobotUnit::factory()->create(['status' => RobotUnitStatus::Borrowed]);

        $this->actingAs($member)->postJson('/backend/loans', [
            'robot_unit_id' => $unit->id,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_admin_return_updates_status_unit_and_late_fine_and_rejects_second_return(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $unit = RobotUnit::factory()->create(['status' => RobotUnitStatus::Borrowed]);
        $loan = Loan::factory()->create([
            'user_id' => $member->id,
            'robot_unit_id' => $unit->id,
            'loan_date' => now()->subDays(8)->toDateString(),
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($admin)->postJson("/backend/admin/loans/{$loan->id}/return")
            ->assertOk()
            ->assertJsonPath('data.status', LoanStatus::Returned->value)
            ->assertJsonPath('data.return_date', CarbonImmutable::today(config('app.timezone'))->toJSON())
            ->assertJsonPath('data.late_days', 3)
            ->assertJsonPath('data.fine_amount', 15000);

        $this->assertDatabaseHas('robot_units', [
            'id' => $unit->id,
            'status' => RobotUnitStatus::Available->value,
        ]);
        $this->postJson("/backend/admin/loans/{$loan->id}/return")->assertUnprocessable();
    }
}
