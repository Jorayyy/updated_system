<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class CommonDataSeeder extends Seeder
{
    public function run(): void
    {
        $depts = [
            'Operations' => 'Core Operations management and support',
            'Customer Support' => 'Customer service and technical support level 1',
            'Technical Support' => 'Advanced technical assistance and infrastructure',
            'Sales' => 'Sales and marketing department',
            'Data Entry' => 'Data encoding and validation',
            'Human Resources' => 'Personnel management and HR services',
            'Accounting' => 'Financial tracking and payroll processing',
            'Management' => 'Executive level and team management',
            'Security' => 'Facility safety and IT security'
        ];

        foreach ($depts as $deptName => $description) {
            Department::firstOrCreate(
                ['name' => $deptName],
                [
                    'description' => $description,
                    'is_active' => true
                ]
            );
        }
    }
}
