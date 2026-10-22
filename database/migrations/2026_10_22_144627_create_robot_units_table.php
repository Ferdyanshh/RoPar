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
        Schema::create('robot_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('robot_id')->constrained()->restrictOnDelete();
            $table->string('unit_code', 30)->unique();
            $table->string('status', 20)->default('tersedia')->index();
            $table->text('condition_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robot_units');
    }
};
