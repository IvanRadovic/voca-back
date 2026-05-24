<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AssistantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\MentorController;
use App\Http\Controllers\Api\NvoController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SavedCallController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/nvo', [AuthController::class, 'registerNvo']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/stats', [DashboardController::class, 'platformStats']);

Route::get('/calls', [CallController::class, 'index']);
Route::get('/calls/{call}', [CallController::class, 'show']);
Route::get('/calls/{call}/similar', [CallController::class, 'similar']);
Route::get('/calls/{call}/feedbacks', [FeedbackController::class, 'index']);

Route::get('/nvos/{nvo}', [NvoController::class, 'show']);

Route::get('/certificates/{code}', [CertificateController::class, 'show']);
Route::get('/certificates/{code}/pdf', [CertificateController::class, 'download']);

Route::get('/leaderboard', [GamificationController::class, 'leaderboard']);

Route::get('/calls/{call}/stories', [StoryController::class, 'index']);
Route::get('/stories/recent', [StoryController::class, 'recent']);

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);

Route::get('/mentors', [MentorController::class, 'index']);
Route::get('/mentors/{mentor}', [MentorController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile', [ProfileController::class, 'update']); // multipart avatar upload
    Route::put('/profile/nvo', [ProfileController::class, 'updateNvo']);

    // Youth: personalized feed & applications
    Route::get('/feed', [CallController::class, 'recommendations']);
    Route::post('/calls/{call}/apply', [ApplicationController::class, 'store']);
    Route::delete('/calls/{call}/apply', [ApplicationController::class, 'destroy']);
    Route::get('/my/applications', [ApplicationController::class, 'myApplications']);

    // Wishlist
    Route::get('/my/saved', [SavedCallController::class, 'index']);
    Route::post('/calls/{call}/save', [SavedCallController::class, 'toggle']);

    // Feedback
    Route::post('/calls/{call}/feedbacks', [FeedbackController::class, 'store']);
    Route::get('/my/feedbacks', [FeedbackController::class, 'mine']);
    Route::get('/my/certificates', [CertificateController::class, 'mine']);
    Route::get('/my/stories', [StoryController::class, 'mine']);
    Route::post('/calls/{call}/stories', [StoryController::class, 'store']);
    Route::get('/me/gamification', [GamificationController::class, 'me']);
    Route::post('/mentors/{mentor}/request', [MentorController::class, 'requestSession']);

    // AI assistant
    Route::post('/ai/cover-letter', [AssistantController::class, 'coverLetter']);
    Route::post('/ai/cv', [AssistantController::class, 'cv']);
    Route::post('/ai/assistant', [AssistantController::class, 'chat']);

    /*
    |----------------------------------------------------------------------
    | NVO-only routes
    |----------------------------------------------------------------------
    */
    Route::middleware('nvo')->group(function () {
        Route::get('/my/posts', [PostController::class, 'mine']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::post('/posts/{post}', [PostController::class, 'update']); // multipart
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);

        Route::get('/nvo/stats', [DashboardController::class, 'nvoStats']);
        Route::get('/nvo/analytics', [DashboardController::class, 'nvoAnalytics']);
        Route::get('/nvo/calls', [CallController::class, 'myCalls']);
        Route::post('/calls', [CallController::class, 'store']);
        Route::put('/calls/{call}', [CallController::class, 'update']);
        Route::post('/calls/{call}', [CallController::class, 'update']); // multipart image upload
        Route::delete('/calls/{call}', [CallController::class, 'destroy']);

        Route::get('/calls/{call}/applicants', [ApplicationController::class, 'applicants']);
        Route::get('/calls/{call}/applicants/export', [ApplicationController::class, 'exportApplicants']);
        Route::put('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
        Route::post('/calls/{call}/announce', [ApplicationController::class, 'announce']);
    });
});
