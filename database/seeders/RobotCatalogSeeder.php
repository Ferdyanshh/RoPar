<?php

namespace Database\Seeders;

use App\Enums\RobotUnitStatus;
use App\Models\Category;
use App\Models\Robot;
use App\Models\RobotUnit;
use Illuminate\Database\Seeder;

class RobotCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = collect([
            'Romantis',
            'Humoris',
            'Penyayang',
            'Pendiam',
            'Petualang',
        ])->mapWithKeys(fn (string $name): array => [
            $name => Category::query()->firstOrCreate(['name' => $name])->id,
        ]);

        $catalog = [
            'Aya' => ['categories' => ['Romantis', 'Penyayang'], 'units' => ['AYA-001', 'AYA-002']],
            'Rio' => ['categories' => ['Humoris', 'Petualang'], 'units' => ['RIO-001', 'RIO-002', 'RIO-003']],
            'Luna' => ['categories' => ['Pendiam', 'Penyayang'], 'units' => ['LUN-001']],
            'Dio' => ['categories' => ['Humoris'], 'units' => ['DIO-001', 'DIO-002']],
        ];

        foreach ($catalog as $name => $robotData) {
            $robot = Robot::query()->firstOrCreate(['name' => $name], [
                'description' => 'Unit robot '.$name.' untuk dipinjam oleh anggota.',
            ]);
            $robot->categories()->syncWithoutDetaching(collect($robotData['categories'])->map(fn (string $categoryName): int => $categories[$categoryName])->all());

            foreach ($robotData['units'] as $unitCode) {
                RobotUnit::query()->firstOrCreate(
                    ['unit_code' => $unitCode],
                    ['robot_id' => $robot->id, 'status' => RobotUnitStatus::Available],
                );
            }
        }
    }
}
