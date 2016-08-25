<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Molecule\Notice\Notice;

class ListNotifyBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $now = time();
    $map = array();
      $map['user_id'] = $_REQUEST['user_id'];
      $map['is_know'] = 0;
    $rows = M('app_notice',NULL)->where($map)
            ->order('add_time desc')
            ->select();
    if (empty($rows)) $rows = array();
    $result->data = self::pack($rows);
    return $result;
  }

  static function pack($rows){
    $dict = Notice::type_name_dict();
    $data = array();
    foreach ($rows as $r) {
      $type = $r['type'];
      $data['title'] = Notice::type_desc($type);
      $data['notice_time'] = $r['add_time'];
      $data['type'] = $dict[$type];
      $data['content'] = $r['content'];
      $data['assoc_id'] = $r['assoc_id'];
    }
    return $data;
  }
}
