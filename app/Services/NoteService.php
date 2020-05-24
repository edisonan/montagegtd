<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;

/**
 * 笔记业务逻辑
 * 
 * @author edison.an
 *
 */
class NoteService {
	/**
	 * Get all of the notes for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forUser(User $user) {
		return Note::where ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' )->get ();
	}
	
	/**
	 * Get all of the notes for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forStatus($status) {
		return Note::where ( 'status', $status )->orderBy ( 'created_at', 'desc' )->get ();
	}
	
	/**
	 * Get all of the notes for a given user.
	 *
	 * @param User $user        	
	 * @return Collection
	 */
	public function forUserByStatus(User $user, $status, $needPage = false) {
		$note = Note::with ( [ 
				'noteTagMaps.tag',
				'user' 
		] )->where ( 'status', $status )->orWhere ( 'user_id', $user->id )->orderBy ( 'created_at', 'desc' );
		if ($needPage) {
			return $note->paginate ( 50 );
		} else {
			return $note->get ();
		}
	}
}
