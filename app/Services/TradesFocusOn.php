<?php

namespace App\Services;

use App\Services\BaseService;

use Illuminate\Support\Facades\Cache;
use App\Models\TradesCurrenciesModel;
use App\Models\TradesFocusOnModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;

class TradesFocusOn extends BaseService
{

    public function focusOn(int $uid, int $trade_id, string $state = 'follow')
    {

        if($state == 'follow') {
            $result = $this->getTradesFocusOn()->addFocusOn($uid, $trade_id);
        } else if($state == 'unfollow'){
            $result = $this->getTradesFocusOn()->delFocusOn($uid, $trade_id);
        }

        $userFocus = $this->getTradesFocusOn()->getFocusByUid($uid);
        if(!$userFocus->isEmpty()) {
            $focusIds = $userFocus->toArray();
            $focusIds = array_column($focusIds, 'trade_id');
            $keyCache = "tradeFocusOn@{$uid}";
            Cache::forever($keyCache, $focusIds);
        }

        return $result;
    }

    public function getFocusTradeIds($uid)
    {
        $keyCache = "tradeFocusOn@{$uid}";
        if (Cache::has($keyCache)) {
            return Cache::get($keyCache);
        }

        $userFocus = $this->getTradesFocusOn()->getFocusByUid($uid);
        if(!$userFocus->isEmpty()) {
            $focusIds = $userFocus->toArray();
            $focusIds = array_column($focusIds, 'trade_id');
            $keyCache = "tradeFocusOn@{$uid}";
            Cache::forever($keyCache, $focusIds);
            return $focusIds;
        }

        return null;
    }

    private function getTradesCurrencies()
    {
        return new TradesCurrenciesModel();
    }

    private function getTradesFocusOn()
    {
        return new TradesFocusOnModel();
    }

    private function getCueercny()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }
}
