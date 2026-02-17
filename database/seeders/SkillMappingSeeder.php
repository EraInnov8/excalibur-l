<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Reads mapping.csv and upserts into skill_mappings.
     * Uses unique personality columns to avoid duplicates on re-run.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/mapping.csv');

        if (! file_exists($path)) {
            $this->command->error("CSV file not found: {$path}");
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->command->error("Could not open CSV: {$path}");
            return;
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            $this->command->error('CSV file is empty or invalid.');
            return;
        }

        $expected = ['energy', 'orientation', 'structure', 'drive', 'reaction', 'skill_1', 'skill_2', 'skill_3', 'skill_4', 'skill_5'];
        if ($headers !== $expected) {
            fclose($handle);
            $this->command->error('CSV headers do not match expected: ' . implode(', ', $expected));
            return;
        }

        $inserted = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 10) {
                continue;
            }

            $data = array_combine($headers, array_slice($row, 0, 10));
            if ($data === false) {
                continue;
            }

            $exists = DB::table('skill_mappings')
                ->where('energy', $data['energy'])
                ->where('orientation', $data['orientation'])
                ->where('structure', $data['structure'])
                ->where('drive', $data['drive'])
                ->where('reaction', $data['reaction'])
                ->exists();

            $now = now();

            if ($exists) {
                $data['updated_at'] = $now;
                DB::table('skill_mappings')
                    ->where('energy', $data['energy'])
                    ->where('orientation', $data['orientation'])
                    ->where('structure', $data['structure'])
                    ->where('drive', $data['drive'])
                    ->where('reaction', $data['reaction'])
                    ->update($data);
                $updated++;
            } else {
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                DB::table('skill_mappings')->insert($data);
                $inserted++;
            }
        }

        fclose($handle);
        $this->command->info("Skill mappings: {$inserted} inserted, {$updated} updated.");
    }
}
