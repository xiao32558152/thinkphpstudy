<?php

namespace app\api\model;

use think\Model;
use think\Request;
use app\api\model\Speak as SpeakModel;

class Question extends Model
{
    //
    public function speak()
    {
        return $this->belongsTo('Speak', 'speak_id', 'id');
    }

    public function createSpeak()
    {
        $speak = new SpeakModel;
        $speak->title = input('post.title');
        $speak->content = input('post.content');
        $speak->image_url = input('post.imageurl');
        $speak->save();
        return $speak->id;
    }
}
