<?php

namespace app\api\model;

use think\Model;
use think\Request;
use app\api\model\Speak as SpeakModel;
use app\api\model\Image as ImageModel;

class Answer extends Model
{
    public function speak()
    {
        return $this->belongsTo('Speak', 'speak_id', 'id');
    }

    public function createSpeak()
    {
        $speak = new SpeakModel;
        $speak->title = input('post.title');
        $speak->content = input('post.content');
        $speak->save();

        // 保存图片
        $images = input('post.imageurl');
        $imageUrls = explode(',',$images);
        for($index = 0; $index < count($imageUrls); $index++) 
        {
        	$image = new ImageModel;
        	$image->from = 2;
        	$image->speak_id = $speak->id;
        	$image->url = $imageUrls[$index];
        	$image->save();
        }
        return $speak->id;
    }
}
