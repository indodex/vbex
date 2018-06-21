<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Redis\Withdraw;
use App\User, Mail;

class SendShipmentNotification implements ShouldQueue
{
    /**
     * 任务将被推送到的连接名称.
     *
     * @var string|null
     */
    // public $connection = 'sqs';

    /**
     * 任务将被推送到的连接名称.
     *
     * @var string|null
     */
    // public $queue = 'listeners';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(OrderShipped $event)
    {
        $payCode = uniqid();
        $order   = $event->order;
        $user    = User::find($order->uid);
        $email   = $user->email;
        $content = __('api.public.withdraw_intro_line', ['number' => (float) $order->amount, 'code' => $order->currencyTo->code, 'address' => $order->address]);

        // 判断是否可以自动转账
        if(bccomp($order->amount, $order->currencyTo->extract_number_audit, 8) > -1) {
            return false;
        }

        if (empty($email)) {
            return false;
        }

        (new Withdraw)->setWithdrawApplyKey($payCode, $order->id, 45);
        $confirmUrl = url(config('app.url').'/dailog/tip?infotype=withdrawApply&id='.$payCode);
        $supportUrl = url(config('app.url'));

        Mail::send('auth.withdrawMail',['content' => $content, 'confirmUrl' => $confirmUrl, 'supportUrl' => $supportUrl],function($message) use($email){
            $message ->to($email)->subject(__('api.public.withdraw_email_subject'));
        });
    }

    public function failed(OrderShipped $event, $exception)
    {
        //
    }
}
