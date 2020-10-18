<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    static $tsml_conference_providers = [
        'bluejeans.com' => 'Bluejeans',
        'freeconference.com' => 'Free Conference',
        'freeconferencecall.com' => 'FreeConferenceCall',
        'meet.google.com' => 'Google Hangouts',
        'gotomeet.me' => 'GoToMeeting',
        'gotomeeting.com' => 'GoToMeeting',
        'skype.com' => 'Skype',
        'webex.com' => 'WebEx',
        'zoho.com' => 'Zoho',
        'zoom.us' => 'Zoom',    
    ];
    
    static $tsml_types = [
        '11th Step Meditation' => '11',
        '12 Steps & 12 Traditions' => '12x12',
        'American Sign Language' => 'ASL',
        'As Bill Sees It' => 'ABSI',
        'Babysitting Available' => 'BA',
        'Big Book' => 'B',
        'Birthday' => 'H',
        'Breakfast' => 'BRK',
        'Candlelight' => 'CAN',
        'Child-Friendly' => 'CF',
        'Closed' => 'C',
        'Concurrent with Al-Anon' => 'AL-AN',
        'Concurrent with Alateen' => 'AL',
        'Cross Talk Permitted' => 'XT',
        'Daily Reflections' => 'DR',
        'Digital Basket' => 'DB',
        'Discussion' => 'D',
        'Dual Diagnosis' => 'DD',
        'English' => 'EN',
        'Fragrance Free' => 'FF',
        'French' => 'FR',
        'Gay' => 'G',
        'Grapevine' => 'GR',
        'Indigenous' => 'NDG',
        'Italian' => 'ITA',
        'Japanese' => 'JA',	
        'Korean' => 'KOR',
        'Lesbian' => 'L',
        'Literature' => 'LIT',
        'Living Sober' => 'LS',
        'LGBTQ' => 'LGBTQ',
        'Meditation' => 'MED',
        'Men' => 'M',
        'Native American' => 'N',
        'Newcomer' => 'BE',
        'Non-Smoking' => 'NS',
        'Open' => 'O',
        'Online' => 'ONL',
        'Outdoor' => 'OUT',
        'People of Color' => 'POC',
        'Polish' => 'POL',
        'Portuguese' => 'POR',
        'Professionals' => 'P',
        'Punjabi' => 'PUN',
        'Russian' => 'RUS',
        'Secular' => 'A',
        'Seniors' => 'SEN',
        'Smoking Permitted' => 'SM',
        'Spanish' => 'S',
        'Speaker' => 'SP',
        'Step Study' => 'ST',
        'Location Temporarily Closed' => 'TC',
        'Tradition Study' => 'TR',
        'Transgender' => 'T',
        'Wheelchair Access' => 'X',
        'Wheelchair-Accessible Bathroom' => 'XB',
        'Women' => 'W',
        'Young People' => 'Y',  
    ];
}
