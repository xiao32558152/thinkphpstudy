<?php

namespace app\api\model;

use think\Model;

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
}
