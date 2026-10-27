<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Loan;
use App\Models\RobotUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_print_the_filtered_loan_report_as_a_pdf(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $member->id,
            'robot_unit_id' => RobotUnit::factory(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.loans.print', [
                'month' => $loan->loan_date->format('Y-m'),
                'status' => $loan->status->value,
            ]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=laporan-riwayat-peminjaman.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_printed_loan_report_rejects_conflicting_date_filters(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.loans.print', ['month' => '2026-10', 'from' => '2026-10-01']))
            ->assertUnprocessable();
    }
}
