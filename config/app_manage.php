<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application 管理权限配置
    |--------------------------------------------------------------------------
    |
    | 通过 /api/v2/app-manage/* 进行应用工作台自动化管理时，
    | 除了路由要求的 hybrid.token:write 之外，控制器还会校验
    | “应用管理者白名单”：只有在这里列出的用户才能创建/修改/删除
    | 应用、文件与虚拟数据表。默认情况下禁止所有用户，避免普通用户
    | 通过自建 admin PAT 发布应用或在服务器上执行 PHP 代码。
    |
    | allowed_emails: 允许的用户邮箱列表（推荐）
    | allowed_user_ids: 允许的用户 ID 列表
    |
    */

    'allowed_emails' => explode(',', env('APP_MANAGE_ALLOWED_EMAILS', '')),

    'allowed_user_ids' => array_values(array_filter(array_map('intval', explode(',', env('APP_MANAGE_ALLOWED_USER_IDS', ''))))),

];