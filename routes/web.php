<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes([
    'register' => false,
]);

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('profile', ProfileController::class)->name('profile');
    Route::resource('employees', EmployeeController::class);
});

// lokal disk
Route::get('/local-disk', function() {
    Storage::disk('local')->put('local-example.txt', 'This is local example content');
    return asset('storage/local-example.txt');
});

// publik disk
Route::get('/public-disk', function() {
    Storage::disk('public')->put('public-example.txt', 'This is public example content');
    return asset('storage/public-example.txt');
});

// isi file local
Route::get('/retrieve-local-file', function() {
    if (Storage::disk('local')->exists('local-example.txt')) {
        $contents = Storage::disk('local')->get('local-example.txt');
    } else {
        $contents = 'File does not exist';
    }

    return $contents;
});

// isi file public
Route::get('/retrieve-public-file', function() {
    if (Storage::disk('public')->exists('public-example.txt')) {
        $contents = Storage::disk('public')->get('public-example.txt');
    } else {
        $contents = 'File does not exist';
    }

    return $contents;
});

// download file local
Route::get('/download-local-file', function() {
    return Storage::download('local-example.txt', 'local file');
});

// download file public
Route::get('/download-public-file', function() {
    return Storage::download('public/public-example.txt', 'public file');
});

// file url
Route::get('/file-url', function() {
    // Just prepend "/storage" to the given path and return a relative URL
    $url = Storage::url('local-example.txt');
    return $url;
});

// file size
Route::get('/file-size', function() {
    $size = Storage::size('local-example.txt');
    return $size;
});

// file path
Route::get('/file-path', function() {
    $path = Storage::path('local-example.txt');
    return $path;
});

// upload file
Route::get('/upload-example', function() {
    return view('upload_example');
});

// upload file
Route::post('/upload-example', function(Request $request) {
    $path = $request->file('avatar')->store('public');
    return $path;
})->name('upload-example');

// Delete
Route::get('/delete-local-file', function(Request $request) {
    Storage::disk('local')->delete('local-example.txt');
    return 'Deleted';
});

Route::get('/delete-public-file', function(Request $request) {
    Storage::disk('public')->delete('public-example.txt');
    return 'Deleted';
});

// rute download file
Route::get('download-file/{employeeId}', [EmployeeController::class, 'downloadFile'])->name('employees.downloadFile');

// server side
Route::get('getEmployees', [EmployeeController::class, 'getData'])->name('employees.getData');

// excel
Route::get('exportExcel', [EmployeeController::class, 'exportExcel'])->name('employees.exportExcel');

// pdf
Route::get('exportPdf', [EmployeeController::class, 'exportPdf'])->name('employees.exportPdf');

// halaman Home
// Route::get('home', [HomeController::class, 'index'])->name('home');

// halaman Profile
// Route::get('profile', ProfileController::class)->name('profile');

// halaman employee list
// Route::resource('employees', EmployeeController::class);

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
