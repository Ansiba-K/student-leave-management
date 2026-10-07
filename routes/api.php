<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeaveController;
use  App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\LeaveBalanceController;
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
Route::post('student/leaves', [LeaveController::class, 'storeStudentLeave']);
Route::get('/leaves/student/{student_id}', [LeaveController::class, 'studentLeaves']);
Route::put('/leaves/student/{student_id}/cancel', [LeaveController::class, 'cancelStudentLeave']);


// staff routes
Route::prefix('staff')->group(function () {
    // Staff leave management
    Route::post('/leaves', [LeaveController::class, 'storeStaffLeave']);
    Route::get('/student-leaves', [LeaveController::class, 'allStudentLeaves']);
    Route::get('/staff-leaves', [LeaveController::class, 'allStaffLeaves']);
    Route::get('/leaves/{staff_id}', [LeaveController::class, 'staffLeaves']);
    Route::put('/leaves/{staff_id}/cancel', [LeaveController::class, 'cancelStaffLeave']);
    // approve or reject leave
    Route::put('/leaves/{id}', [LeaveController::class, 'updateStatus']);
    // leave balance management
    Route::post('/leave-balances', [LeaveBalanceController::class, 'store']);
    

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

