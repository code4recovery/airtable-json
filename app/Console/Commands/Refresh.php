<?php

namespace App\Console\Commands;
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\FormatController;
use App\Http\Controllers\ImportController;


class Refresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the data feed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //get results from airtable
        $meetings = ImportController::table('Meetings', 'TSML Meetings');
    
        //format them in the right format
        $meetings = FormatController::convert($meetings);
    
        //prepare data
        Storage::disk('public')->put('feed.json', response()->json($meetings)->getContent());        

        $this->info('done!');
    }
}
