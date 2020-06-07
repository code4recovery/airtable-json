<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatController extends Controller
{
    //convert airtable format to meeting guide format
    static function convert($rows) {
        $meetings = [];

        foreach ($rows as $row) {
            if (empty($row->fields->{"Locations_Street_Address"}[0])) {
                continue;
            }
            $meetings[] = [
                'slug' => $row->id,
                'types' => array_map(function($type) { return strtoupper(trim($type, " \t\n\r\0\x0B\xc2\xa0")); }, $row->fields->{"Designations"}),
                'name' => $row->fields->{"Meeting Name [Legacy]"},
                'day' => $row->fields->{"Day"},
                'time' => date('H:i', strtotime($row->fields->{"Start Time"})),
                'notes' => $row->fields->{"Meeting Note"},
                'location' => $row->fields->{"Locations_Site_Name"}[0],
                'address' => $row->fields->{"Locations_Street_Address"}[0],
                'city' => $row->fields->{"Locations_City"}[0],
                'postal_code' => $row->fields->{"Locations_ZIP"}[0],
                'region' => $row->fields->{"Locations_City"} == 'San Francisco' ? 'San Francisco' : 'Marin',
                'sub_region' => $row->fields->{"Locations_Neighborhood"}[0],
                'location_notes' => $row->fields->{"Locations_Note"}[0],
            ];
        }

        return $meetings;
    }
}
