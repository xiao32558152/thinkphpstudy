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
        $topic = new TopicModel;
        $topic->question_id = $topic->createTopic();
        $topic->grade = input('post.grade');
        $topic->subject = input('post.subject');
		$topic->createtime = date('Y-m-d H:i:s',time());
		$topic->price = $price;
		$topic->user_id = $user->id;
        $topic->save();

        $user_topic = new UserTopicModel;
        $user_topic->user_id = $user->id;
        $user_topic->topic_id = $topic->id;
        $user_topic->role = 1;
        $user_topic->save();

        return $topic->id;
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
    	$answer_id = TopicModel::setAnswerByTopicID($topic_id);
    	$topic = TopicModel::get($topic_id);
    	$topic->status = 2; // 2表示回答完成待确认，8表示完成付款
    	$topic->save();

    	$user_topic = new UserTopicModel;
        $user_topic->user_id = $user->id;
        $user_topic->topic_id = $topic->id;
        $user_topic->role = 2;
        $user_topic->save();

    	return $answer_id;
    }
}