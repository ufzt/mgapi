<?php
namespace Common\Behavior\Activity;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;

class InviteSubmitStepTwoBehavior extends CommonBehavior{

  static function commit() {
     # 检查必填参数
    $inquire_params = array('sn_code','password','dkey');
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

    self::update();
    return $result;
  }

  static function update(){
    $where = array();
      $where['sn_code'] = $_REQUEST['sn_code'];
    $row = M('app_invite', NULL)->field('user_id')->where($where)->find();
    if(!empty($row)){
      $where = array();
        $where['userId'] = $row['user_id'];
      $data = array();
        $data['userPass'] = $_REQUEST['password'];
      M('user')->where($where)->save($data);
    }
  }
}
