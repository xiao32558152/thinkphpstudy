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
use think\Log;

class Topic extends BaseController
{
    public function getTopic($id, $sort, $grade, $subject)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic($id, $sort, $grade, $subject, 0, 0);
        Log::record("get topic", 'log');
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
    	$price = input('post.price');
        $topic = new TopicModel;
        $topic->question_id = $topic->createTopic();
        $topic->grade = input('post.grade');
        $topic->subject = input('post.subject');
		$topic->createtime = date('Y-m-d H:i:s',time());
		$topic->price = $price;
		$topic->user_id = input('post.userid');
        $topic->save();

        $user_topic = new UserTopicModel;
        $user_topic->user_id = input('post.userid');
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
    	$topic_id = input('post.topic_id');
    	$answer_id = TopicModel::setAnswerByTopicID($topic_id);
    	$topic = TopicModel::get($topic_id);
    	$topic->status = 2; // 回答完成待确认
    	$topic->save();

    	$user_topic = new UserTopicModel;
        $user_topic->user_id = input('post.userid');
        $user_topic->topic_id = $topic->id;
        $user_topic->role = 2;
        $user_topic->save();

    	return $answer_id;
    }
}