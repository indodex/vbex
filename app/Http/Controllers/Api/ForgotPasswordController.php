<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Validator;
use App\Http\Controllers\ApiController;

class ForgotPasswordController extends ApiController
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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);
        if ($validator->fails()) {
            return $this->setStatusCode(403)
                        ->responseNotFound($validator->messages()->toArray()['email'][0]);
        }

        
        $emailValidator = Validator::make($request->all(), ['email' => 'required|unique:users']);
        if(!$emailValidator->fails()){
            return $this->setStatusCode(403)->responseNotFound(__('passwords.user'));
        }

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        if($response == Password::RESET_LINK_SENT){
            return $this->responseSuccess([], __('passwords.sent'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('passwords.send_email_error'));
        }
        // return $response == Password::RESET_LINK_SENT
        //             ? $this->sendResetLinkResponse($response)
        //             : $this->sendResetLinkFailedResponse($request, $response);
        // return $response == Password::RESET_LINK_SENT
        //             ? $this->sendResetLinkResponse($response)
        //             : $this->sendResetLinkFailedResponse($request, $response);
    }
}
