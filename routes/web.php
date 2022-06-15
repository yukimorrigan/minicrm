<?php

use Illuminate\Support\Facades\Route;
use App\Facades\LocalizationService;
use App\Http\Controllers\Crm\CompanyController;
use App\Http\Controllers\Crm\EmployeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'prefix' => LocalizationService::getLangPrefix(),
    'middleware' => ['auth', 'verified', 'set_language']
], function () {
    Route::get('/', function () {
        return redirect()->route('register');
    });
    Route::get('/companies/page',[CompanyController::class, 'page'])->name('companies.page');
    Route::get('/employees/page',[EmployeeController::class, 'page'])->name('employees.page');
    Route::resources([
        'companies' => CompanyController::class,
        'employees' => EmployeeController::class
    ]);
});

require __DIR__.'/auth.php';
