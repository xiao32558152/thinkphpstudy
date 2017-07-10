<?php

namespace app\api\model;

use app\lib\enum\TopicStatusEnum;
use think\Model;
use think\Request;
use app\api\model\User;
use app\api\model\Question as QuestionModel;
use app\api\model\Answer as AnswerModel;

class Topic extends Model
{
    //
    protected $hidden = ['question_id', 'isPublic', 'user_id', 'answer_user_id', 'stop_time', 'createtime'];

    public function question()
    {
        return $this->belongsTo('Question', 'question_id', 'id');
    }

    public function answers()
    {
        // 只返回最新的一个答案
         return $this->hasMany('Answer', 'topic_id', 'id')->order('answer_time', 'desc')->limit(1);
    }
    
    public function askuser()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function answeruser()
    {
        return $this->belongsTo('User', 'answer_user_id', 'id');
    }

    public static function getTopic($id, $sort, $grade, $subject, $status, $isPublic)
    {
        self::where('stop_time', '<=', date('Y-m-d H:i:s',time()))->update(['status' => '9']);
        
        // 设置speak的user_id
        // for ($x=0; $x<=106; $x++) 
        // {
        //     $topic = self::get($x);
        //     if ($topic)
        //     {
        //         $question = Question::get($topic->question_id);
        //         if ($question) 
        //         {
        //             $qSpeak = Speak::get($question->speak_id);
        //             if ($qSpeak)
        //             {
        //                 $qSpeak->user_id = $topic->user_id;
        //                 $qSpeak->save();
        //             }
        //         }
        //     }
        // }
        $banner = self::with(['question','askuser', 'question.speak','question.speak.image']);
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
        if ($status == TopicStatusEnum::WAIT_ANSWER)
        {
            $banner = $banner->where('status', '=', $status);
        }
        else if ($status == 8 && $isPublic)
        {
            $banner = $banner->where('status', '=', $status);
            $banner = $banner->where('isPublic', '=', 1);
        }
        // $banner = $banner->where('stop_time', '>', date('Y-m-d H:i:s',time()));
        if ($sort == 1)
        {
            $banner = $banner->order('id', 'desc');
        }
        else if ($sort == 2)
        {
            $banner = $banner->order('price', 'desc');
        }

        // 这种方式image和user都可以返回
        // $banner = $banner->page($id,10)->join('user u','topic.user_id = u.id')->select();
        $banner = $banner->page($id,10)->select();
        return $banner;
    }

    public static function getMyTopic($id, $type, $userID)
    {
        self::where('stop_time', '<=', date('Y-m-d H:i:s',time()))->update(['status' => '9']);

        $banner = self::with(['question','question.speak','question.speak.image']);

        if ($type == 0)
        {// 我问
            $banner = $banner->where('user_id', '=', $userID);
        }
        else
        {// 我答
            $banner = $banner->where('answer_user_id', '=', $userID);
        }
        
        $banner = $banner->order('id', 'desc');
        $banner = $banner->page($id,10)->select();
        
        return $banner;
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
        $answer = self::with(['answers','answeruser', 'answers.speak','answers.speak.image'])
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
