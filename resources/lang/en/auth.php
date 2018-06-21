<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login' => [
        'login'                => 'login',
        'password'             => 'password',
        'email'                => 'email address',
        'loggingin'            => 'logging in',
        'empty_email'          => 'E-mail can not be empty',
        'password_error'       => 'password error',
        'login_fail'           => 'Login failed',
        'login_success'        => 'login successful',
        'forget_password'      => 'Forgot your password? ',
        'empty_new_password'   => 'new password can not be empty',
        'edit_passowrd_failed' => 'password change failed',
        'empty_user'           => 'user name, password verification failed! If the same username is entered incorrectly five times in a row, the user will be locked for one hour. ',
        'too_many_attempts'    =>' Too many password errors, please wait one hour before logging in. ',
        'user_locked'          => 'The user has been locked, please contact the administrator to unlock. ',
    ],
    'register' => [
        'register'               => 'register',
        'email_verify_subject'   => 'email verification code',
        'empty_email'            => 'E-mail can not be empty',
        'empty_name'             => 'user name can not be empty',
        'empty_password'         => 'password can not be empty',
        'confirm_password_error' => 'twice the password is not the same',
        'repeat_email'           => 'E-mail registered',
        'register_error'         => 'registration failed',
        'register_success'       => 'registration successful',
        'email_verify_content'   => 'Use :code to email your HAC account for verification. This code will expire in 30 minutes. If you receive this email in error, please ignore it or contact HAC customer service. ',
        'email_verify_subject'   => 'HAC Passcode',
        'verify_error'           => 'verification failed',
        'send_email_success'     => 'verification code sent successfully',
        'send_email_error'       => 'Verification code failed to send',
        'email_host_error'       => 'mail service error',
        'empty_code'             => 'CAPTCHA can not be empty',
        'verify_code_error'      => 'verification failed',
        'verify_code_success'    => 'Verify successful',
        'top_message'            => "You agree to the <u> Terms of Service </u> and confirm that you are not a U.S. citizen or resident and U.S. citizens or residents will not be permitted to use this platform until they have been granted a U.S. federal and state license. More details <u> Please read the full </u>, thank you for your patience!
        In order to confirm your agreement to our Terms of Service, please provide your email address. ",
        'email'                  => 'email address',
        'edit_email'             => 'modify email address',
        'reset_send '            =>' Resend ',
        'pl_input_password'      => 'Please enter your user name and password. ',
        'continue'               => 'continue',
        'input_email_msg'        => 'Please enter the verification code sent to your email :email. ',
        'password'               => 'password',
        'password_confirmation'  => 'Confirm password',
        'password_verify_error'  => 'Password verification failed',
        'empty_mobile'           => 'phone number can not be empty',
        'username'               => 'username',
        'sending_failure'        => 'Email Send failed',
    ]
];
