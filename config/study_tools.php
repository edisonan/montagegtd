<?php

/*
|--------------------------------------------------------------------------
| 学习工具（Study Tools）配置
|--------------------------------------------------------------------------
| 卡片注册表 + 远程辅导房间参数 + ICE 服务器配置
*/

return [

    // 工具卡片注册表（/study/tools 页面数据驱动，新增工具在此追加）
    'tools' => [
        [
            'key' => 'tutoring',
            'name' => '远程辅导',
            'icon' => 'fas fa-chalkboard-teacher',
            'desc' => '基于 WebRTC 的音视频远程辅导：通过课程/计划 URL 或本地文件展示学习内容，双方可切换标注画笔实时同步批注。',
            'tags' => ['实时', 'WebRTC', '同步白板'],
            'url' => '/study/tools/tutoring',
            'accent' => '#1e3a8a',
        ],
    ],

    // 房间有效期（分钟）
    'room_ttl_minutes' => 120,

    // 单条信令/白板消息 payload 上限（字节）
    'message_payload_max' => 65536,

    // 拉取消息单页上限
    'message_poll_limit' => 200,

    // 加入房间口令（留空 = 仅凭房号即可加入）
    'room_join_password' => '',

    // 前端 ICE 服务器（WebRTC 连接用）
    'ice_servers' => [
        ['urls' => 'stun:stun.l.google.com:19302'],
        ['urls' => 'stun:stun1.l.google.com:19302'],
    ],

    // 可选 TURN（生产环境建议配置；urls 为空则忽略）
    'turn' => [
        'urls' => env('STUDY_TOOLS_TURN_URLS', ''),
        'username' => env('STUDY_TOOLS_TURN_USERNAME', ''),
        'credential' => env('STUDY_TOOLS_TURN_CREDENTIAL', ''),
    ],

    // 高保真服务端 Office 转换开关（V1 未实现，默认关闭；开启需部署 LibreOffice）
    'office_server_conversion' => false,
];