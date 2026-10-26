<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('robot_units')
            ->whereIn('status', ['perawatan', 'hilang'])
            ->update(['status' => 'rusak']);

        DB::table('profiles')
            ->whereNull('address')
            ->update(['address' => '']);

        Schema::table('profiles', function (Blueprint $table): void {
            $table->text('address')->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name', 100)->change();
        });

        Schema::table('robots', function (Blueprint $table): void {
            $table->string('name', 150)->change();
        });

        Schema::table('robot_units', function (Blueprint $table): void {
            $table->string('unit_code', 50)->change();
        });

        Schema::table('robot_category', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('robot_category', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::table('robot_units', function (Blueprint $table): void {
            $table->string('unit_code', 30)->change();
        });

        Schema::table('robots', function (Blueprint $table): void {
            $table->string('name', 120)->change();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name', 80)->change();
        });

        Schema::table('profiles', function (Blueprint $table): void {
            $table->text('address')->nullable()->change();
        });

        DB::table('robot_units')->where('status', 'rusak')->update(['status' => 'perawatan']);
    }
};
