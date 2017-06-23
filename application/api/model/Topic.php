<?php

namespace app\api\model;

use think\Model;
use think\Request;
use app\api\model\Question as QuestionModel;

class Topic extends Model
{
    //
    public function question()
    {
        return $this->belongsTo('Question', 'question_id', 'id');
    }

    public static function getTopic()
    {
        $banner = self::with(['question','question.speak'])
            ->select();

        return $banner;
    }

    public function answers()
    {
        return $this->hasMany('Answer', 'topic_id', 'id');
    }

    public static function createTopic()
    {
        $price = input('post.price');
        $stopTime = input('post.stoptime');

        $question = new QuestionModel;
        $question->speak_id = $question->createSpeak();
        $question->price = $price;
        $question->stop_time = $stopTime;
        $question->save();
        return $question->id;
    }
    public static function getAnswerByTopicID($id)
    {
        $answer = self::with(['answers','answers.speak'])
            ->find($id);
        return $answer;
    }
}
