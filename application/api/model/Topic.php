<?php

namespace app\api\model;

use think\Model;
use think\Request;
use app\api\model\Question as QuestionModel;
use app\api\model\Answer as AnswerModel;

class Topic extends Model
{
    //
    public function question()
    {
        return $this->belongsTo('Question', 'question_id', 'id');
    }

    public static function getTopic()
    {
        $banner = self::with(['question','question.speak','question.speak.image'])
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
    public static function setAnswerByTopicID($id)
    {
        $answer = new AnswerModel;
        $answer->speak_id = $answer->createSpeak();
        $answer->topic_id = $id;
        $answer->save();
        return $answer->id;
    }
}
