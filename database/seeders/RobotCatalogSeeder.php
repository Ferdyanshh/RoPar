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
            'Pendengar Setia',
            'Pemula',
            'Premium',
        ])->mapWithKeys(fn (string $name): array => [
            $name => Category::query()->firstOrCreate(['name' => $name])->id,
        ]);

        $catalog = [
            'Astra' => ['Premium', 'Pendengar Setia'],
            'Bima' => ['Humoris', 'Pemula'],
            'Citra' => ['Romantis', 'Pendengar Setia'],
            'Damar' => ['Humoris'],
            'Elara' => ['Premium', 'Romantis'],
            'Nara' => ['Pemula', 'Pendengar Setia'],
        ];

        $unitNumber = 1;

        foreach ($catalog as $name => $categoryNames) {
            $robot = Robot::query()->firstOrCreate(['name' => $name], [
                'description' => 'Unit robot '.$name.' untuk dipinjam oleh anggota.',
            ]);
            $robot->categories()->syncWithoutDetaching(collect($categoryNames)->map(fn (string $categoryName): int => $categories[$categoryName])->all());

            foreach (range(1, 2) as $unitIndex) {
                RobotUnit::query()->firstOrCreate(
                    ['unit_code' => sprintf('RBT-%03d', $unitNumber++)],
                    ['robot_id' => $robot->id, 'status' => RobotUnitStatus::Available],
                );
            }
        }
    }
}
