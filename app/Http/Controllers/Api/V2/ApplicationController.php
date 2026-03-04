<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Code;

class ApplicationController extends Controller
{
    public function show($appSlug, $codePath)
    {
        $application = Application::where('slug', $appSlug)
            ->where('status', '<', 4)
            ->firstOrFail();

        $code = Code::where('app_id', $application->id)
            ->where('path', $codePath)
            ->where('status', 1)
            ->firstOrFail();

        return response($code->content)->header('Content-Type', $this->getContentType($code->type));
    }

    private function getContentType($type)
    {
        $typeMap = array(
            1 => 'text/php',
            2 => 'text/html',
            3 => 'text/javascript',
            4 => 'text/css',
            5 => 'application/json',
        );

        return isset($typeMap[$type]) ? $typeMap[$type] : 'text/plain';
    }
}

