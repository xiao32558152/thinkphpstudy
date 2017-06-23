<?php

namespace app\api\model;

use think\Model;
use app\api\model\Speak as SpeakModel;

class Answer extends Model
{
    public function speak()
    {
        return $this->belongsTo('Speak', 'speak_id', 'id');
    }
}
