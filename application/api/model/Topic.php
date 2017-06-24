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
        $banner = self::with(['question','question.speak','question.speak.image']);
        // 是否是全部年级
        if ($grade != 0)
        {
            $banner = $banner->where('grade', '=', $grade);
        }
        // 是否是全部学科
        if ($subject != 0)
        {
            $banner = $banner->where('subject', '=', $subject);
        }
        if ($sort == 1)
        {
            $banner = $banner->order('id', 'desc');
        }
        else if ($sort == 2)
        {
            $banner = $banner->order('price', 'desc');
        }

        $banner = $banner->page($id,10)->select();
        
        return $banner;
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
