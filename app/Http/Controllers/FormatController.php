<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatController extends Controller
{
    //convert airtable format to meeting guide format
    static function convert($rows, $return_errors=false) {
        $meetings = $errors = [];

        $required_fields = ['Meeting Name', 'Day', 'Start Time'];

        $location_fields = ['Street Address', 'City', 'ZIP'];

        $categories = ['Accessibility', 'Open / Closed', 'Format', 'Status', 'Focus'];

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $values = [
            'American Sign Language' => 'ASL',
            'Beginner' => 'BE',
            'Big Book' => 'B',
            'Book Study' => 'LIT',
            'Chip Meeting' => 'H',
            'Chips Monthly' => 'H',
            'Chips Weekly' => 'H',
            'Childcare' => 'BA',
            'Closed' => 'C',
            'Discussion' => 'D',
            'Espanol (Spanish Language)' => 'S',
            'Gay' => 'G',
            'Lesbian' => 'L',
            'Location Temporarily Closed' => 'TC',
            'Meditation' => 'MED',
            'Men' => 'M',
            'Open' => 'O',
            'Secular' => 'A',
            'Seniors' => 'SEN',
            'Speaker' => 'SP',
            'Speaker Discussion' => 'D',
            'Step Study' => 'ST',
            'Steps & Traditions' => 'ST',
            'Traditions Study' => 'TR',
            'Transgender' => 'T', 
            'Wheelchair Accessible' => 'X',
            'Women' => 'W',
            'Young People' => 'YP',
        ];

        foreach ($rows as $row) {

            //must have each of these fields
            foreach ($required_fields as $field) {
                if (empty($row->fields->{$field})) {
                    $errors[] = [
                        'id' => $row->id,
                        'name' => @$row->fields->{'Meeting Name'},
                        'issue' => 'empty ' . $field . ' field',
                    ];
                    continue 2;    
                }
            }

            //must have one of these fields
            $location = false;
            foreach ($location_fields as $field) {
                if (!empty($row->fields->{$field})) $location = true;
            }
            if (!$location) {
                $errors[] = [
                    'id' => $row->id,
                    'name' => $row->fields->{'Meeting Name'},
                    'issue' => 'no location information',
                ];
                continue;
            }

            //day must be valid
            if (!in_array($row->fields->{'Day'}, $days)) {
                $errors[] = [
                    'id' => $row->id,
                    'name' => $row->fields->{'Meeting Name'},
                    'issue' => 'unexpected day',
                    'value' => $row->fields->{'Day'},
                ];
                continue;
            }

            //types
            $types = [];
            foreach ($categories as $category) {
                if (!empty($row->fields->{$category})) {
                    if (!is_array($row->fields->{$category})) $row->fields->{$category} = [$row->fields->{$category}];
                    foreach ($row->fields->{$category} as $value) {
                        if (!array_key_exists($value, $values)) {
                            $errors[] = [
                                'id' => $row->id,
                                'name' => $row->fields->{'Meeting Name'},
                                'issue' => 'unexpected ' . $category,
                                'value' => $value,
                            ];
                            continue;
                        }
                        $types[] = $values[$value];
                    }
                }
            }

            //hide meetings that are temporarily closed and not online
            if (in_array('TC', $types) && 
                empty($row->fields->{'Remote meeting URL'}) &&
                empty($row->fields->{'Phone'})) {
                continue;
            }

            //region
            $region = null;
            if (!empty($row->fields->{'City'}[0])) {
                $region = ($row->fields->{'City'}[0] == 'San Francisco') ? 'San Francisco' : 'Marin';
            }

            $meetings[] = [
                'slug' => $row->id,
                'name' => $row->fields->{'Meeting Name'},
                'time' => date('H:i', strtotime($row->fields->{'Start Time'})),
                'day' => array_search($row->fields->{'Day'}, $days),
                'types' => array_unique($types),
                'conference_url' => @$row->fields->{'Remote meeting URL'},
                'conference_phone' => @$row->fields->{'Phone'},
                'square' => @$row->fields->{'Square'},
                'venmo' => @$row->fields->{'Venmo'},
                'paypal' => @$row->fields->{'PayPal'},
                'notes' => @$row->fields->{'Meeting Note'},
                'location' => @$row->fields->{'Location Name'}[0],
                'address' => @$row->fields->{'Street Address'}[0],
                'city' => @$row->fields->{'City'}[0],
                'postal_code' => @$row->fields->{'ZIP'}[0],
                'region' => $region,
                'sub_region' => @$row->fields->{'Neighborhood'}[0],
                'location_notes' => @$row->fields->{'Locations_Note'}[0],
            ];
        }

        return $return_errors ? $errors : $meetings;
    }
}
