<?php

use App\Http\Controllers\PhoneRequestController;
use App\Http\Controllers\SurveyAccessController;
use App\Http\Controllers\SurveyEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/phone-requests', [PhoneRequestController::class, 'store'])
    ->name('phone-requests.store');

Route::post('/survey/start', [SurveyAccessController::class, 'start'])
    ->name('survey.start');

Route::post('/survey/decline', [SurveyAccessController::class, 'decline'])
    ->name('survey.decline');

Route::post('/survey/events', [SurveyEventController::class, 'store'])
    ->name('survey-events.store');
