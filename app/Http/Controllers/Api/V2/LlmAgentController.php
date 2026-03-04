<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LlmAgentController extends \App\Http\Controllers\LlmAgentController
{
    protected function bootstrapAuthContext(Request $request)
    {
        $user = $request->user();
        if ($user) {
            Auth::setUser($user);
        }
    }

    public function index(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::index($request);
    }

    public function show($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::show($id);
    }

    public function store(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::store($request);
    }

    public function update(Request $request, $id)
    {
        $this->bootstrapAuthContext($request);
        return parent::update($request, $id);
    }

    public function destroy($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::destroy($id);
    }

    public function toggleStatus($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::toggleStatus($id);
    }

    public function createDraft(Request $request)
    {
        $this->bootstrapAuthContext($request);
        return parent::createDraft($request);
    }

    public function updateDraft(Request $request, $id)
    {
        $this->bootstrapAuthContext($request);
        return parent::updateDraft($request, $id);
    }

    public function publishDraft($id)
    {
        $this->bootstrapAuthContext(request());
        return parent::publishDraft($id);
    }

    public function testChat(Request $request, $id)
    {
        $this->bootstrapAuthContext($request);
        return parent::testChat($request, $id);
    }
}
