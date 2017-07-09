<?php

namespace app\api\model;

use think\Model;
use think\Request;
use app\api\model\Speak as SpeakModel;
use app\api\model\Image as ImageModel;
use app\api\service\Token as TokenService;

class Question extends Model
{
    
    protected $hidden = ['id', 'speak_id'];

    public function speak()
    {
        return $this->belongsTo('Speak', 'speak_id', 'id');
    }

    public function createSpeak()
    {
        $uid = TokenService::getCurrentUid();
        $user = User::get($uid);
        if(!$user){
            throw new UserException([
                'code' => 404,
                'msg' => '该用户不存在',
                'errorCode' => 60001
            ]);
        }

        $speak = new SpeakModel;
        $speak->title = input('post.title');
        $speak->user_id = $user->id;
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
