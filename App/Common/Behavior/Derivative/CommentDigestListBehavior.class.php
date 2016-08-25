<?php
namespace Common\Behavior\Derivative;

use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class CommentDigestListBehavior extends CommonBehavior{

  const FIELDS = 'id, userid, topid as relation_id, addtime, type, userid as user_id, addtime as add_time';

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
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
    #获取消息总数
    $data['message_sum'] = M('newcomments')->where($where)->count();
    if(empty($data['message_sum']))
      return array(
        'message_sum' => 0,
        'message' => array(),
      );

    #获取消息列表
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $rows = M('newcomments')->field(self::FIELDS)->where($where)
           ->order('addTime desc')->limit($limit)
           ->select();
    #获取赞我的用户ian
    foreach ($rows as $key => $value)
      $user_ids[] = $value['userid'];
    $ians = UserIAN::dozen($user_ids);
    #mapping
    foreach ($rows as $key => $value) {
      if (isset($ians[$value['userid']]) ) {
        $ian = $ians[$value['userid']];
        $rows[$key]['user_name'] = $ian['user_name'];
        $rows[$key]['user_avatar'] = $ian['user_avatar'];
      } else {
        $rows[$key]['user_name'] = '';
        $rows[$key]['user_avatar'] = UserIAN::empty_avatar();
      }
      # 封面图
      $type = array_search($value['type'], C('type'));
      $prop = LikeDigestListBehavior::get_prop($type, $value['relation_id']);
      $rows[$key]['type'] = $type;
      $rows[$key]['photo_url']   = $prop['photo_url'];
      $rows[$key]['father_type'] = $prop['father_type'];
      $rows[$key]['father_relation_id']   = $prop['father_relation_id'];
      #unset
      $arr = array('id', 'is_know');
      foreach ($arr as $k => $v)
        unset($rows[$key][$v]);
    }
    $data['message'] = $rows;
    return $data;
  }

  private static function read_message(){
    $where = array();
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
      $where['is_know'] = 0;
      $where['addTime'] = array('lt', time());
    M('newcomments')->where($where)->setField('is_know', 1);
  }
}
