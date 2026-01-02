<?php

namespace App\Services;

use App\Repositories\DailySummaryRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ThingRepository;
use App\Repositories\ArticleRepository;
use App\Repositories\ArticleSubRepository;
use App\Repositories\MindRepository;
use App\Repositories\NoteRepository;
use App\Models\User;
use App\Http\Utils\CommonUtil;

/**
 * 业务逻辑
 *
 * @author edison.an
 *        
 */
class DailySummaryService {
	/**
	 *
	 * @var DailySummaryRepository
	 */
	protected $dailySummaryRepository;
	/**
	 *
	 * @var ThingRepository
	 */
	protected $thingRepository;
	/**
	 *
	 * @var ArticleSubRepository
	 */
	protected $articleSubRepository;
	/**
	 *
	 * @var MindRepository
	 */
	protected $mindRepository;
	/**
	 *
	 * @var NoteRepository
	 */
	protected $noteRepository;
	
	/**
	 *
	 * @param CategoryRepository $categories        	
	 */
	public function __construct(DailySummaryRepository $dailySummaryRepository, ThingRepository $thingRepository, ArticleSubRepository $articleSubRepository, MindRepository $mindRepository, NoteRepository $noteRepository) {
		$this->dailySummaryRepository = $dailySummaryRepository;
		$this->thingRepository = $thingRepository;
		$this->articleSubRepository = $articleSubRepository;
		$this->mindRepository = $mindRepository;
		$this->noteRepository = $noteRepository;
	}
	
	/**
	 * 获取日报列表
	 * 
	 * @return \App\Repositories\unknown
	 */
	public function getList() {
		return $this->dailySummaryRepository->getUserList ( Auth::id () );
	}
	
	/**
	 * 根据日期获取日报信息
	 * 
	 * @param unknown $summaryDate        	
	 * @return \App\Repositories\unknown
	 */
	public function getBySummaryDate($summaryDate) {
		return $this->dailySummaryRepository->getUserDailySummaryByDate ( Auth::id (), $summaryDate );
	}
	
	/**
	 * 根据日期获取提示信息
	 * 
	 * @param unknown $summaryDate        	
	 * @return \App\Repositories\unknown
	 */
	public function getTipInfos($summaryDate) {
		$startTime = $summaryDate . ' 00:00:00';
		$endTime = $summaryDate . ' 23:59:59';
		
		$things = $this->thingRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$articleSubs = $this->articleSubRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$minds = $this->mindRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$notes = $this->noteRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		
		$thingTypeInfos = array(1=>'事情', 2=>'任务', 3=>'番茄');
		$infos = array (
			'thing'=>array('name'=>'事情', 'list'=>array()),
			'article'=>array('name'=>'文章', 'list'=>array()),
			'mind'=>array('name'=>'导图', 'list'=>array()),
			'note'=>array('name'=>'笔记', 'list'=>array()),
		);
		foreach ( $things as $thing ) {
			$type = isset($thingTypeInfos[$thing->type]) ? '['.$thingTypeInfos[$thing->type].']':'';
			$infos ['thing'] ['list'] [] = array (
					'content' => $type . $thing->name,
					'url' => '' 
			);
		}
		foreach ( $articleSubs as $articleSub ) {
			$infos ['article'] ['list'] [] = array (
					'content' => '[' . $articleSub->status . ']' . $articleSub->article->subject,
					'url' => '/article/view/' . $articleSub->article_id 
			);
		}
		foreach ( $minds as $mind ) {
			$infos ['mind'] ['list'] [] = array (
					'content' => $mind->name,
					'url' => '/mind/' . $mind->id 
			);
		}
		foreach ( $notes as $note ) {
			$infos ['note'] ['list'] [] = array (
					'content' => $note->name,
					'url' => '' 
			);
		}
		
		return $infos;
	}
	
	/**
	 * 每日日报提醒
	 */
	public function scheduleDailySummaryReminder() {
		$users = User::where ( 'last_login', '>', date ( 'Y-m-d 00:00:00', time () - 3 * 24 * 60 * 60 ) )->get ();
		foreach ( $users as $user ) {
			if (isset ( $user->setting->ifttt_notify )) {
				CommonUtil::iftttnotify ( '日报提醒', '记录一下这一天的日报吧~',  config('app.url').'/dailycreate', $user->setting->ifttt_notify );
			}
		}
	}
}
