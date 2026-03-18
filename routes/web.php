<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\UserController;


// ─── Public ──────────────────────────────────────────────────────────────────
Route::post('/login', [UserController::class, 'login'])->name('api.login');
Route::post('/register', [UserController::class, 'register'])->name('api.register');
Route::get('/verify-email', [UserController::class, 'verifyEmail'])->name('api.verify-email');
Route::post('/forgot-password', [UserController::class, 'forgotPassword'])->name('api.forgot-password');
Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('api.reset-password');
Route::get('/me', [UserController::class, 'me'])->name('api.me');
Route::get('/counts', [AdminController::class, 'getCounts'])->name('api.counts');
Route::get('/audio/{filename}', [SpeechController::class, 'stream'])->where('filename', '.*');

Route::post('/locations', [LocationController::class, 'getLocations'])->name('api.location.list');
Route::post('/speakers', [SpeakerController::class, 'getSpeakers'])->name('api.speaker.list');
Route::post('/speeches', [SpeechController::class, 'getSpeeches'])->name('api.speech.list');

Route::get('/categories', [StatisticController::class, 'getCategories'])->name('api.category.list');
Route::get('/languages', [StatisticController::class, 'getLanguages'])->name('api.language.list');
Route::get('/insights', [StatisticController::class, 'getInsights'])->name('api.insights');
Route::get('/words', [StatisticController::class, 'getWords'])->name('api.word.list');
Route::get('/quizzes', [StatisticController::class, 'getQuizzes'])->name('api.quiz.list');

Route::get('/speech/{id}', [SpeechController::class, 'getSpeech'])->name('api.speech.get');
Route::put('/speech/{id}', [SpeechController::class, 'editSpeech'])->name('api.speech.edit');
Route::get('/location/{id}', [LocationController::class, 'getLocation'])->name('api.location.get');
Route::get('/language/{id}', [StatisticController::class, 'getLanguage'])->name('api.language.get');
Route::get('/category/{id}', [StatisticController::class, 'getCategory'])->name('api.category.get');
Route::get('/options', [AdminController::class, 'getOptions'])->name('api.option.get');

// ─── Authenticated users (User or Admin) ─────────────────────────────────────
Route::middleware(['auth', 'role:User|Admin'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('api.logout');

    Route::post('/speaker', [SpeakerController::class, 'addSpeaker'])->name('api.speaker.add');
    Route::get('/speaker/{id}', [SpeakerController::class, 'getSpeaker'])->name('api.speaker.get');
    Route::put('/speaker/{id}', [SpeakerController::class, 'editSpeaker'])->name('api.speaker.edit');
    Route::delete('/speaker/{id}', [SpeakerController::class, 'removeSpeaker'])->name('api.speaker.remove');

    Route::post('/speech', [SpeechController::class, 'addSpeech'])->name('api.speech.add');
    Route::delete('/speech/{id}', [SpeechController::class, 'removeSpeech'])->name('api.speech.remove');

    Route::get('/user/{id}', [UserController::class, 'getUser'])->name('api.user.get');
    Route::put('/user/{id}', [UserController::class, 'editUser'])->name('api.user.edit');
    Route::put('/user/{id}/password', [UserController::class, 'changePassword'])->name('api.user.changePassword');
});


// ─── Admin only ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::post('/users', [UserController::class, 'getUsers'])->name('api.user.list');
    Route::post('/user', [UserController::class, 'addUser'])->name('api.user.add');
    Route::delete('/user/{id}', [UserController::class, 'removeUser'])->name('api.user.remove');
    Route::put('/user/{id}/role', [UserController::class, 'assignRole'])->name('api.user.assignRole');

    Route::post('/location', [LocationController::class, 'addLocation'])->name('api.location.add');
    Route::put('/location/{id}', [LocationController::class, 'editLocation'])->name('api.location.edit');
    Route::delete('/location/{id}', [LocationController::class, 'removeLocation'])->name('api.location.remove');

    Route::put('/option/{id}', [AdminController::class, 'editOption'])->name('api.option.edit');

    Route::post('/category', [StatisticController::class, 'addCategory'])->name('api.category.add');
    Route::put('/category/{id}', [StatisticController::class, 'editCategory'])->name('api.category.edit');
    Route::delete('/category/{id}', [StatisticController::class, 'removeCategory'])->name('api.category.remove');

    Route::post('/language', [StatisticController::class, 'addLanguage'])->name('api.language.add');
    Route::put('/language/{id}', [StatisticController::class, 'editLanguage'])->name('api.language.edit');
    Route::delete('/language/{id}', [StatisticController::class, 'removeLanguage'])->name('api.language.remove');

    Route::post('/word', [StatisticController::class, 'addWord'])->name('api.word.add');
    Route::put('/word/{id}', [StatisticController::class, 'editWord'])->name('api.word.edit');
    Route::delete('/word/{id}', [StatisticController::class, 'removeWord'])->name('api.word.remove');

    Route::post('/quiz', [StatisticController::class, 'addQuiz'])->name('api.quiz.add');
    Route::put('/quiz/{id}', [StatisticController::class, 'editQuiz'])->name('api.quiz.edit');
    Route::delete('/quiz/{id}', [StatisticController::class, 'removeQuiz'])->name('api.quiz.remove');
});
