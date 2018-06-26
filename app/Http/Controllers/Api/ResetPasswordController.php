<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Http\Controllers\ApiController;

class ResetPasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
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

        $validator = Validator::make($request->all(), ['token' => 'required']);
        if ($validator->fails()) {
            return $this->setStatusCode(403)
                        ->responseNotFound($validator->messages()->toArray()['token'][0]);
        }

        $validator = Validator::make($request->all(), ['password' => 'required|confirmed|min:6']);
        if ($validator->fails()) {
            return $this->setStatusCode(403)
                        ->responseNotFound($validator->messages()->toArray()['password'][0]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        
        if($response == Password::PASSWORD_RESET){
            return $this->responseSuccess([], __('passwords.reset'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('passwords.token'));
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        // return $response == Password::PASSWORD_RESET
        //             ? $this->sendResetResponse($response)
        //             : $this->sendResetFailedResponse($request, $response);
    }

}
