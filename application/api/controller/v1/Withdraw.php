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
    public function getWithdrawList($id)
    {
        $list = WithdrawModel::getWithdrawList($id);
        return $list;
    }
    
    public function setWithdrawDone($id)
    {
        $admin = input('post.token');
        if ($admin != 'suda2017')
        {
            return -1;
        }
        $withdraw = WihthdrawModel::get($id);
        $withdraw->status = 1;// 1表示已处理
    }
}