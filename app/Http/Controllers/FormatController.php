<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatController extends Controller
{
    //convert airtable format to meeting guide format
    static function convert($rows) {
        $meetings = $errors = [];

        //temporary lookup for days
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($rows as $row) {

            //some rows are empty, here's a simple hack
            if (!in_array($row->fields->{'Day'}, $days)) {
                $errors[] = [
                    'meeting' => $row->fields->{'Meeting Name'},
                    'issue' => 'invalid day ' + $row->fields->{'Day'},
                ];
                continue;
            }

            //types
            $types = [];
            if ($row->fields->{'Open / Closed'} == 'Open') $types[] = 'O';
            if ($row->fields->{'Open / Closed'} == 'Closed') $types[] = 'C';
            if ($row->fields->{'Accessibility'} == 'Wheelchair Accessible') $types[] = 'X';
            if (in_array('Discussion', $row->fields->{'Format'})) $types[] = 'D';
            if (in_array('Speaker', $row->fields->{'Format'})) $types[] = 'S';
            if ($row->fields->{'Status'}[0] == 'Location Temporarily Closed') $types[] = 'TC';

            $meetings[] = [
                'slug' => $row->id,
                'name' => $row->fields->{'Meeting Name'},
                'time' => date('H:i', strtotime(@$row->fields->{'Start Time'})),
                'day' => array_search($row->fields->{'Day'}, $days),
                //'notes' => @$row->fields->{'Meeting Note'},
                'location' => @$row->fields->{'Location Name'}[0],
                'address' => @$row->fields->{'Street Address'}[0],
                'city' => @$row->fields->{'City'}[0],
                'postal_code' => @$row->fields->{'ZIP'}[0],
                'region' => @$row->fields->{'City'}[0] == 'San Francisco' ? 'San Francisco' : 'Marin',
                'sub_region' => @$row->fields->{'Neighborhood'}[0],
                'location_notes' => @$row->fields->{'Locations_Note'}[0],
                'types' => array_map(function($type) { return strtoupper(trim($type, ' \t\n\r\0\x0B\xc2\xa0')); }, $row->fields->{'Designations'}),
            ];
        }

        //dd($errors);

        return $meetings;
    }
}
