<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('robot_unit_id')->constrained()->restrictOnDelete();
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('late_days')->default(0);
            $table->unsignedInteger('fine_amount')->default(0);
            $table->string('status', 20)->default('dipinjam')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['robot_unit_id', 'status']);
            $table->index(['loan_date', 'return_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
