<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\AdminAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'book-manage'], function () {
    Route::get('add-shelf', 'BookManageController@add_shelf');
    Route::post('add-shelf-process', 'BookManageController@add_shelf_process');
    Route::get('update-shelf', 'BookManageController@update_shelf');
    Route::get('edit-shelf/{id}', 'BookManageController@edit_shelf');
    Route::post('edit-shelf-process/{id}', 'BookManageController@edit_shelf_process');
    Route::get('remove-shelf', 'BookManageController@remove_shelf');
    Route::get('remove-shelf-process/{id}', 'BookManageController@remove_shelf_process');
    Route::get('add-book', 'BookManageController@add_book');
    Route::post('add-book-process', 'BookManageController@add_book_process');
    Route::get('update-book', 'BookManageController@update_book');
    Route::get('edit-book/{id}', 'BookManageController@edit_book');
    Route::post('edit-book-process/{id}', 'BookManageController@edit_book_process');
    Route::get('remove-book', 'BookManageController@remove_book');
    Route::get('remove-book-process/{id}', 'BookManageController@remove_book_process');
    Route::get('book-order', 'BookManageController@book_order');
    Route::get('add-order', 'BookManageController@add_order');
    Route::post('add-order-process', 'BookManageController@add_order_process');
    Route::get('book-received', 'BookManageController@book_received');
    Route::get('book-received-process/{id}', 'BookManageController@book_received_process');
    Route::get('programming-book', 'BookManageController@programming_book');
    Route::get('networking-book', 'BookManageController@networking_book');
    Route::get('database-book', 'BookManageController@database_book');
});



// Routes for student management
Route::prefix('students')->group(function () {
    // Approve a student account
    Route::put('approve/{id}', 'App\Http\Controllers\StudentManageController@student_approve');

    // Reject a student account
    Route::delete('reject/{id}', 'App\Http\Controllers\StudentManageController@student_reject');

    // Remove a student account
    Route::delete('remove/{id}', 'App\Http\Controllers\StudentManageController@remove_student_process');

    // Get a list of approved students
    Route::get('approved', 'App\Http\Controllers\StudentManageController@student_info');

    // Get detailed information about a specific student
    Route::get('details/{id}', 'App\Http\Controllers\StudentManageController@student_details');
});

// Routes for notifications
Route::prefix('notifications')->group(function () {
    // Get pending notifications
    Route::get('/', 'App\Http\Controllers\StudentManageController@notification');

    // Get the count of pending notifications
    Route::get('count', 'App\Http\Controllers\StudentManageController@notify_count');
});

// Routes for student's collection and submission
Route::prefix('students')->group(function () {
    // Get a student's collection of books
    Route::get('{id}/collection', 'App\Http\Controllers\StudentManageController@my_collection');

    // Get a student's submitted books
    Route::get('{id}/submission', 'App\Http\Controllers\StudentManageController@my_submission');
});

Route::group(['prefix' => 'student'], function () {
    Route::get('/sign-up-show', [StudentAuthController::class, 'sign_up_show']);
    Route::post('/sign-up-process', [StudentAuthController::class, 'sign_up_process']);
    Route::get('/verify-email/{id}', [StudentAuthController::class, 'verify_email']);
    Route::post('/confirm-email', [StudentAuthController::class, 'confirm_email']);
    Route::get('/forget-password', [StudentAuthController::class, 'forget_password']);
    Route::post('/forget-password-process', [StudentAuthController::class, 'forget_password_process']);
    Route::get('/recover-password', [StudentAuthController::class, 'recover_password']);
    Route::post('/recover-password-process', [StudentAuthController::class, 'recover_password_process']);
    Route::post('/sign-in-process', [StudentAuthController::class, 'sign_in_process']);
});

Route::group(['middleware' => 'auth.student'], function () {
    Route::get('/student/dashboard', [StudentAuthController::class, 'dashboard']);
    Route::get('/student/log-out', [StudentAuthController::class, 'log_out']);
    Route::get('/student/change-password', [StudentAuthController::class, 'change_password']);
    Route::post('/student/change-password-process', [StudentAuthController::class, 'change_password_process']);
    Route::get('/student/edit-info', [StudentAuthController::class, 'edit_info']);
    Route::post('/student/edit-info-process/{id}', [StudentAuthController::class, 'edit_info_process']);
});


Route::group(['prefix' => 'admin'], function () {
    Route::get('/sign-in', [AdminAuthController::class, 'sign_in_show']);
    Route::post('/sign-in', [AdminAuthController::class, 'sign_in_process']);
    Route::get('/forget-password', [AdminAuthController::class, 'forget_password']);
    Route::post('/forget-password', [AdminAuthController::class, 'forget_password_process']);
    Route::get('/recover-password', [AdminAuthController::class, 'recover_password']);
    Route::post('/recover-password', [AdminAuthController::class, 'recover_password_process']);

    Route::group(['middleware' => 'auth:admin'], function () {
        Route::get('/dashboard', [AdminAuthController::class, 'dashboard']);
        Route::get('/log-out', [AdminAuthController::class, 'log_out']);
        Route::get('/student-request', [AdminAuthController::class, 'student_request']);
        Route::get('/change-password', [AdminAuthController::class, 'change_password']);
        Route::post('/change-password', [AdminAuthController::class, 'change_password_process']);
        Route::get('/edit-info', [AdminAuthController::class, 'edit_info']);
        Route::post('/update-info', [AdminAuthController::class, 'update_info_process']);
    });
});
