<?php

namespace App\Mailer;

class UserMailer extends Mailer
{
    public function verifyMail($user)
    {
    	$subject = __('voyager.register.email_verify_subject');
        return $this->sendTo($user, $subject, $view, $data);
    }
}
