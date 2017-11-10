<?php

namespace app\api\model;

use app\lib\enum\TopicStatusEnum;
use think\Model;
use think\Request;
use app\api\model\User;
use app\api\model\Question as QuestionModel;
use app\api\model\Answer as AnswerModel;

class Withdraw extends Model
{
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }

    public function getWithdrawList($pageNum)
    {
        $list = self::with(['user']);
        $list = $list->page($pageNum,10)->select();
        return $list;
    }
    
}
