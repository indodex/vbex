<?php

namespace App\Repositories;

class BlockFactory
{
	protected $typeList;

	public function __construct()
    {
        $this->typeList = array(
            'BTC' => __NAMESPACE__ . '\Block\BtcRepository',
            'EOS' => __NAMESPACE__ . '\Block\EosRepository',
            'ETH' => __NAMESPACE__ . '\Block\EthRepository'
        );
    }

    /**
     * 创建车子
     *
     * @param string $type a known type key
     *
     * @return VehicleInterface a new instance of VehicleInterface
     * @throws \InvalidArgumentException
     */
    public function create($type)
    {
        if (!array_key_exists($type, $this->typeList)) {
            throw new \InvalidArgumentException("$type is not valid vehicle");
        }
        $className = $this->typeList[$type];

        return new $className();
    }
}