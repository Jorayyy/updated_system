<?php

namespace Database\Seeders;

use App\Models\Computer;
use Illuminate\Database\Seeder;

class ComputerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $computers = [
            [
                'pc_number' => 'PC-001',
                'name' => 'Workstation 1',
                'location' => 'Floor 1, Area A',
                'specs' => 'Intel Core i5-10400, 8GB RAM, 256GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-002',
                'name' => 'Workstation 2',
                'location' => 'Floor 1, Area A',
                'specs' => 'Intel Core i5-10400, 8GB RAM, 256GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-003',
                'name' => 'Workstation 3',
                'location' => 'Floor 1, Area A',
                'specs' => 'Intel Core i5-10400, 8GB RAM, 256GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-004',
                'name' => 'Workstation 4',
                'location' => 'Floor 1, Area B',
                'specs' => 'Intel Core i5-10400, 16GB RAM, 512GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-005',
                'name' => 'Workstation 5',
                'location' => 'Floor 1, Area B',
                'specs' => 'Intel Core i5-10400, 16GB RAM, 512GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-006',
                'name' => 'Workstation 6',
                'location' => 'Floor 2, Area A',
                'specs' => 'Intel Core i7-10700, 16GB RAM, 512GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-007',
                'name' => 'Workstation 7',
                'location' => 'Floor 2, Area A',
                'specs' => 'Intel Core i7-10700, 16GB RAM, 512GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-008',
                'name' => 'Workstation 8',
                'location' => 'Floor 2, Area B',
                'specs' => 'Intel Core i7-10700, 32GB RAM, 1TB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-009',
                'name' => 'HR Station',
                'location' => 'HR Department',
                'specs' => 'Intel Core i7-10700, 16GB RAM, 512GB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
            [
                'pc_number' => 'PC-010',
                'name' => 'Admin Station',
                'location' => 'Admin Office',
                'specs' => 'Intel Core i7-10700, 32GB RAM, 1TB SSD, Windows 11',
                'status' => 'available',
                'is_active' => true,
            ],
        ];

        foreach ($computers as $computer) {
            Computer::updateOrCreate(
                ['pc_number' => $computer['pc_number']],
                $computer
            );
        }

        $this->command->info('Sample computers seeded successfully!');
    }
}
