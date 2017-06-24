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

class Topic extends BaseController
{
    public function getTopic($id)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic();
        return $banner;
    }

    public function createTopic($id)
    {
        $topic = new TopicModel;
        $topic->question_id = $topic->createTopic();
        $topic->grade = input('post.grade');
        $topic->subject = input('post.subject');
        $topic->create_time = date('Y-m-d H:i:s',time());
        $topic->save();
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
    	return $answer_id;
    }
}