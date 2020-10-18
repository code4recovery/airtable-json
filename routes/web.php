<?php

use Illuminate\Support\Facades\Route;
use App\Console\Commands\Refresh;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\FormatController;

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

Route::get('/', function() {

    return response(Storage::disk('public')->get('feed.json'))
        ->header('Content-Type', 'application/json');

});

Route::get('refresh', function() {

    Artisan::call('refresh');

    return 'refreshed!';
});

Route::get('errors', function() {

    //get results from airtable
    $meetings = ImportController::table('SYNC_tsml', 'TSML_fields');

    //format them in the right format
    $errors = FormatController::convert($meetings, true);

    //prepare data
    return view('errors', compact('errors'));
});