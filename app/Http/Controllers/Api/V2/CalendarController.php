<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Services\CalendarService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    protected $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
        $personCalUrl = $this->calendarService->getPersonCalUrl();
        $host = CommonUtil::getUrlHost(config('app.url'));

        $cals = array(
            array(
                'theme' => '2018 世界杯',
                'url' => 'webcal://' . $host . '/calics/worldcup',
            ),
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'person_cal_url' => $personCalUrl,
            'cals' => $cals,
        )));
    }

    public function ics(Request $request, string $theme)
    {
        $icsInfo = $this->calendarService->getIcsByTheme($theme);

        return response($icsInfo['file_content'], 200, array(
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $icsInfo['file_name'] . '.ics"',
        ));
    }

    public function taskics(Request $request, string $calToken)
    {
        $icsInfo = $this->calendarService->getIcsByCalToken($calToken);

        return response($icsInfo['file_content'], 200, array(
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $icsInfo['file_name'] . '.ics"',
        ));
    }
}
