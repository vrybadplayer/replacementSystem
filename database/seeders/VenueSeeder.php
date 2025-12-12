<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $venues = [
            ['venueId' => 'V001', 'venueType' => 'Lecture Hall', 'pax' => 300, 'equipment' => 'projector'],
            ['venueId' => 'V002', 'venueType' => 'Lecture Hall', 'pax' => 250, 'equipment' => 'projector'],
            ['venueId' => 'V003', 'venueType' => 'Lecture Hall', 'pax' => 200, 'equipment' => 'projector'],
            ['venueId' => 'V004', 'venueType' => 'Lecture Hall', 'pax' => 350, 'equipment' => 'projector'],
            ['venueId' => 'V005', 'venueType' => 'Lecture Hall', 'pax' => 230, 'equipment' => 'projector'],
            ['venueId' => 'V006', 'venueType' => 'IoT Lab',      'pax' => 20,  'equipment' => 'iot kit'],
            ['venueId' => 'V007', 'venueType' => 'Network Lab',  'pax' => 25,  'equipment' => 'routers, switches'],
            ['venueId' => 'V008', 'venueType' => 'Tutorial Room','pax' => 30,  'equipment' => 'projector'],
            ['venueId' => 'V009', 'venueType' => 'Tutorial Room','pax' => 25,  'equipment' => 'projector'],
            ['venueId' => 'V010', 'venueType' => 'Tutorial Room','pax' => 20,  'equipment' => 'projector'],
            ['venueId' => 'V011', 'venueType' => 'Computer Lab', 'pax' => 20,  'equipment' => 'projector'],
            ['venueId' => 'V012', 'venueType' => 'Computer Lab', 'pax' => 30,  'equipment' => 'projector'],
            ['venueId' => 'V013', 'venueType' => 'Small Room',   'pax' => 15,  'equipment' => 'projector'],
            ['venueId' => 'V014', 'venueType' => 'Small Room',   'pax' => 10,  'equipment' => 'projector'],
            ['venueId' => 'V015', 'venueType' => 'IoT Lab',      'pax' => 18,  'equipment' => 'iot kit'],
            ['venueId' => 'V016', 'venueType' => 'Network Lab',  'pax' => 22,  'equipment' => 'routers, switches'],
            ['venueId' => 'V017', 'venueType' => 'Tutorial Room','pax' => 28,  'equipment' => 'projector'],
            ['venueId' => 'V018', 'venueType' => 'Small Room',   'pax' => 12,  'equipment' => 'projector'],
            ['venueId' => 'V019', 'venueType' => 'Computer Lab', 'pax' => 26,  'equipment' => 'projector'],
            ['venueId' => 'V020', 'venueType' => 'Tutorial Room','pax' => 30,  'equipment' => 'projector'],
        ];

        // Loop to add timestamps to every row automatically
        foreach ($venues as &$venue) {
            $venue['created_at'] = $now;
            $venue['updated_at'] = $now;
        }

        // Insert into database
        DB::table('venue')->insert($venues);
    }
}