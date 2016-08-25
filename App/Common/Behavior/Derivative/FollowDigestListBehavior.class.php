<?php
namespace Common\Behavior\Derivative;

use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class FollowDigestListBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    self::pick_page_params();
    $data = self::message_list();
    $result->count = $data['message_sum'];
    $result->data = $data['message'];
    self::read_message();
    return $result;
  }

  private static function message_list(){
    $where = array();
      $where['followUser'] = $_REQUEST['my_user_id'];
    #获取消息总数
    $data['message_sum'] = M('user_follow')->where($where)->count();
    if(empty($data['message_sum']))
      return array(
        'message_sum' => 0,
        'message' => array(),
      );

    #获取消息列表
    #limit
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $rows = M('user_follow')
            ->field('userId, addTime, userId as user_id, addTime as add_time')
            ->where($where)
            ->order('addTime desc')
            ->limit($limit)
            ->select();
    #获取赞我的人的用户信息
    foreach ($rows as $key => $value) {
      $user_ids[] = $value['userid'];
    }
    $ians = UserIAN::dozen($user_ids);
    foreach ($rows as $key => $value) {
      # user_name user_avatar
      if (isset($ians[$value['userid']]) ) {
        $ian = $ians[$value['userid']];
        $rows[$key]['user_name'] = $ian['user_name'];
        $rows[$key]['user_avatar'] = $ian['user_avatar'];
      } else {
        $rows[$key]['user_name'] = '';
        $rows[$key]['user_avatar'] = UserIAN::empty_avatar();
      }
    }
    $data['message'] = $rows;
    return $data;
  }

  private static function read_message(){
    $where = array();
      $where['followUser'] = $_REQUEST['my_user_id'];
      $where['isKnow'] = 0;
      $where['addTime'] = array('lt', time());
    M('user_follow')->where($where)->setField('isKnow', 1);
  }
}
