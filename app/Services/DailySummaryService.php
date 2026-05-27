<?php

namespace App\Services;

use App\Repositories\DailySummaryRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\JournalRepository;
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
	 * @var JournalRepository
	 */
	protected $journalRepository;
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
	public function __construct(DailySummaryRepository $dailySummaryRepository, JournalRepository $journalRepository, ArticleSubRepository $articleSubRepository, MindRepository $mindRepository, NoteRepository $noteRepository) {
		$this->dailySummaryRepository = $dailySummaryRepository;
		$this->journalRepository = $journalRepository;
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
		
		$journals = $this->journalRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$articleSubs = $this->articleSubRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$minds = $this->mindRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		$notes = $this->noteRepository->getListForSummary ( Auth::id (), $startTime, $endTime );
		
		$journalTypeInfos = array(1=>'手账', 2=>'任务', 3=>'专注');
		$infos = array (
			'journal'=>array('name'=>'手账', 'list'=>array()),
			'article'=>array('name'=>'文章', 'list'=>array()),
			'mind'=>array('name'=>'导图', 'list'=>array()),
			'note'=>array('name'=>'笔记', 'list'=>array()),
		);
		foreach ( $journals as $journal ) {
			$type = isset($journalTypeInfos[$journal->type]) ? '['.$journalTypeInfos[$journal->type].']':'';
			$infos ['journal'] ['list'] [] = array (
					'content' => $type . $journal->name,
					'url' => '' 
			);
		}
		foreach ( $articleSubs as $articleSub ) {
			if (empty($articleSub) || empty($articleSub->article)) {
				continue;
			}
			$subject = isset($articleSub->article->subject) ? $articleSub->article->subject : '';
			$articleId = isset($articleSub->article_id) ? $articleSub->article_id : '';
			if ($subject === '' || $articleId === '') {
				continue;
			}
			$infos ['article'] ['list'] [] = array (
					'content' => '[' . $articleSub->status . ']' . $subject,
					'url' => '/article/view/' . $articleId
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
				CommonUtil::iftttNotify ( '日报提醒', '记录一下这一天的日报吧~',  config('app.url').'/dailycreate', $user->setting->ifttt_notify );
			}
		}
	}
}
