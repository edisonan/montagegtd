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
            'model_id' => 'nullable|exists:llm_models,id',
            'credential_id' => 'nullable|exists:llm_provider_credentials,id',
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
            'model_id' => 'sometimes|nullable|exists:llm_models,id',
            'credential_id' => 'sometimes|nullable|exists:llm_provider_credentials,id',
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

        // Laravel 5.5 兼容性处理：使用验证后的数据
        $validatedData = [];
        foreach ($validator->getData() as $key => $value) {
            if ($validator->hasRule($key, ['sometimes', 'required', 'nullable', 'exists', 'string', 'array', 'integer', 'numeric', 'date'])) {
                $validatedData[$key] = $value;
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