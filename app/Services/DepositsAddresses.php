<?php

namespace App\Services;

use App\Services\BaseService;

use App\Models\DepositsAddressesModel;
use App\Models\CurrencyModel;
use App\Models\Erc20TokensModel;
use App\Models\UserModel;
use App\Coin;
use Illuminate\Support\Facades\Storage;
use DB;

class DepositsAddresses extends BaseService
{
    /**
     * 获取虚拟货币充值地址
     * @param  [type] $uid      [description]
     * @param  [type] $currency [description]
     * @return [type]           [description]
     */
    public function getUserAddress($uid, $coinType)
    {
        if(empty($uid)) {
            return $this->error(__('api.account.lack_user'));
        }
        if(empty($coinType)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $currency = $this->getCurrencyModel()->getByCode($coinType);

        if(empty($currency) || is_null($currency)) {
            return $this->error(__('api.account.lack_currency'));
        }
        $currency = $currency->toArray();

        $addressModel = $this->getDepositsAddressesModel()
                             ->setCurrency($currency['id']);

        $address = $addressModel->getInfoByUid($uid);

        if(is_null($address))
            $address = $this->createAddress($uid, $currency['id']);
        if(!empty($address)) {
            $address = $address->toArray();
        }
        $address['coin']    = $coinType;
        $address['logo']    = $currency['logo'] ? Storage::disk('public')->url($currency['logo']) : '';
        $address['explain'] = __('api.account.tip_explain', ['coin' => $coinType, 'number' => $currency['confirmations']]);
        $address['tip']     = __('public.account.tip_title');
        return $this->success($address);
    }

    public function createAddress($uid, $currency) 
    {
        $addressModel = $this->getDepositsAddressesModel();
        
        $type = $this->getCurrencyType($currency);
        if(is_null($type)) {
            return null;
        }
        
        
        $erc20TokensModel = new Erc20TokensModel();
        $isHas = $erc20TokensModel->checkCurrency($currency);
        if($isHas) {
            $ethId   = $this->getCurrencyModel()->getIdByCode('ETH');
            $address = $addressModel->setCurrency($ethId)->getAddressByUid($uid);
            if(!empty($address)) {
                return $address;
            }else {
                $type = 'ETH';
                $currency = $this->getCurrencyModel()->getIdByCode($type);
            }
        } 


        $address = $this->getCoin()->getnewaddress($type);
        
        if(is_null($address)) {
            return null;
        }

        $id = $addressModel->createAddress([
                    'uid'      => $uid,
                    'currency' => $currency,
                    'address'  => $address,
                    'password' => ''
                ]);
        return $addressModel->getInfo($id);
    }

    public function getCurrencyType($currency)
    {
        $type = $this->getCurrencyModel()->getInfo($currency);

        if(!is_null($type)) {
            return $type->code;
        } else {
            return null;
        }
    }

    private function getDepositsAddressesModel()
    {
        return new DepositsAddressesModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getCoin()
    {
        $server = env('WALLET_HOST') . ':' . env('WALLET_PORT');
        return new Coin($server);
    }
}
