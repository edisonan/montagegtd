<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LlmController extends \App\Http\Controllers\LlmController
{
    protected function bootstrapAuthContext(Request $request)
    {
        $user = $request->user();
        if ($user) {
            Auth::setUser($user);
        }
    }

    public function getProviders(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::getProviders($request);
    }

    public function getProvider($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::getProvider($id);
    }

    public function getModels(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::getModels($request);
    }

    public function getModel($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::getModel($id);
    }

    public function getCredentials(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::getCredentials($request);
    }

    public function getCredential($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::getCredential($id);
    }

    public function chat(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::chat($request);
    }

    public function askAi(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::askAi($request);
    }

    public function getUsageStats(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::getUsageStats($request);
    }

    public function testCredential($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::testCredential($id);
    }

    public function saveProvider(Request $request, $id = null)
    {
        $this->bootstrapAuthContext($request);
        return parent::saveProvider($request, $id);
    }

    public function deleteProvider($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::deleteProvider($id);
    }

    public function saveModel(Request $request, $id = null)
    {
        $this->bootstrapAuthContext($request);
        return parent::saveModel($request, $id);
    }

    public function deleteModel($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::deleteModel($id);
    }

    public function saveCredential(Request $request, $id = null)
    {
        $this->bootstrapAuthContext($request);
        return parent::saveCredential($request, $id);
    }

    public function deleteCredential($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::deleteCredential($id);
    }
}
