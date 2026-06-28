<?php

namespace App\Repositories;

use App\Models\DigestTask;

class DigestTaskRepository
{
    public function create(array $data)
    {
        return DigestTask::create($data);
    }

    public function findById($id)
    {
        return DigestTask::with(array('user', 'profile'))->where('id', $id)->first();
    }

    public function update($id, array $data)
    {
        $task = DigestTask::where('id', $id)->first();
        if ($task) {
            $task->update($data);
        }

        return $task;
    }

    public function findPendingTasks($limit = 20)
    {
        return DigestTask::with(array('user', 'profile'))
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', date('Y-m-d H:i:s'));
            })
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    public function paginateByUserId($userId, $perPage = 20)
    {
        return DigestTask::with('profile')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findOpenTaskByProfileId($profileId)
    {
        return DigestTask::where('profile_id', $profileId)
            ->whereIn('status', array('pending', 'processing'))
            ->orderBy('id', 'desc')
            ->first();
    }
}
