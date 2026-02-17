<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * GET /api/questions
     * Returns up to 50 questions: 5 per trait, shuffled.
     * Returns available questions if insufficient exist.
     */
    public function questions(): JsonResponse
    {
        $dimensions = ['energy', 'orientation', 'structure', 'drive', 'reaction'];
        $traitsByDimension = [
            'energy' => ['Introverted', 'Extroverted'],
            'orientation' => ['Practical', 'Imaginative'],
            'structure' => ['Organized', 'Spontaneous'],
            'drive' => ['Cooperative', 'Competitive'],
            'reaction' => ['Reflective', 'Responsive'],
        ];

        $questions = collect();

        foreach ($dimensions as $dimension) {
            foreach ($traitsByDimension[$dimension] as $trait) {
                $selected = Question::where('dimension', $dimension)
                    ->where('trait', $trait)
                    ->inRandomOrder()
                    ->limit(5)
                    ->get();
                $questions = $questions->concat($selected);
            }
        }

        $questions = $questions->shuffle()->values();

        return response()->json([
            'questions' => $questions->map(fn (Question $q) => [
                'id' => $q->id,
                'dimension' => $q->dimension,
                'trait' => $q->trait,
                'text' => $q->text,
            ]),
        ]);
    }

    /**
     * POST /api/assessment/submit
     * Calculates personality profile and returns matching skills.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.score' => 'required|integer|min:1|max:5',
        ]);

        $answers = $validated['answers'];
        $questionIds = array_column($answers, 'question_id');
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        $scoresByDimension = [];
        foreach ($answers as $a) {
            $q = $questions->get($a['question_id']);
            if (! $q) {
                continue;
            }
            $dim = $q->dimension;
            $trait = $q->trait;
            if (! isset($scoresByDimension[$dim][$trait])) {
                $scoresByDimension[$dim][$trait] = 0;
            }
            $scoresByDimension[$dim][$trait] += (int) $a['score'];
        }

        $personality = [];
        $dimensions = ['energy', 'orientation', 'structure', 'drive', 'reaction'];
        $traitsByDimension = [
            'energy' => ['Introverted', 'Extroverted'],
            'orientation' => ['Practical', 'Imaginative'],
            'structure' => ['Organized', 'Spontaneous'],
            'drive' => ['Cooperative', 'Competitive'],
            'reaction' => ['Reflective', 'Responsive'],
        ];

        foreach ($dimensions as $dim) {
            $scores = $scoresByDimension[$dim] ?? [];
            $traits = $traitsByDimension[$dim];
            $aScore = $scores[$traits[0]] ?? 0;
            $bScore = $scores[$traits[1]] ?? 0;

            if ($aScore > $bScore) {
                $personality[$dim] = $traits[0];
            } elseif ($bScore > $aScore) {
                $personality[$dim] = $traits[1];
            } else {
                $personality[$dim] = 'Balanced';
            }
        }

        $mapping = DB::table('skill_mappings')
            ->where('energy', $personality['energy'])
            ->where('orientation', $personality['orientation'])
            ->where('structure', $personality['structure'])
            ->where('drive', $personality['drive'])
            ->where('reaction', $personality['reaction'])
            ->first();

        $skills = [];
        if ($mapping) {
            $skills = array_filter([
                $mapping->skill_1,
                $mapping->skill_2,
                $mapping->skill_3,
                $mapping->skill_4,
                $mapping->skill_5,
            ]);
        }

        return response()->json([
            'personality' => [
                'energy' => $personality['energy'],
                'orientation' => $personality['orientation'],
                'structure' => $personality['structure'],
                'drive' => $personality['drive'],
                'reaction' => $personality['reaction'],
            ],
            'skills' => array_values($skills),
        ]);
    }

    /**
     * POST /api/manual/generate
     * Generate learning path from manually selected traits (no assessment).
     */
    public function manualGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'energy' => 'required|string|in:Introverted,Extroverted,Balanced',
            'orientation' => 'required|string|in:Practical,Imaginative,Balanced',
            'structure' => 'required|string|in:Organized,Spontaneous,Balanced',
            'drive' => 'required|string|in:Cooperative,Competitive,Balanced',
            'reaction' => 'required|string|in:Reflective,Responsive,Balanced',
        ]);

        $mapping = DB::table('skill_mappings')
            ->where('energy', $validated['energy'])
            ->where('orientation', $validated['orientation'])
            ->where('structure', $validated['structure'])
            ->where('drive', $validated['drive'])
            ->where('reaction', $validated['reaction'])
            ->first();

        $skills = [];
        if ($mapping) {
            $skills = array_filter([
                $mapping->skill_1,
                $mapping->skill_2,
                $mapping->skill_3,
                $mapping->skill_4,
                $mapping->skill_5,
            ]);
        }

        return response()->json([
            'personality' => [
                'energy' => $validated['energy'],
                'orientation' => $validated['orientation'],
                'structure' => $validated['structure'],
                'drive' => $validated['drive'],
                'reaction' => $validated['reaction'],
            ],
            'skills' => array_values($skills),
        ]);
    }
}
