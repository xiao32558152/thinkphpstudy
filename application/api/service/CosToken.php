<?php
/**
 * Created by PhpStorm.
 * User: bnu
 * Date: 2017/6/26
 * Time: 13:18
 */

namespace app\api\service;
use app\api\model\ThirdApp;
use app\lib\exception\TokenException;
use think\Exception;

class CosToken extends Token
{
    public function get()
    {
        $appid = "1253759887";
		$bucket = "wxupload";
		$secret_id = "AKIDgMq8E8rMgoQxISjPFtMgok6GEzb0rE2m";
		$secret_key = "voW2LSN5bv31qSs681iGhHl393kFYeYF";
		$expired = time() + 3600;// 3600秒过期
		$onceExpired = 0;
		$current = time();
		$rdm = rand();
		// $fileid = "/200001/newbucket/tencent_test.jpg";

		$multi_effect_signature = 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$expired.'&t='.$current.'&r='.$rdm.'&f=';

		// $once_signature=
		// 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$onceExpired.'&t='.$current.'&r='.$rdm.'&f='.$fileid;

		$multi_effect_signature = base64_encode(hash_hmac('SHA1', $multi_effect_signature, $secret_key, true).$multi_effect_signature);

		// $once_signature = base64_encode(hash_hmac('SHA1',$once_signature,$secret_key, true).$once_signature);

		// echo $multi_effect_signature."\n"; 

		// echo $once_signature."\n";
		return $multi_effect_signature;
    }
    
    private function saveToCache($values){
        $token = self::generateToken();
        $expire_in = config('setting.token_expire_in');
        $result = cache($token, json_encode($values), $expire_in);
        if(!$result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $token;
    }
}