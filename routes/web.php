<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/bisaya-suchi/{subject}', [PublicController::class, 'subject'])->whereNumber('subject')->name('subjects.show');
Route::get('/bisaya-suchi', [PublicController::class, 'subjects'])->name('subjects');
Route::get('/places', [PublicController::class, 'places'])->name('places');
Route::get('/places/{subject}', [PublicController::class, 'place'])->whereNumber('subject')->name('places.show');
Route::get('/bansawali', [PublicController::class, 'tree'])->name('bansawali');
foreach (['gaihre-parichaya' => 'gaihre_intro', 'history' => 'history', 'about' => 'about', 'contact' => 'contact'] as $path => $key) {
    Route::get('/'.$path, [PublicController::class, 'page'])->defaults('key', $key)->name($path);
}
Route::get('/blog', [PublicController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PublicController::class, 'post'])->name('blog.show');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureAdmin::class])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/members/tree', [Admin\MemberController::class, 'tree'])->name('members.tree');
    Route::get('/places', [Admin\SubjectEntryController::class, 'places'])->name('places');
    Route::resource('subjects', Admin\SubjectEntryController::class);
    Route::resource('members', Admin\MemberController::class)->except('show');
    Route::resource('posts', Admin\PostController::class)->except('show');
    Route::get('/content', [Admin\ContentController::class, 'index'])->name('content.index');
    Route::get('/content/{page}/edit', [Admin\ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content/{page}', [Admin\ContentController::class, 'update'])->name('content.update');
    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});
