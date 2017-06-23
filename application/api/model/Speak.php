<?php

namespace app\api\model;

use think\Model;

class Speak extends Model
{
    //
    public function image()
    {
        return $this->hasMany('Image', 'speak_id', 'id');
    }
}
