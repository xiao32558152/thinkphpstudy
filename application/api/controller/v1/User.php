<?php
/**
 * Created by PhpStorm.
 * User: bnu
 * Date: 2017/6/21
 * Time: 22:52
 */

namespace app\api\controller\v1;

use app\lib\enum\TopicStatusEnum;
use app\api\controller\BaseController;
use app\api\model\Topic as TopicModel;
use app\api\model\UserTopic as UserTopicModel;
use app\api\model\Complain as ComplainModel;
use app\api\model\Withdraw as WithdrawModel;
use app\api\model\User;
use app\api\service\Token;
use app\api\service\Token as TokenService;
use think\Log;
use app\lib\exception\UserException;

class UserController extends BaseController
{
    public function withdrawMoney()
    {
        $uid = TokenService::getCurrentUid();
        $user = User::get($uid);
        if(!$user){
            throw new UserException([
                'code' => 404,
                'msg' => '该用户不存在',
                'errorCode' => 60001
            ]);
        }
        $money = input('post.money');
        
    }
    
    public function getMoney()
    {
        $uid = TokenService::getCurrentUid();
        $user = User::get($uid);
        if(!$user){
            throw new UserException([
                'code' => 404,
                'msg' => '该用户不存在',
                'errorCode' => 60001
            ]);
        }
    	
    }
}