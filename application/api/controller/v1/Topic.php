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

class Topic extends BaseController
{
    public function getTopic($id, $sort, $grade, $subject)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic($id, $sort, $grade, $subject, 0, 0);
        Log::write("get topic", 'log');
        return $banner;
    }

	public function getPublicTopic($id, $sort, $grade, $subject)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic($id, $sort, $grade, $subject, TopicStatusEnum::PAID, 1);
        return $banner;
    }
    public function getChargeBackTopic($id, $sort, $grade, $subject)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic($id, $sort, $grade, $subject, TopicStatusEnum::CHARGE_BACK, 0);
        return $banner;
    }

    public function getMyTopic()
    {
        $page = input('post.page');
        $type = input('post.type');
        $uid = TokenService::getCurrentUid();
        $user = User::get($uid);
        if(!$user){
            throw new UserException([
                'code' => 404,
                'msg' => '该用户不存在',
                'errorCode' => 60001
            ]);
        }
        $banner = TopicModel::getMyTopic($page, $type, $user->id);
        return $banner;
    }

    public function createTopic($id)
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
    	$price = input('post.price');
        $stopTime = input('post.stoptime');
        $topic = new TopicModel;
        $topic->question_id = $topic->createTopic();
        $topic->grade = input('post.grade');
        $topic->subject = input('post.subject');
        $topic->stop_time = $stopTime;
		$topic->createtime = date('Y-m-d H:i:s',time());
		$topic->price = $price;
        $topic->user_id = $user->id;
        $topic->status = -1;
        $topic->save();

        $user_topic = new UserTopicModel;
        $user_topic->user_id = $user->id;
        $user_topic->topic_id = $topic->id;
        $user_topic->role = 1;
        $user_topic->save();

        return $topic->id;
    }

	public function rushTopic()
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

        // 检查下是否已经被其他用户抢走了
        $topic_id = input('post.topic_id');
        $topic = TopicModel::get($topic_id);
        if ($topic->status != 0)
        {
        	return -1;
        }

        // 设置抢答状态
        $topic->status = 1;
        $topic->answer_user_id = $user->id;
        $topic->save();

        return 0;
	}

    public function setTopicStatus()
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
        $topicStatus = input('post.status');
        if ($topicStatus == TopicStatusEnum::NEED_MODIFY)
        {
            // 检查下是否是该用户提问的
            $topic_id = input('post.topic_id');
            $topic = TopicModel::get($topic_id);
            if ($topic->user_id != $user->id)
            {
                throw new UserException([
                    'code' => 404,
                    'msg' => '该问题不是该用户提问的',
                    'errorCode' => 60002
                ]);
            }
            // 检查下状态是否是已回答待确认或者已修改待确认
            if ($topic->status != TopicStatusEnum::HAS_ANSWERED && 
                $topic->status != TopicStatusEnum::HAS_MODIFIED)
            {
                throw new UserException([
                    'code' => 404,
                    'msg' => '该问题不能将状态改成需要修改',
                    'errorCode' => 60003
                ]);
            }
            
            $topic->status = TopicStatusEnum::NEED_MODIFY;
            $topic->status_description = input('post.content');
            $topic->save();
        }
        else if ($topicStatus == TopicStatusEnum::CHARGE_BACK)
        {
            // 检查下是否是该用户提问的
            $topic_id = input('post.topic_id');
            $topic = TopicModel::get($topic_id);
            if ($topic->user_id != $user->id)
            {
                throw new UserException([
                    'code' => 404,
                    'msg' => '该问题不是该用户提问的',
                    'errorCode' => 60002
                ]);
            }

            // 设置成退单状态
            $topic->status = TopicStatusEnum::CHARGE_BACK;
            $topic->status_description = input('post.content');
            $topic->save();

            // 新建一条申诉退单记录
            // $complain = new ComplainModel();
            // $complain->topic_id = $topic_id;
            // $complain->user_id = $user->id;
            // $complain->description = "退单,原因:".input('post.content');
            // $complain->save();
        }
        else if ($topicStatus == TopicStatusEnum::CHARGE_BACK_DONE)
        {
            $topic_id = input('post.topic_id');
            $topic = TopicModel::get($topic_id);

            // 设置成退单完成状态
            $topic->status = TopicStatusEnum::CHARGE_BACK_DONE;
            $topic->status_description = input('post.content');
            $topic->save();

            // 新建一条待打钱记录
            $withdraw = new WithdrawModel();
            $withdraw->user_id = $user->id;
            $withdraw->money = $topic->price;
            $withdraw->status = 0; // 0表示未处理
            $withdraw->save();
        }
        
        return 0;
    }

    public function payTopic()
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

        // 检查下是否是该用户提问的
        $topic_id = input('post.topic_id');
        $topic = TopicModel::get($topic_id);
        if ($topic->user_id != $user->id)
        {
            throw new UserException([
                'code' => 404,
                'msg' => '该问题不是该用户提问的',
                'errorCode' => 60002
            ]);
        }
        // 检查下状态是否是已回答待确认
        if ($topic->status != TopicStatusEnum::HAS_ANSWERED)
        {
            throw new UserException([
                'code' => 404,
                'msg' => '该问题还不能确认支付',
                'errorCode' => 60003
            ]);
        }
        // 设置成完成付款状态
        $topic->status = TopicStatusEnum::PAID;
        $topic->save();

        // 钱给到用户账户
        $user->money = $user->money + $topic->price;
        $user->save();
        return 0;
    }
    
    public function complainTopic()
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

        // 检查下是否是该用户提问或者回答的
        $topic_id = input('post.topic_id');
        $topic = TopicModel::get($topic_id);
        if ($topic->user_id != $user->id && $topic->answer_user_id != $user->id)
        {
            throw new UserException([
                'code' => 404,
                'msg' => '该问题不是该用户提问或回答的',
                'errorCode' => 60002
            ]);
        }
        // 检查下状态是否是已回答待确认
        if ($topic->status != TopicStatusEnum::HAS_ANSWERER && 
            $topic->status != TopicStatusEnum::HAS_ANSWERED &&
            $topic->status != TopicStatusEnum::NEED_MODIFY &&
            $topic->status != TopicStatusEnum::WAIT_MODIFY &&
            $topic->status != TopicStatusEnum::WAIT_MODIFY)
        {
            throw new UserException([
                'code' => 404,
                'msg' => '该问题不能申诉',
                'errorCode' => 60003
            ]);
        }
        // 设置成申诉状态
        $topic->status = TopicStatusEnum::COMPLAIN;
        $topic->save();

        // 新建一条申诉记录
        $complain = new ComplainModel();
        $complain->topic_id = $topic_id;
        $complain->user_id = $user->id;
        $complain->description = input('post.content');
        $complain->save();
        return 0;
    }

    public function getAnswer($id)
    {
    	$answers = TopicModel::getAnswerByTopicID($id);
    	return $answers;
    }

    public function setAnswer()
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

    	$topic_id = input('post.topic_id');
        $topic = TopicModel::get($topic_id);
        // 只有该topic是抢答状态或者需要修改的状态，且该用户就是抢答用户才可以回答
        if (($topic->status != TopicStatusEnum::HAS_ANSWERER && $topic->status != TopicStatusEnum::NEED_MODIFY) || 
        $topic->answer_user_id != $user->id)
        {
            return "fail";
        }
        $answer_id = TopicModel::setAnswerByTopicID($topic_id);
        if ($topic->status != TopicStatusEnum::HAS_ANSWERER)
        {
            $topic->status = TopicStatusEnum::HAS_ANSWERED;
        }
        else
        {
            $topic->status = TopicStatusEnum::NEED_MODIFY;
        }
    	$topic->save();

		// 该话题增加了一位参与的用户
    	$user_topic = new UserTopicModel;
        $user_topic->user_id = $user->id;
        $user_topic->topic_id = $topic->id;
        $user_topic->role = 1;
        $user_topic->save();

    	return $answer_id;
    }
}