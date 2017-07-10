<?php

namespace app\api\model;

use think\Model;
use think\Db;

class Speak extends Model
{
    //
    protected $hidden = ['id', 'user_id', 'voice_id', 'voice_url'];

    public function image()
    {
        return $this->hasMany('Image', 'speak_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }
}
