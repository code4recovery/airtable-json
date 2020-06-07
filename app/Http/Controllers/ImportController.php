<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class ImportController extends Controller
{

    //recursive function to get records from Airtable API in batches
    static function table($table, $view=null, $offset=null, $client=null) {

        //set up a request handler
        if (!$client) $client = new Client();

        //set up curl request
        $response = $client->get(self::airtableUrl($table, $view, $offset), [
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
            array_merge(
                $result->records, 
                self::table($table, $view, $result->offset, $client)
            );

    }

    //set up url
    private static function airtableUrl($table, $view=null, $offset=null) {

        $url = 'https://api.airtable.com/v0/' . env('AIRTABLE_BASE') . '/' . $table;

        $params = [];

        if ($view) {
            $params['view'] = $view;
        }

        //if there's a record offset, append it to the URL and wait for Airtable's API limit
        if ($offset) {
            $params['offset'] = $offset;
            sleep(.2);
        }

        if (count($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
