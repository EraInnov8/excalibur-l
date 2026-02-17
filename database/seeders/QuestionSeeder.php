<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    private const EXPECTED_HEADERS = ['dimension', 'trait', 'text'];

    /**
     * Trait normalization: CSV values → skill_mappings / API expected values.
     */
    private const TRAIT_MAP = [
        'Introvert' => 'Introverted',
        'Extrovert' => 'Extroverted',
        'Organised' => 'Organized',
    ];

    /**
     * Import questions from CSV into the questions table.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/questions.csv');

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

        $headers = array_map('trim', array_map('strtolower', $headers));
        $expected = array_map('strtolower', self::EXPECTED_HEADERS);
        if ($headers !== $expected) {
            fclose($handle);
            $this->command->error('CSV headers do not match expected: dimension,trait,text (case-insensitive)');
            return;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) {
                $skipped++;
                continue;
            }

            $dimension = trim($row[0] ?? '');
            $trait = trim($row[1] ?? '');
            $text = trim($row[2] ?? '');

            if ($dimension === '' || $trait === '' || $text === '') {
                $skipped++;
                continue;
            }

            $dimension = strtolower($dimension);
            $trait = self::TRAIT_MAP[$trait] ?? $trait;

            $created = Question::firstOrCreate(
                [
                    'dimension' => $dimension,
                    'trait' => $trait,
                    'text' => $text,
                ],
                ['dimension' => $dimension, 'trait' => $trait, 'text' => $text]
            );

            if ($created->wasRecentlyCreated) {
                $imported++;
            }
        }

        fclose($handle);
        $this->command->info("Questions imported: {$imported}, skipped: {$skipped}");
    }
}
