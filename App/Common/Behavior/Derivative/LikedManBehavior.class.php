<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class LikedManBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('type', 'relation_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = C('type.'.strtolower($_REQUEST['type']) );
    if (empty($type)) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }
    $result->data = self::fetch();
    return $result;
  }

  static function fetch(){
    $map = array();
      $map['relation_id'] = $_REQUEST['relation_id'];
      $map['type'] = C('type.'.strtolower($_REQUEST['type']) );
    $rows = M('user_liked')->field('user_id, add_time')
            ->where($map)
            ->order('add_time desc')
            ->select();
    foreach ($rows as $key => $value) {
      $user_ids[] = $value['user_id'];
    }
    $ian = UserIAN::dozen($user_ids);
    foreach ($rows as $i => $r) {
      # user_name user_avatar
      if (isset($ians[$r['user_id']]) ) {
        $ian = $ians[$r['user_id']];
        $rows[$i]['user_name'] = $ian['user_name'];
        $rows[$i]['user_avatar'] = $ian['user_avatar'];
      }
      else {
        $rows[$i]['user_name'] = '';
        $rows[$i]['user_avatar'] = UserIAN::empty_avatar();
      }
    }
    return $rows;
  }
}
