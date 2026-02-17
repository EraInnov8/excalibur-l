<?php

use App\Http\Controllers\Api\AssessmentController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/questions', [AssessmentController::class, 'questions']);
Route::post('/assessment/submit', [AssessmentController::class, 'submit']);
Route::post('/manual/generate', [AssessmentController::class, 'manualGenerate']);
