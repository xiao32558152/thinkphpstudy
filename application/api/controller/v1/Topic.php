<?php
/**
 * Created by PhpStorm.
 * User: bnu
 * Date: 2017/6/21
 * Time: 22:52
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Topic as TopicModel;

class Topic extends BaseController
{
    public function getTopic($id)
    {
        // id为空则返回前20个topic
        $banner = TopicModel::getTopic();
        return $banner;
    }
}