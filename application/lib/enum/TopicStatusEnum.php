<?php
/**
 * Created by PhpStorm.
 * User: bnu
 * Date: 2017/7/1
 * Time: 21:47
 */

namespace app\lib\enum;


class TopicStatusEnum
{
    const WAIT_PAY = -1; // 已创建未支付

    const WAIT_ANSWER = 0; // 待回答

    const HAS_ANSWERER = 1;  // 被抢答

    const HAS_ANSWERED = 2; // 已回答待确认

    const NEED_MODIFY = 3; // 需要再修改

    const CHARGE_BACK = 4; // 退单

    const WAIT_MODIFY = 5; // 待修改

    const HAS_MODIFIED = 6; // 已修改待确认

    const COMPLAIN = 7; // 申诉

    const PAID = 8; // 已确认付款

    const EXPIRED = 9; // 已过期且无人回答
}