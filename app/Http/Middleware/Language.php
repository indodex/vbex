<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;
use Closure;
use App;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $languageArr = array('en','zh_TW', 'zh_CN');
        $curee = isset($_COOKIE['lang']) ? $_COOKIE['lang']:'';
        if($curee == 'zh-cn'){
            $curee = 'zh_CN';
        } else if ($curee == 'zh-tw') {
            $curee = 'zh_TW';
        }
        if(isset($curee)){
            $locale = in_array($curee,$languageArr) ? $curee : 'zh_CN';
        } else {
            $locale = get_brower_lang();
        }
        App::setLocale($locale);
        return $next($request);
    }
}
