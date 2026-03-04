<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LlmSessionController extends \App\Http\Controllers\LlmSessionController
{
    protected function bootstrapAuthContext(Request $request)
    {
        $user = $request->user();
        if ($user) {
            Auth::setUser($user);
        }
    }

    public function getSessions(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::getSessions($request);
    }

    public function getSession($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::getSession($id);
    }

    public function createSession(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::createSession($request);
    }

    public function updateSessionTitle(Request $request, $id)
    {
        $this->bootstrapAuthContext($request);
        return parent::updateSessionTitle($request, $id);
    }

    public function clearSession($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::clearSession($id);
    }

    public function togglePinSession($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::togglePinSession($id);
    }

    public function deleteSession($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::deleteSession($id);
    }
}
