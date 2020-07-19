<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatController extends Controller
{
    //convert airtable format to meeting guide format
    static function convert($rows, $return_errors=false) {
        $meetings = $errors = $new_conference_providers = [];

        $required_fields = ['Meeting Name', 'Day', 'Start Time'];

        $location_fields = ['Street Address', 'City', 'ZIP'];

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        //standard TSML types are defined in Controller.php
        $values = array_merge(self::$tsml_types, [
            'Beginner' => 'BE',
            'Book Study' => 'LIT',
            'Chip Meeting' => 'H',
            'Chips Monthly' => 'H',
            'Chips Weekly' => 'H',
            'Childcare' => 'BA',
            'Speaker Discussion' => 'D',
            'Step Study' => 'ST',
            'Traditions Study' => 'TR',
        ]);

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
            $row->fields->{'TSML_Type_Final'} = explode(',', $row->fields->{'TSML_Type_Final'});
            foreach ($row->fields->{'TSML_Type_Final'} as $value) {
                if (!array_key_exists($value, $values)) {
                    $errors[] = [
                        'id' => $row->id,
                        'name' => $row->fields->{'Meeting Name'},
                        'issue' => 'unexpected type',
                        'value' => $value,
                    ];
                    continue;
                }
                $types[] = $values[$value];
            }

            //hide meetings that are temporarily closed and not online
            if (in_array('TC', $types) && 
                empty($row->fields->{'Remote meeting URL'}) &&
                empty($row->fields->{'TSML_Phone_Final'})) {
                continue;
            }

            //region
            $region = null;
            if (!empty($row->fields->{'City'}[0])) {
                $region = ($row->fields->{'City'}[0] == 'San Francisco') ? 'San Francisco' : 'Marin';
            }

            //conference url
            if (!empty($row->fields->{'Remote meeting URL'})) {
                $url = parse_url($row->fields->{'Remote meeting URL'});
                $matches = array_filter(array_keys(self::$tsml_conference_providers), function($domain) use($url) {
                    return stripos($url['host'], $domain) !== false;
                });
                if (!count($matches)) {
                    $new_conference_providers[] = $url['host'];
                    $errors[] = [
                        'id' => $row->id,
                        'name' => $row->fields->{'Meeting Name'},
                        'issue' => 'unexpected conference provider',
                        'value' => $url['host'],
                    ];    
                }
            }

            $meetings[] = [
                'slug' => $row->id,
                'name' => $row->fields->{'Meeting Name'},
                'time' => date('H:i', strtotime($row->fields->{'Start Time'})),
                'day' => array_search($row->fields->{'Day'}, $days),
                'types' => array_unique($types),
                'conference_url' => @$row->fields->{'Remote meeting URL'},
                'conference_phone' => @$row->fields->{'TSML_Phone_Final'},
                'square' => @$row->fields->{'Cash App'},
                'venmo' => @$row->fields->{'Venmo'},
                'paypal' => @$row->fields->{'PayPal'},
                'notes' => @$row->fields->{'Meeting Note'},
                'location' => @$row->fields->{'Location Name'}[0],
                'address' => @$row->fields->{'Street Address'}[0],
                'city' => @$row->fields->{'City'}[0],
                'postal_code' => @$row->fields->{'ZIP'}[0],
                'region' => $region,
                'sub_region' => @$row->fields->{'Neighborhood'}[0],
                'location_notes' => @$row->fields->{'Location Note'}[0],
                'timezone' => 'America/Los_Angeles',
            ];
        }

        /*
        $new_conference_providers = array_map('strtolower', array_map(function($domain) {
            if (substr($domain, 0, 4) == 'www.') {
                $domain = substr($domain, 4);
            }
            return $domain;
        }, $new_conference_providers));
        sort($new_conference_providers);
        dd(array_unique($new_conference_providers));
        */

        return $return_errors ? $errors : $meetings;
    }
}
