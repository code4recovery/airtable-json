<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormatController extends Controller
{
    //convert airtable format to meeting guide format
    static function convert($rows, $return_errors=false) {
        $meetings = $errors = $new_conference_providers = [];

        $required_fields = ['TSML_name', 'TSML_day', 'TSML_time'];

        $location_fields = ['TSML_address', 'TSML_city', 'TSML_postal_code'];

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

            //dd($row->fields);

            //must have each of these fields
            foreach ($required_fields as $field) {
                if (empty(self::getValue($row, $field))) {
                    $errors[] = [
                        'id' => $row->id,
                        'name' => self::getValue($row, 'Meeting Name'),
                        'issue' => 'empty ' . $field . ' field',
                    ];
                    continue 2;    
                }
            }

            //must have one of these fields
            $location = false;
            foreach ($location_fields as $field) {
                if (!empty(self::getValue($row, $field))) $location = true;
            }
            if (!$location) {
                $errors[] = [
                    'id' => $row->id,
                    'name' => self::getValue($row, 'TSML_name'),
                    'issue' => 'no location information',
                ];
                continue;
            }

            //day must be valid
            if (!in_array(self::getValue($row, 'TSML_day'), $days)) {
                $errors[] = [
                    'id' => $row->id,
                    'name' => self::getValue($row, 'TSML_name'),
                    'issue' => 'unexpected day',
                    'value' => self::getValue($row, 'TSML_day'),
                ];
                continue;
            }

            //types
            $types = [];
            if (!empty($row->fields->{'TSML_types'})) {
                $row->fields->{'TSML_types'} = explode(',', $row->fields->{'TSML_types'});
                foreach ($row->fields->{'TSML_types'} as $value) {
                    $value = trim($value);
                    if (!array_key_exists($value, $values)) {
                        $errors[] = [
                            'id' => $row->id,
                            'name' => self::getValue($row, 'TSML_name'),
                            'issue' => 'unexpected type',
                            'value' => $value,
                        ];
                        continue;
                    }
                    $types[] = $values[$value];
                }    
            }

            //hide meetings that are temporarily closed and not online
            if (in_array('TC', $types) && 
                empty(self::getValue($row, 'TSML_conference_url')) &&
                empty(self::getValue($row, 'TSML_conference_phone'))) {
                continue;
            }

            //conference url
            if (!empty(self::getValue($row, 'TSML_conference_url'))) {

                $url = parse_url(self::getValue($row, 'TSML_conference_url'));
                if (empty($url['host'])) {
                    $errors[] = [
                        'id' => $row->id,
                        'name' => self::getValue($row, 'TSML_name'),
                        'issue' => 'could not parse url',
                        'value' => self::getValue($row, 'TSML_conference_url'),
                    ];
                } else {
                    $matches = array_filter(array_keys(self::$tsml_conference_providers), function($domain) use($url) {
                        return stripos($url['host'], $domain) !== false;
                    });
                    if (!count($matches)) {
                        $new_conference_providers[] = $url['host'];
                        $errors[] = [
                            'id' => $row->id,
                            'name' => self::getValue($row, 'TSML_name'),
                            'issue' => 'unexpected conference provider',
                            'value' => $url['host'],
                        ];    
                    }
                }
            }

            $meetings[] = [
                'slug' => $row->id,
                'name' => self::getValue($row, 'TSML_name'),
                'time' => date('H:i', strtotime(self::getValue($row, 'TSML_time'))),
                'day' => array_search(self::getValue($row, 'TSML_day'), $days),
                'types' => array_unique($types),
                'conference_url' => self::getValue($row, 'TSML_conference_url'),
                'conference_url_notes' => self::getValue($row, 'TSML_conference_url_notes'),
                'conference_phone' => self::getValue($row, 'TSML_phone'),
                'conference_phone_notes' => self::getValue($row, 'TSML_conference_phone_notes'),
                'square' => self::getValue($row, 'TSML_square'),
                'venmo' => self::getValue($row, 'TSML_venmo'),
                'paypal' => self::getValue($row, 'TSML_paypal'),
                'notes' => self::getValue($row, 'TSML_notes'),
                'location' => self::getValue($row, 'TSML_location'),
                'address' => self::getValue($row, 'TSML_address'),
                'city' => self::getValue($row, 'TSML_city'),
                'state' => self::getValue($row, 'TSML_state'),
                'postal_code' => self::getValue($row, 'TSML_postal_code'),
                'region' => self::getValue($row, 'TSML_region'),
                'sub_region' => self::getValue($row, 'TSML_sub_region'),
                'location_notes' => self::getValue($row, 'TSML_location_notes'),
                'timezone' => self::getValue($row, 'TSML_location_notes'),
                'feedback_url' => self::getValue($row, 'TSML_feedback_url'),
                'latitude' => self::getValue($row, 'TSML_latitude'),
                'longitude' => self::getValue($row, 'TSML_longitude'),
            ];
        }

        return $return_errors ? $errors : $meetings;
    }

    //airtable values can sometimes be an array
    static function getValue($row, $key) {
        if (empty($row->fields->{$key})) return null;
        if (is_array($row->fields->{$key})) return trim($row->fields->{$key}[0]);
        return trim($row->fields->{$key});
    }
}
