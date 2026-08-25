<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangayController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\GrowthForecastController;
use App\Http\Controllers\Api\HealthRecordController;
use App\Http\Controllers\Api\InterventionController;
use App\Http\Controllers\Api\NutritionRecommendationController;
use App\Http\Controllers\Api\RecoveryPredictionController;
use App\Http\Controllers\Api\RiskMapController;
use App\Http\Controllers\Api\RiskPredictionController;
use App\Http\Controllers\Api\RiskTrendController;
use App\Http\Controllers\Api\TimelineController;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------------- Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// ----------------------------------------------------- Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/barangays', [BarangayController::class, 'index']);
    Route::post('/barangays', [BarangayController::class, 'store']);

    Route::get('/families', [FamilyController::class, 'index']);
    Route::post('/families', [FamilyController::class, 'store']);
    Route::get('/families/{family}', [FamilyController::class, 'show']);
    Route::patch('/families/{family}', [FamilyController::class, 'update']);

    Route::get('/children', [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    Route::get('/children/{child}', [ChildController::class, 'show']);
    Route::patch('/children/{child}/deactivate', [ChildController::class, 'deactivate']);

    Route::get('/health-records', [HealthRecordController::class, 'index']);
    Route::post('/health-records', [HealthRecordController::class, 'store']);

    Route::get('/predictions', [RiskPredictionController::class, 'index']);
    Route::post('/predictions/{child}/run', [RiskPredictionController::class, 'run']);

    Route::get('/recommendations', [NutritionRecommendationController::class, 'index']);

    Route::get('/growth-forecasts', [GrowthForecastController::class, 'index']);
    Route::post('/growth-forecasts/{child}/run', [GrowthForecastController::class, 'run']);

    Route::get('/interventions', [InterventionController::class, 'index']);
    Route::post('/interventions', [InterventionController::class, 'store']);
    Route::patch('/interventions/{intervention}/status', [InterventionController::class, 'updateStatus']);
    Route::post('/interventions/{intervention}/accept-suggestion', [InterventionController::class, 'acceptSuggestion']);

    Route::get('/risk-map', [RiskMapController::class, 'index']);

    Route::get('/timeline/{childId}', [TimelineController::class, 'show']);

    Route::get('/follow-ups', [FollowUpController::class, 'index']);
    Route::patch('/follow-ups/{followUp}', [FollowUpController::class, 'update']);

    Route::get('/recovery-predictions', [RecoveryPredictionController::class, 'index']);
    Route::post('/recovery-predictions/{child}/run', [RecoveryPredictionController::class, 'run']);

    Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage']);

    Route::get('/risk-trend/{childId}', [RiskTrendController::class, 'show']);
});