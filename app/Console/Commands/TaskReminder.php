<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\TaskRepository;
use Mail;

/**
 * reminder the deadline task
 * 
 * @author edison.an
 *        
 */
class TaskReminder extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'task_reminder';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Task Reminder';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$taskRepository = new TaskRepository ();
		
		$start_time = date ( 'Y-m-d H:i:s' );
		$end_time = date ( 'Y-m-d H:i:s', strtotime ( $start_time ) + 60 );
		
		// get need remind task list
		$tasks = $taskRepository->forUserByRemindTime ( $start_time, $end_time );
		foreach ( $tasks as $task ) {
			$user = $task->user;
			Mail::send ( 'emails.reminder', [ 
					'user' => $user,
					'task' => $task 
			], function ($m) use ($user, $task) {
				$m->to ( $user->email, $user->name )->subject ( 'Task Reminder for :' . $task->name );
			} );
		}
	}
}
