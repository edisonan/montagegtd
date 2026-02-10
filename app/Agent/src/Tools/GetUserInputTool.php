<?php

namespace App\Agent\Tools;

/**
 * 用户输入工具
 */
class GetUserInputTool extends BaseTool
{
    public function getName(): string
    {
        return 'get_user_input';
    }

    public function getDescription(): string
    {
        return 'Request user input when additional information is needed to complete the task.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_input_fields' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'field_name' => [
                                'type' => 'string',
                                'description' => 'The name of the input field'
                            ],
                            'field_type' => [
                                'type' => 'string',
                                'enum' => ['string', 'integer', 'boolean', 'number'],
                                'description' => 'The type of the input field'
                            ],
                            'field_description' => [
                                'type' => 'string',
                                'description' => 'Description of what information is needed'
                            ]
                        ],
                        'required' => ['field_name', 'field_type', 'field_description']
                    ],
                    'description' => 'List of input fields to request from user'
                ],
                'context' => [
                    'type' => 'string',
                    'description' => 'Additional context for why this input is needed'
                ]
            ],
            'required' => ['user_input_fields']
        ];
    }

    public function execute(array $arguments): ToolResult
    {
        // 这个工具实际上不会被执行，它只是用来触发用户输入请求
        // 在 AgentLoop 中会特殊处理这个工具调用
        return ToolResult::failure("get_user_input tool should not be executed directly");
    }

    /**
     * 检查是否为用户输入工具调用
     *
     * @param string $toolName 工具名称
     * @return bool
     */
    public static function isUserInputTool(string $toolName): bool
    {
        return $toolName === 'get_user_input';
    }

    /**
     * 解析用户输入字段
     *
     * @param array $arguments 工具参数
     * @return array
     */
    public static function parseUserInputFields(array $arguments): array
    {
        $fields = [];
        if (isset($arguments['user_input_fields']) && is_array($arguments['user_input_fields'])) {
            foreach ($arguments['user_input_fields'] as $fieldData) {
                $fields[] = [
                    'field_name' => $fieldData['field_name'],
                    'field_type' => $fieldData['field_type'],
                    'field_description' => $fieldData['field_description']
                ];
            }
        }
        return $fields;
    }
}