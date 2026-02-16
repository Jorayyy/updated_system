<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = date('Y');
        
        // =====================================================
        // PHILIPPINE HOLIDAYS
        // =====================================================
        
        // Philippine Regular Holidays (100% premium pay)
        $phRegularHolidays = [
            ['name' => 'New Year\'s Day', 'date' => "$year-01-01", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Araw ng Kagitingan (Day of Valor)', 'date' => "$year-04-09", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Maundy Thursday', 'date' => "$year-04-02", 'description' => 'Philippine Regular Holiday - April 2, 2026'],
            ['name' => 'Good Friday', 'date' => "$year-04-03", 'description' => 'Philippine Regular Holiday - April 3, 2026'],
            ['name' => 'Labor Day', 'date' => "$year-05-01", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Independence Day', 'date' => "$year-06-12", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'National Heroes Day', 'date' => "$year-08-31", 'description' => 'Philippine Regular Holiday - Last Monday of August'],
            ['name' => 'Bonifacio Day', 'date' => "$year-11-30", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Christmas Day', 'date' => "$year-12-25", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Rizal Day', 'date' => "$year-12-30", 'description' => 'Philippine Regular Holiday'],
        ];

        // Philippine Special Non-Working Holidays (30% premium pay)
        $phSpecialHolidays = [
            ['name' => 'Chinese New Year', 'date' => "$year-02-17", 'description' => 'Philippine Special Non-Working Holiday - Feb 17, 2026'],
            ['name' => 'EDSA People Power Revolution Anniversary', 'date' => "$year-02-25", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Black Saturday', 'date' => "$year-04-04", 'description' => 'Philippine Special Non-Working Holiday - April 4, 2026'],
            ['name' => 'Ninoy Aquino Day', 'date' => "$year-08-21", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'All Saints\' Day', 'date' => "$year-11-01", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'All Souls\' Day', 'date' => "$year-11-02", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Feast of the Immaculate Conception', 'date' => "$year-12-08", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Christmas Eve', 'date' => "$year-12-24", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Last Day of the Year', 'date' => "$year-12-31", 'description' => 'Philippine Special Non-Working Holiday'],
        ];

        // Philippine Special Working Holidays
        $phSpecialWorkingHolidays = [
            ['name' => 'Eid\'l Fitr (End of Ramadan)', 'date' => "$year-03-20", 'description' => 'Philippine Special Working Holiday - (Estimated)'],
            ['name' => 'Eid\'l Adha (Feast of Sacrifice)', 'date' => "$year-05-27", 'description' => 'Philippine Special Working Holiday - (Estimated)'],
        ];

        // =====================================================
        // UNITED STATES HOLIDAYS
        // =====================================================
        
        // US Federal Holidays
        $usHolidays = [
            ['name' => 'New Year\'s Day (US)', 'date' => "$year-01-01", 'description' => 'US Federal Holiday'],
            ['name' => 'Martin Luther King Jr. Day', 'date' => "$year-01-19", 'description' => 'US Federal Holiday - Jan 19, 2026'],
            ['name' => 'Presidents\' Day', 'date' => "$year-02-16", 'description' => 'US Federal Holiday - Feb 16, 2026'],
            ['name' => 'Memorial Day', 'date' => "$year-05-25", 'description' => 'US Federal Holiday - May 25, 2026'],
            ['name' => 'Juneteenth National Independence Day', 'date' => "$year-06-19", 'description' => 'US Federal Holiday'],
            ['name' => 'Independence Day (US)', 'date' => "$year-07-04", 'description' => 'US Federal Holiday'],
            ['name' => 'Labor Day (US)', 'date' => "$year-09-07", 'description' => 'US Federal Holiday - Sept 7, 2026'],
            ['name' => 'Columbus Day / Indigenous Peoples\' Day', 'date' => "$year-10-12", 'description' => 'US Federal Holiday - Oct 12, 2026'],
            ['name' => 'Veterans Day', 'date' => "$year-11-11", 'description' => 'US Federal Holiday'],
            ['name' => 'Thanksgiving Day', 'date' => "$year-11-26", 'description' => 'US Federal Holiday - Nov 26, 2026'],
            ['name' => 'Christmas Day (US)', 'date' => "$year-12-25", 'description' => 'US Federal Holiday'],
        ];

        // US Common Non-Federal Holidays (Special)
        $usSpecialHolidays = [
            ['name' => 'Valentine\'s Day', 'date' => "$year-02-14", 'description' => 'US Observance'],
            ['name' => 'St. Patrick\'s Day', 'date' => "$year-03-17", 'description' => 'US Observance'],
            ['name' => 'Easter Sunday', 'date' => "$year-04-05", 'description' => 'US Observance - April 5, 2026'],
            ['name' => 'Mother\'s Day', 'date' => "$year-05-10", 'description' => 'US Observance - May 10, 2026'],
            ['name' => 'Father\'s Day', 'date' => "$year-06-21", 'description' => 'US Observance - June 21, 2026'],
            ['name' => 'Halloween', 'date' => "$year-10-31", 'description' => 'US Observance'],
            ['name' => 'Black Friday', 'date' => "$year-11-27", 'description' => 'US Shopping Holiday - Day after Thanksgiving'],
            ['name' => 'Christmas Eve (US)', 'date' => "$year-12-24", 'description' => 'US Observance'],
            ['name' => 'New Year\'s Eve (US)', 'date' => "$year-12-31", 'description' => 'US Observance'],
        ];

        // =====================================================
        // INSERT HOLIDAYS
        // =====================================================

        // Insert Philippine Regular Holidays
        foreach ($phRegularHolidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'name' => $holiday['name']],
                [
                    'type' => 'regular',
                    'is_recurring' => true,
                    'description' => $holiday['description'],
                ]
            );
        }

        // Insert Philippine Special Non-Working Holidays
        foreach ($phSpecialHolidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'name' => $holiday['name']],
                [
                    'type' => 'special',
                    'is_recurring' => !str_contains($holiday['name'], 'Chinese') && 
                                     !str_contains($holiday['name'], 'Black Saturday'),
                    'description' => $holiday['description'],
                ]
            );
        }

        // Insert Philippine Special Working Holidays
        foreach ($phSpecialWorkingHolidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'name' => $holiday['name']],
                [
                    'type' => 'special_working',
                    'is_recurring' => false,
                    'description' => $holiday['description'],
                ]
            );
        }

        // Insert US Federal Holidays
        foreach ($usHolidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'name' => $holiday['name']],
                [
                    'type' => 'regular',
                    'is_recurring' => true,
                    'description' => $holiday['description'],
                ]
            );
        }

        // Insert US Special/Observance Holidays
        foreach ($usSpecialHolidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date'], 'name' => $holiday['name']],
                [
                    'type' => 'special',
                    'is_recurring' => !str_contains($holiday['description'], 'varies'),
                    'description' => $holiday['description'],
                ]
            );
        }

        $this->command->info('Philippine and US holidays for ' . $year . ' seeded successfully!');
        $this->command->info('Total holidays: ' . Holiday::count());
    }
}
