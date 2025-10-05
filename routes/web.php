<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/pricing', [FrontController::class, 'pricing'])->name('front.pricing');

Route::match(['get', 'post'], '/booking/payment/midtrans/notification', [FrontController::class, 'payment_midtrans_notification'])->name('front.payment.midtrans.notification');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:student')->group(function () {
        // Subscription
        Route::get('/dashboard/subscriptions/', [DashboardController::class, 'subscriptions'])->name('dashboard.subscriptions');
        Route::get('/dashboard/subscriptions/{transaction}', [DashboardController::class, 'subscription_detail'])->name('dashboard.subscription.detail');

        // Course
        Route::get('/dashboard/courses', [CourseController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/courses/{course:slug}', [CourseController::class, 'detail'])->name('dashboard.course.detail');
        Route::get('/dashboard/search/courses', [CourseController::class, 'search_courses'])->name('dashboard.search.course');

        // 
        Route::middleware(['check.subscription'])->group(function () {
            Route::get('/dashboard/join/{course:slug}', [CourseController::class, 'join'])->name('dashboard.course.join');
            Route::get('/dashboard/learning/{course:slug}/{courseSection}/{sectionContent}', [CourseController::class, 'learning'])->name('dashboard.course.learning');
            Route::get('/dashboard/learning/{course:slug}/finished', [CourseController::class, 'learning_finished'])->name('dashboard.course.learning.finished');
        });

        // Checkout
        Route::get('/checkout/success', [FrontController::class, 'checkout_success'])->name('front.checkout.success');
        Route::get('/checkout/{pricing}', [FrontController::class, 'checkout'])->name('front.checkout');

        // midtrans
        Route::get('/booking/payment/midtrans', [FrontController::class, 'payment_store_midtrans'])->name('front.payment.store.midtrans');
    });
});

require __DIR__ . '/auth.php';
