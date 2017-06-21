<?php

namespace app\api\model;

use think\Model;

class Question extends Model
{
    //
    public function speak()
    {
        return $this->belongsTo('Speak', 'speak_id', 'id');
    }
}
