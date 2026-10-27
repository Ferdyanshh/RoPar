<?php

namespace Tests\Feature;

use App\Enums\LoanStatus;
use App\Enums\RobotUnitStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Robot;
use App\Models\RobotUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BackendFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_seeder_creates_a_profile_with_an_address(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('profiles', [
            'full_name' => 'Admin RoPar',
            'phone' => '081234567890',
            'address' => 'Alamat Admin RoPar',
        ]);
    }

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

    public function test_registration_requires_address_and_accepts_international_phone_number(): void
    {
        $payload = [
            'name' => 'Ferdy',
            'email' => 'ferdy@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'full_name' => 'Ferdy Example',
            'phone' => '+628123456789',
        ];

        $this->postJson('/backend/register', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['address']);

        $this->postJson('/backend/register', [...$payload, 'address' => 'Bandung'])
            ->assertCreated()
            ->assertJsonPath('data.profile.phone', '+628123456789');
    }

    public function test_backend_requires_auth_and_members_cannot_access_admin_routes(): void
    {
        $this->getJson('/backend/profile')->assertUnauthorized();

        $member = User::factory()->create(['role' => UserRole::Member]);
        $this->actingAs($member)->getJson('/backend/admin/categories')->assertForbidden();
        $this->getJson('/admin/loans/history/print')->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)->getJson('/backend/admin/categories')->assertOk();
    }

    public function test_member_and_admin_can_update_their_display_currency(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member)
            ->patchJson('/backend/profile', ['currency' => 'USD'])
            ->assertOk()
            ->assertJsonPath('data.currency', 'USD');
        $this->assertDatabaseHas('users', ['id' => $member->id, 'currency' => 'USD']);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)
            ->patchJson('/backend/profile', ['currency' => 'IDR'])
            ->assertOk()
            ->assertJsonPath('data.currency', 'IDR');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'currency' => 'IDR']);

        $this->patchJson('/backend/profile', ['currency' => 'EUR'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_admin_can_generate_a_pdf_for_the_filtered_loan_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create();
        $unit = RobotUnit::factory()->create();
        Loan::factory()->create([
            'user_id' => $member->id,
            'robot_unit_id' => $unit->id,
            'status' => LoanStatus::Returned,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/admin/loans/history/print?status='.LoanStatus::Returned->value);

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=laporan-riwayat-peminjaman.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $this->getJson('/admin/loans/history/print?from=2026-10-25&to=2026-10-20')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['from']);
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

    public function test_member_can_return_own_loan_but_cannot_return_another_members_loan(): void
    {
        $member = User::factory()->create();
        $unit = RobotUnit::factory()->create(['status' => RobotUnitStatus::Borrowed]);
        $today = CarbonImmutable::today(config('app.timezone'));
        $loan = Loan::factory()->create([
            'user_id' => $member->id,
            'robot_unit_id' => $unit->id,
            'loan_date' => $today->subDays(5)->toDateString(),
            'due_date' => $today->toDateString(),
        ]);

        $this->actingAs($member)
            ->postJson("/backend/loans/{$loan->id}/return", ['return_date' => $today->subDay()->toDateString()])
            ->assertOk()
            ->assertJsonPath('data.status', LoanStatus::Returned->value)
            ->assertJsonPath('data.return_date', $today->toJSON())
            ->assertJsonPath('data.late_days', 0)
            ->assertJsonPath('data.fine_amount', 0);

        $this->assertDatabaseHas('robot_units', [
            'id' => $unit->id,
            'status' => RobotUnitStatus::Available->value,
        ]);

        $otherMember = User::factory()->create();
        $otherUnit = RobotUnit::factory()->create(['status' => RobotUnitStatus::Borrowed]);
        $otherLoan = Loan::factory()->create([
            'user_id' => $otherMember->id,
            'robot_unit_id' => $otherUnit->id,
        ]);

        $this->postJson("/backend/loans/{$otherLoan->id}/return")
            ->assertNotFound();

        $this->assertDatabaseHas('loans', ['id' => $otherLoan->id, 'status' => LoanStatus::Borrowed->value]);
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

        $clientProvidedDate = CarbonImmutable::today(config('app.timezone'))->subDays(3)->toDateString();
        $this->actingAs($admin)->postJson("/backend/admin/loans/{$loan->id}/return", ['return_date' => $clientProvidedDate])
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

    public function test_admin_cannot_delete_a_category_assigned_to_a_robot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::factory()->create();
        $robot = Robot::factory()->create();
        $robot->categories()->attach($category);

        $this->actingAs($admin)
            ->deleteJson("/backend/admin/categories/{$category->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category']);

        $this->assertDatabaseHas('robot_category', [
            'robot_id' => $robot->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_unit_list_defaults_to_fifteen_rows_and_accepts_requirement_unit_codes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $robot = Robot::factory()->create();

        RobotUnit::factory()->count(16)->create(['robot_id' => $robot->id]);

        $this->actingAs($admin)
            ->getJson('/backend/admin/robot-units')
            ->assertOk()
            ->assertJsonPath('per_page', 15);

        $response = $this->postJson('/backend/admin/robot-units', [
            'robot_id' => $robot->id,
            'unit_code' => 'AYA-001',
        ])->assertCreated()->assertJsonPath('data.unit_code', 'AYA-001');

        $unitId = $response->json('data.id');

        $this->patchJson("/backend/admin/robot-units/{$unitId}", ['status' => RobotUnitStatus::Damaged->value])
            ->assertOk()
            ->assertJsonPath('data.status', RobotUnitStatus::Damaged->value);
    }
}
