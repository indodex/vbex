<?php

namespace App\Http\Proxy;
use App\Models\LoginLogModel;
use Illuminate\Http\Request;

class TokenProxy {

    protected $http;

    /**
     * TokenProxy constructor.
     * @param $http
     */

    public function __construct(\GuzzleHttp\Client $http)
    {
        $this->http = $http;
    }

    public function login($email, $password,$uid = 0)
    {
        if (auth()->attempt(['email' => $email, 'password' => $password])) {
            return $this->proxy('password', [
                'username' => $email,
                'password' => $password,
                'uid' => $uid,
                'scope'    => '',
            ]);
        }

        return response()->json([
            'code' => 421,
            'message' => __('api.member.credentials_not_match'),
            'data' => []
        ], 202);
    }

    public function refresh()
    {
        $refreshToken = request()->cookie('refreshToken');

        return $this->proxy('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);
    }

    public function logout()
    {
        $user = auth()->guard('api')->user();
        if (is_null($user)) {
            app('cookie')->queue(app('cookie')->forget('refreshToken'));

            return response()->json([
                'message' => 'Logout!',
            ], 204);
        }

        $accessToken = $user->token();

        app('db')->table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true,
            ]);

        app('cookie')->queue(app('cookie')->forget('refreshToken'));

        $accessToken->revoke();

        return response()->json([
            'message' => 'Logout!',
        ], 204);

    }

    public function proxy($grantType, array $data = [])
    {

        $data = array_merge($data, [
            'client_id'     => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'grant_type'    => $grantType,
        ]);
        $client = new \GuzzleHttp\Client(['verify' => false]);
        // //$this->http->setDefaultOption('verify', false);
        $response = $client->post(env('APP_URL').'/oauth/token', [
            'form_params' => $data,
        ]);
        $token = json_decode((string)$response->getBody(), true);
            
        //$token = json_decode((string)$response->getBody(), true);
        // $response = self::curl('https://hash.tw/oauth/token', $data, true,true);
        //www.trading.com
        // $response = self::curl('http://www.trading.comoauth/oauth/token', $data, true,1);
        // // $response = $this->curl_request('https://hash.tw/oauth/token', $data);
        // $token = json_decode($response,1);
        LoginLogModel::create(['uid'=>$data['uid'],'ip'=>\Request::getClientIp(),'login_time'=>time()]);
        return response()->json([
            'code'       => 200,
            'token'      => $token['access_token'],
            'auth_id'    => md5($token['refresh_token']),
            'expires_in' => $token['expires_in'],
        ])->cookie('refreshToken', $token['refresh_token'], 1440000, null, null, false, true);
    }


    //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
    function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        // curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        dd($data);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
}