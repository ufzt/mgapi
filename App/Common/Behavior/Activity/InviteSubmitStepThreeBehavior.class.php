<?php
namespace Common\Behavior\Activity;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;

class InviteSubmitStepThreeBehavior extends CommonBehavior{

  static function commit() {
     # 检查必填参数
    $inquire_params = array('sn_code','name','dkey');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 检查dkey
    if (!seed::is_dkey($_REQUEST['dkey'])) {
      $result->res = false;
      $result->msg = '会话已过期';
      return $result;
    }

    # 检查邀请码是否过期
    $data = InviteVisitBehavior::fetch(true);
    if (empty($data)) {
      $result->res = false;
      $result->msg = '邀请码已过期或手机号未验证';
      return $result;
    }

    # 检查用户名是否已存在
    $user_id = M('user')->where(array('userName'=>$_REQUEST['name']))
          ->getfield('userId');
    if ($user_id > 0) {
      $result->res = false;
      $result->msg = '用户名已存在';
      return $result;
    }

    self::update();
    return $result;
  }

  static function update(){
    $where = array();
      $where['sn_code'] = $_REQUEST['sn_code'];
    M('app_invite', NULL)->where($where)
      ->setField('status', InviteVisitBehavior::YI_WAN_CHENG);
    $row = M('app_invite', NULL)->field('user_id')->where($where)->find();
    if(!empty($row)){
      $where = array();
        $where['userId'] = $row['user_id'];
      $data = array();
        $data['userName'] = $_REQUEST['name'];
        if (!empty($_REQUEST['intro']))
        $data['introduction'] = $_REQUEST['intro'];
      M('user')->where($where)->save($data);
    }
  }
}
