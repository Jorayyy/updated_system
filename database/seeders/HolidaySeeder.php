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
            ['name' => 'Maundy Thursday', 'date' => "$year-04-17", 'description' => 'Philippine Regular Holiday - Date varies yearly'],
            ['name' => 'Good Friday', 'date' => "$year-04-18", 'description' => 'Philippine Regular Holiday - Date varies yearly'],
            ['name' => 'Labor Day', 'date' => "$year-05-01", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Independence Day', 'date' => "$year-06-12", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'National Heroes Day', 'date' => "$year-08-26", 'description' => 'Philippine Regular Holiday - Last Monday of August'],
            ['name' => 'Bonifacio Day', 'date' => "$year-11-30", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Christmas Day', 'date' => "$year-12-25", 'description' => 'Philippine Regular Holiday'],
            ['name' => 'Rizal Day', 'date' => "$year-12-30", 'description' => 'Philippine Regular Holiday'],
        ];

        // Philippine Special Non-Working Holidays (30% premium pay)
        $phSpecialHolidays = [
            ['name' => 'Chinese New Year', 'date' => "$year-01-29", 'description' => 'Philippine Special Non-Working Holiday - Date varies yearly'],
            ['name' => 'EDSA People Power Revolution Anniversary', 'date' => "$year-02-25", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Black Saturday', 'date' => "$year-04-19", 'description' => 'Philippine Special Non-Working Holiday - Date varies yearly'],
            ['name' => 'Ninoy Aquino Day', 'date' => "$year-08-21", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'All Saints\' Day', 'date' => "$year-11-01", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'All Souls\' Day', 'date' => "$year-11-02", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Feast of the Immaculate Conception', 'date' => "$year-12-08", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Christmas Eve', 'date' => "$year-12-24", 'description' => 'Philippine Special Non-Working Holiday'],
            ['name' => 'Last Day of the Year', 'date' => "$year-12-31", 'description' => 'Philippine Special Non-Working Holiday'],
        ];

        // Philippine Special Working Holidays
        $phSpecialWorkingHolidays = [
            ['name' => 'Eid\'l Fitr (End of Ramadan)', 'date' => "$year-03-31", 'description' => 'Philippine Special Working Holiday - Date varies yearly'],
            ['name' => 'Eid\'l Adha (Feast of Sacrifice)', 'date' => "$year-06-07", 'description' => 'Philippine Special Working Holiday - Date varies yearly'],
        ];

        // =====================================================
        // UNITED STATES HOLIDAYS
        // =====================================================
        
        // US Federal Holidays
        $usHolidays = [
            ['name' => 'New Year\'s Day (US)', 'date' => "$year-01-01", 'description' => 'US Federal Holiday'],
            ['name' => 'Martin Luther King Jr. Day', 'date' => "$year-01-20", 'description' => 'US Federal Holiday - Third Monday of January'],
            ['name' => 'Presidents\' Day', 'date' => "$year-02-17", 'description' => 'US Federal Holiday - Third Monday of February'],
            ['name' => 'Memorial Day', 'date' => "$year-05-26", 'description' => 'US Federal Holiday - Last Monday of May'],
            ['name' => 'Juneteenth National Independence Day', 'date' => "$year-06-19", 'description' => 'US Federal Holiday'],
            ['name' => 'Independence Day (US)', 'date' => "$year-07-04", 'description' => 'US Federal Holiday'],
            ['name' => 'Labor Day (US)', 'date' => "$year-09-01", 'description' => 'US Federal Holiday - First Monday of September'],
            ['name' => 'Columbus Day / Indigenous Peoples\' Day', 'date' => "$year-10-13", 'description' => 'US Federal Holiday - Second Monday of October'],
            ['name' => 'Veterans Day', 'date' => "$year-11-11", 'description' => 'US Federal Holiday'],
            ['name' => 'Thanksgiving Day', 'date' => "$year-11-27", 'description' => 'US Federal Holiday - Fourth Thursday of November'],
            ['name' => 'Christmas Day (US)', 'date' => "$year-12-25", 'description' => 'US Federal Holiday'],
        ];

        // US Common Non-Federal Holidays (Special)
        $usSpecialHolidays = [
            ['name' => 'Valentine\'s Day', 'date' => "$year-02-14", 'description' => 'US Observance'],
            ['name' => 'St. Patrick\'s Day', 'date' => "$year-03-17", 'description' => 'US Observance'],
            ['name' => 'Easter Sunday', 'date' => "$year-04-20", 'description' => 'US Observance - Date varies yearly'],
            ['name' => 'Mother\'s Day', 'date' => "$year-05-11", 'description' => 'US Observance - Second Sunday of May'],
            ['name' => 'Father\'s Day', 'date' => "$year-06-15", 'description' => 'US Observance - Third Sunday of June'],
            ['name' => 'Halloween', 'date' => "$year-10-31", 'description' => 'US Observance'],
            ['name' => 'Black Friday', 'date' => "$year-11-28", 'description' => 'US Shopping Holiday - Day after Thanksgiving'],
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
