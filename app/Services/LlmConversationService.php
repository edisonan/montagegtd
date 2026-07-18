<?php

namespace App\Services;

use App\Repositories\LlmConversationRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\LlmConversation;

class LlmConversationService
{

    /**
     * LlmConversationRepository 实例.
     *
     * @var LlmConversationRepository
     */
    protected $repository;

    public function __construct(LlmConversationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 创建一个新的AI对话记录
     *
     * @param array $data
     * @return LlmConversation
     * @throws ValidationException
     */
    public function createConversation(array $data): LlmConversation
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'session_id' => 'nullable|integer',
            'model_id' => 'nullable|exists:llm_models,id',
            'question' => 'required|string',
            'answer' => 'nullable|string',
            'request_data' => 'nullable|array',
            'response_data' => 'nullable|array',
            'prompt_tokens' => 'nullable|integer|min:0',
            'completion_tokens' => 'nullable|integer|min:0',
            'total_tokens' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric',
            'answered_at' => 'nullable|date',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 直接使用 $data，因为已经验证过了
        // 或者手动提取验证后的数据
        $validatedData = [];
        foreach ($rules as $field => $rule) {
            if (array_key_exists($field, $data)) {
                $validatedData[$field] = $data[$field];
            }
        }

        return $this->repository->create($validatedData);
    }

    /**
     * 根据ID查找AI对话记录
     *
     * @param int $id
     * @return LlmConversation|null
     */
    public function findConversationById(int $id): LlmConversation
    {
        return $this->repository->findById($id);
    }

    /**
     * 根据用户ID查找AI对话记录
     *
     * @param int $userId
     * @return mixed
     */
    public function getConversationsByUserId(int $userId)
    {
        return $this->repository->findByUserId($userId);
    }

    /**
     * 更新AI对话记录
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function updateConversation(int $id, array $data): bool
    {
        $validator = Validator::make($data, [
            'user_id' => 'sometimes|required|exists:users,id',
            'session_id' => 'sometimes|nullable|integer',
            'model_id' => 'sometimes|nullable|exists:llm_models,id',
            'question' => 'sometimes|required|string',
            'answer' => 'sometimes|nullable|string',
            'request_data' => 'sometimes|nullable|array',
            'response_data' => 'sometimes|nullable|array',
            'prompt_tokens' => 'sometimes|nullable|integer|min:0',
            'completion_tokens' => 'sometimes|nullable|integer|min:0',
            'total_tokens' => 'sometimes|nullable|integer|min:0',
            'cost' => 'sometimes|nullable|numeric',
            'answered_at' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Laravel 5.5 的 Validator::hasRule 在组合规则下行为不稳定，
        // 直接按白名单提取已经通过校验的字段，避免 update 变成空数组。
        $validatedData = [];
        $allowedFields = [
            'user_id',
            'session_id',
            'model_id',
            'question',
            'answer',
            'request_data',
            'response_data',
            'prompt_tokens',
            'completion_tokens',
            'total_tokens',
            'cost',
            'answered_at',
        ];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $validatedData[$field] = $data[$field];
            }
        }

        return $this->repository->update($id, $validatedData);
    }

    /**
     * 删除AI对话记录
     *
     * @param int $id
     * @return bool
     */
    public function deleteConversation(int $id): bool
    {
        return $this->repository->delete($id);
    }
    
    public function getConversationsByUserIdWithPagination(int $userId, int $perPage = 15)
    {
        return $this->repository->getConversationsByUserIdWithPagination($userId, $perPage);
    }
}
