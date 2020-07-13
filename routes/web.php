<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\ImportController;

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

    //get results from airtable
    $meetings = ImportController::table('Meetings', 'TSML Meetings');

    //format them in the right format
    $meetings = FormatController::convert($meetings);

    //prepare data
    return response()->json($meetings);
});

Route::get('errors', function() {

    //get results from airtable
    $meetings = ImportController::table('Meetings', 'TSML Meetings');

    //format them in the right format
    $errors = FormatController::convert($meetings, true);

    //prepare data
    return view('errors', compact('errors'));
});