<?php
namespace Common\Behavior\Activity;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;

class InviteCreateBehavior extends CommonBehavior{
  const WEI_DA_KAI = 0;

  static function sn_code(){
    return date('ymd').Seed::rand(4).date('His').Seed::rand(4);
  }

  static function commit() {
     # 检查必填参数
    $inquire_params = array('user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $where = array('user_id' => $_REQUEST['user_id']);
    $row = M('app_invite', NULL)->where($where)->find();

    if (empty($row))
      $result->sn_code = self::add();
    else {
      $data = array();
        $data['id']      = $row['id'];
        $data['user_id'] = $row['user_id'];
        $data['status']  = self::WEI_DA_KAI;
        $data['sn_code'] = self::sn_code();
        $data['add_time'] = time();
        $data['update_time'] = time();
      M('app_invite', NULL)->save($data);
      $result->sn_code = $data['sn_code'];
    }
    return $result;
  }

  static function add(){
    $data = array();
        $data['user_id'] = $_REQUEST['user_id'];
        $data['status']  = self::WEI_DA_KAI;
        $data['sn_code'] = self::sn_code();
        $data['add_time'] = time();
        $data['update_time'] = time();
    M('app_invite', NULL)->data($data)->add();
    return $data['sn_code'];
  }
}
