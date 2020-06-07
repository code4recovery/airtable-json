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
    $meetings = ImportController::table('Meetings');

    //dd(array_pop($meetings));


    //format them in the right format
    $meetings = FormatController::convert($meetings);

    //prepare data
    $data = json_encode($meetings);

    //save file
    file_put_contents(public_path() . '/meetings.json', $data);

    //output
    return '<a href="/meetings.json">saved ' . count($meetings) . ' meetings</a>';
});