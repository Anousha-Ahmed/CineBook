<?php

use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FailedJobController as AdminFailedJobController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ScreenController as AdminScreenController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ShowtimeController as AdminShowtimeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingHistoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
| Public
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movies', [HomeController::class, 'movies'])->name('movies.index');
Route::get('/movies/{movie}', [HomeController::class, 'show'])->name('movies.show');

Route::get('/booking/{showtime}/seats', [BookingController::class, 'showSeats'])->name('booking.seats');

Route::post('/stripe/webhook', [WebhookController::class, 'handle'])->name('stripe.webhook');

/*
| Account (any verified role)
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'staff' => redirect()->route('staff.scan'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('bookings.index'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
});

/*
| Customers (staff are read-only and never book)
*/
Route::middleware(['auth', 'verified', 'role:customer,admin'])->group(function () {
    Route::post('/booking/{showtime}/hold', [BookingController::class, 'holdSeats'])
        ->middleware('throttle:20,1')
        ->name('booking.hold');
    Route::post('/booking/{showtime}/release', [BookingController::class, 'release'])
        ->name('booking.release');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])
        ->middleware('throttle:10,1')
        ->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/bookings', [BookingHistoryController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingHistoryController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingHistoryController::class, 'cancel'])->name('bookings.cancel');
});

/*
| Staff
*/
Route::middleware(['auth', 'role:staff,admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/scan', [StaffController::class, 'index'])->name('scan');
    Route::post('/scan', [StaffController::class, 'verify'])
        ->middleware('throttle:30,1')
        ->name('scan.verify');
});

/*
| Admin
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/movies', [AdminMovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/create', [AdminMovieController::class, 'create'])->name('movies.create');
    Route::post('/movies', [AdminMovieController::class, 'store'])->name('movies.store');
    Route::get('/movies/{movie}/edit', [AdminMovieController::class, 'edit'])->name('movies.edit');
    Route::patch('/movies/{movie}', [AdminMovieController::class, 'update'])->name('movies.update');
    Route::delete('/movies/{movie}', [AdminMovieController::class, 'destroy'])->name('movies.destroy');

    Route::get('/screens', [AdminScreenController::class, 'index'])->name('screens.index');
    Route::get('/screens/create', [AdminScreenController::class, 'create'])->name('screens.create');
    Route::post('/screens', [AdminScreenController::class, 'store'])->name('screens.store');
    Route::get('/screens/{screen}/edit', [AdminScreenController::class, 'edit'])->name('screens.edit');
    Route::patch('/screens/{screen}', [AdminScreenController::class, 'update'])->name('screens.update');
    Route::delete('/screens/{screen}', [AdminScreenController::class, 'destroy'])->name('screens.destroy');
    Route::patch('/screens/{screen}/seats/{seat}', [AdminScreenController::class, 'toggleSeat'])->name('screens.seats.toggle');

    Route::get('/genres', [AdminGenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [AdminGenreController::class, 'store'])->name('genres.store');
    Route::patch('/genres/{genre}', [AdminGenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [AdminGenreController::class, 'destroy'])->name('genres.destroy');

    Route::get('/showtimes', [AdminShowtimeController::class, 'index'])->name('showtimes.index');
    Route::get('/showtimes/create', [AdminShowtimeController::class, 'create'])->name('showtimes.create');
    Route::post('/showtimes', [AdminShowtimeController::class, 'store'])->name('showtimes.store');
    Route::get('/showtimes/{showtime}/edit', [AdminShowtimeController::class, 'edit'])->name('showtimes.edit');
    Route::patch('/showtimes/{showtime}', [AdminShowtimeController::class, 'update'])->name('showtimes.update');
    Route::post('/showtimes/{showtime}/cancel', [AdminShowtimeController::class, 'cancel'])->name('showtimes.cancel');
    Route::delete('/showtimes/{showtime}', [AdminShowtimeController::class, 'destroy'])->name('showtimes.destroy');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::patch('/customers/{user}', [AdminCustomerController::class, 'update'])->name('customers.update');

    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales.csv', [AdminReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/bookings.csv', [AdminReportController::class, 'bookingsExport'])->name('reports.bookings');

    Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit.index');

    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    Route::get('/failed-jobs', [AdminFailedJobController::class, 'index'])->name('failed-jobs.index');
    Route::post('/failed-jobs/{uuid}/retry', [AdminFailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::delete('/failed-jobs/{uuid}', [AdminFailedJobController::class, 'destroy'])->name('failed-jobs.destroy');
});

require __DIR__.'/auth.php';
