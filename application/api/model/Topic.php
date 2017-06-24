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

    public static function getTopic($id, $sort, $grade, $subject)
    {
        if ($sort == 0)
        {
            $banner = self::with(['question','question.speak','question.speak.image'])->where('grade', '=', $grade)->where('subject', '=', $subject)->page($id,10)
            ->select();

            return $banner;
        }
        else if ($sort == 1)
        {
            $banner = self::with(['question','question.speak','question.speak.image'])->order('id', 'desc')->page($id,10)
            ->select();

            return $banner;
        }
        else
        {
            $banner = self::with(['question','question.speak','question.speak.image'])->order('price', 'desc')->page($id,10)
            ->select();

            return $banner;
        }
    }

    public function answers()
    {
        // 只返回最新的一个答案
        return $this->hasMany('Answer', 'topic_id', 'id')->order('answer_time', 'desc')->limit(1);
    }

    public static function createTopic()
    {
        $stopTime = input('post.stoptime');

        $question = new QuestionModel;
        $question->speak_id = $question->createSpeak();
        $question->stop_time = $stopTime;
        $question->ask_time = date('Y-m-d H:i:s',time());
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
        $answer->answer_time = date('Y-m-d H:i:s',time());
        $answer->save();
        return $answer->id;
    }
}
