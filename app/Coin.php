<?php
namespace App;

class Coin
{
    /**
     * @var \GearmanClient
     */
    protected $gearmanClient;

    /**
     * Coin constructor.
     * @param string $servers
     */
    public function __construct($servers)
    {
        $this->gearmanClient = new \GearmanClient();
        $this->gearmanClient->addServers($servers);
    }

    /**
     * 获取充值地址
     * @param string $type 币种，BTC：比特币，ETH：以太坊
     * @return string
     */
    public function getnewaddress($type)
    {
        return $this->gearmanClient->doNormal('getnewaddress', $type);
    }

    /**
     * 充值确认，主要用于充值补单
     * @param string $type 币种，BTC：比特币，ETH：以太坊
     * @param string $txid 交易id
     * @return string
     */
    public function deposit($type, $txid)
    {
        return $this->gearmanClient->doBackground('deposit', json_encode([
            'type' => $type,
            'txid' => $txid
        ]));
    }

    /**
     * 提现
     * @param string $type 币种，BTC：比特币，ETH：以太坊
     * @param string $to 提现地址
     * @param float $amount 提现金额
     * @param float $fee 矿工费
     * @return string txid交易id
     */
    public function withdraw($type, $to, $amount, $fee)
    {
        return $this->gearmanClient->doNormal('withdraw', json_encode([
            'type' => $type,
            'to' => $to,
            'amount' => $amount,
            'fee' => $fee,
        ]));
    }
}
