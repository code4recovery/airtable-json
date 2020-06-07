<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class ImportController extends Controller
{

    //recursive function to get records from Airtable API in batches
    static function table($table, $client=null, $offset=null) {


        //set up url
        $request = 'https://api.airtable.com/v0/' . env('AIRTABLE_BASE') . '/' . $table;        

        //if there's a record offset, append it to the URL and wait for Airtable's API limit
        if ($offset) {
            $request .= '?offset=' . $offset;
            sleep(.2);
        }

        //set up a request handler
        if (!$client) $client = new Client();

        //set up curl request
        $response = $client->get($request, [
            'headers' => [
                'Authorization' => 'Bearer ' . env('AIRTABLE_KEY'),
                'Accept' => 'application/json',
            ]
        ]);

        //decode json
        $result = json_decode($response->getBody());

        //recursion
        return (empty($result->offset)) ?
            $result->records :
            array_merge($result->records, self::table($table, $client, $result->offset));

    }
}
