<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        if (request()->header('Content-Type')=='application/json' || (isset(explode('/', $request->url())[3]) && explode('/', $request->url())[3]=="api")) {
            return response()->json(['code' => 200, 'status' => 'success', 'message' => trans($response)], 200);
        }

        return $request->wantsJson() ? new JsonResponse(['message' => trans($response)], 200) : back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['email' => [trans($response)]]);
        }

        if (request()->header('Content-Type')=='application/json' || (isset(explode('/', $request->url())[3]) && explode('/', $request->url())[3]=="api")) {
            return response()->json(['code' => 404, 'status' => 'error', 'message' => trans($response)], 404);
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
    }
}
