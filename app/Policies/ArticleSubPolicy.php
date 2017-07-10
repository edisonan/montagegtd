<?php

namespace App\Policies;

use App\User;
use App\ArticleSub;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticleSubPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can delete the given task.
     *
     * @param  User  $user
     * @param  Task  $task
     * @return bool
     */
    public function destroy(User $user, ArticleSub $articleSub)
    {
        return $user->id === $articleSub->user_id;
    }
}
