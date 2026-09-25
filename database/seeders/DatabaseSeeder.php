<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Project;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        $work = Project::create(['name' => 'Work']);
        foreach (['Finish quarterly report', 'Reply to client emails', 'Prepare sprint demo'] as $i => $name) {
            $work->tasks()->create(['name' => $name, 'priority' => $i + 1]);
        }

        $personal = Project::create(['name' => 'Personal']);
        foreach (['Book dentist appointment', 'Plan weekend trip'] as $i => $name) {
            $personal->tasks()->create(['name' => $name, 'priority' => $i + 1]);
        }
    }
}