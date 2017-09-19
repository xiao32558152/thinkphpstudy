<?php
/**
 * Created by PhpStorm.
 * User: bnu
 * Date: 2017/6/21
 * Time: 22:52
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Topic as TopicModel;
use app\api\model\UserTopic as UserTopicModel;
use app\api\model\User;
use app\api\service\Token;
use app\api\service\Token as TokenService;
use think\Log;

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
        $banner = TopicModel::getTopic($id, $sort, $grade, $subject, 8, 1);
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
        if ($topic->status != 2)
        {
            throw new UserException([
                'code' => 404,
                'msg' => '该问题还不能确认支付',
                'errorCode' => 60003
            ]);
        }
        // 设置成完成付款状态
        $topic->status = 8;
        $topic->save();

        // 钱给到用户账户
        $user->money = $user->money + $topic->price;
        $user->save();
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
        // 只有该topic是抢答状态，且该用户就是抢答用户才可以回答
        if ($topic->status != 1 || $topic->answer_user_id != $user->id)
        {
            return "fail";
        }
        $answer_id = TopicModel::setAnswerByTopicID($topic_id);
    	$topic->status = 2; // 2表示回答完成待确认，8表示完成付款
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