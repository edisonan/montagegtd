<?php

namespace App\Http\Controllers;

use App\Http\Utils\CommonUtil;
use App\Services\CalendarService;
use Illuminate\Http\Request;

/**
 * 日历订阅控制器
 *
 * @author edison.an
 *
 */
class CalendarController extends Controller
{
    /**
     * CalendarService 实例.
     *
     * @var CalendarService
     */
    protected $calendarService;

    /**
     * 构造方法
     *
     * @param CalendarService $cals
     * @return void
     */
    public function __construct(CalendarService $calService)
    {
        $this->middleware('auth', [
            'except' => [
                'ics',
                'taskics'
            ]
        ]);

        $this->calendarService = $calService;
    }

    /**
     * 日历订阅首页
     *
     * @param Request $request
     * @return
     *
     */
    public function index(Request $request)
    {
        // 处理个人日历提醒相关内容
        $personCalUrl = $this->calendarService->getPersonCalUrl();

        $host = CommonUtil::getUrlHost ( config ( 'app.url' ) );
        // 处理公共日历相关内容
        $cals = array(
            array(
                'theme' => '2018 世界杯',
                'url' => 'webcal://'.$host.'/calics/worldcup'
            )
        );

        return view('cals.index', [
            'person_cal_url' => $personCalUrl,
            'cals' => $cals
        ]);
    }

    /**
     * 根据主题获取日历订阅
     *
     * @param Request $request
     * @param String $theme
     */
    public function ics(Request $request, string $theme)
    {
        $icsInfo = $this->calendarService->getIcsByTheme($theme);

        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename = " . $icsInfo ['file_name'] . '.ics');
        header("Accept-ranges:bytes");
        header("Accept-length:" . strlen($icsInfo ['file_content']));

        readfile(config("app.storage_path") . '/' . $icsInfo ['file_name']);
    }

    /**
     * 获取个人任务日历订阅
     *
     * @param Request $request
     * @param String $cal_token
     */
    public function taskics(Request $request, string $cal_token)
    {
        $icsInfo = $this->calendarService->getIcsByCalToken($cal_token);

        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename = " . $icsInfo ['file_name'] . '.ics');
        header("Accept-ranges:bytes");
        header("Accept-length:" . strlen($icsInfo ['file_content']));

        readfile(config("app.storage_path") . '/' . $icsInfo ['file_name']);
    }
}
