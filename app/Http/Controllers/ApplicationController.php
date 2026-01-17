<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Code;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * 根据应用标识和代码路径显示代码内容
     * 
     * @param string $appSlug 应用标识
     * @param string $codePath 代码文件路径
     * @return \Illuminate\Http\Response
     */
    public function show($appSlug, $codePath)
    {
        // 根据应用标识查找应用
        $application = Application::where('slug', $appSlug)
            ->where('status', '<', 4) // 状态不为"已删除"
            ->firstOrFail();

        // 根据应用ID和代码路径查找代码
        $code = Code::where('app_id', $application->id)
            ->where('path', $codePath)
            ->where('status', 1) // 状态必须为启用
            ->firstOrFail();

        // 根据代码类型返回不同的内容
        $contentType = $this->getContentType($code->type);
        
        return response($code->content)
            ->header('Content-Type', $contentType);
    }

    /**
     * 根据代码类型返回对应的Content-Type
     * 
     * @param int $type 代码类型
     * @return string
     */
    private function getContentType($type)
    {
        $typeMap = [
            1 => 'text/php',
            2 => 'text/html',
            3 => 'text/javascript',
            4 => 'text/css',
            5 => 'application/json',
        ];

        return $typeMap[$type] ?? 'text/plain';
    }
}