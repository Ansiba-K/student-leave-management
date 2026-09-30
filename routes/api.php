<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeaveController;
use  App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// leave routes
Route::post('/leaves', [LeaveController::class, 'store']);
Route::get('/leaves/student/{student_id}', [LeaveController::class, 'studentLeaves']);
Route::put('/leaves/student/{student_id}/cancel', [LeaveController::class, 'cancel']);

// staff routes
Route::prefix('staff')->group(function () {
    // Staff leave management
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::put('/leaves/{id}', [LeaveController::class, 'updateStatus']);

    // Staff CRUD
    Route::post('/', [StaffController::class, 'store']);
    Route::get('/', [StaffController::class, 'index']);
    Route::get('/{id}', [StaffController::class, 'show']);
    Route::put('/{id}', [StaffController::class, 'update']);
    Route::delete('/{id}', [StaffController::class, 'destroy']);
});

// department routes
Route::prefix('departments')->group(function () {
    Route::post('/', [DepartmentController::class, 'store']);
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::put('/{id}', [DepartmentController::class, 'update']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
});


// Student routes
Route::prefix('students')->group(function () {

    Route::post('/', [StudentController::class, 'store']);
    Route::get('/', [StudentController::class, 'index']);
    Route::get('/{id}', [StudentController::class, 'show']);
    Route::put('/{id}', [StudentController::class, 'update']);
    Route::delete('/{id}', [StudentController::class, 'destroy']);
});

