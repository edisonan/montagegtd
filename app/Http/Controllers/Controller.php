<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;


/**
 *
 * @author edison.an
 *
 */
class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function jsonAndViewAutoResponse($request, $responseData, $viewPage)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData, 200);
        } else {
            return view($viewPage, $responseData ['result']);
        }
    }

    public function jsonAndRedirectAutoResponse($request, $responseData, $redirectPage)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($responseData, 200);
        } else {
            return redirect($redirectPage)->with("message", "IT WORKS!");
        }
    }

    public function jsonResponse($request, $responseData)
    {
        return response()->json($responseData, 200);
    }
}
