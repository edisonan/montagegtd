<?php

namespace App\Repositories;

use App\Models\LlmConversation;
use Illuminate\Support\Facades\DB;

class LlmConversationRepository
{
    public function create(array $data): LlmConversation
    {
        return LlmConversation::create($data);
    }

    public function findById(int $id): LlmConversation
    {
        return LlmConversation::find($id);
    }

    public function findByUserId(int $userId)
    {
        return LlmConversation::where('user_id', $userId)->paginate(15);
    }

    public function update(int $id, array $data): bool
    {
        $conversation = LlmConversation::find($id);
        if (!$conversation) {
            return false;
        }

        $conversation->fill($data);

        return $conversation->save();
    }

    public function delete(int $id): bool
    {
        return LlmConversation::destroy($id);
    }
    
    public function getConversationsByUserIdWithPagination(int $userId, int $perPage = 15)
    {
        return LlmConversation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
