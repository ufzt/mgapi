<?php
namespace Common\Behavior\Activity;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;
use Common\Atom\UserIAN;

class InviteVisitBehavior extends CommonBehavior{
  const WEI_WAN_CHENG = 10;
  const YI_WAN_CHENG  = 20;

  static function commit() {
    # 检查必填参数
    $inquire_params = array('sn_code');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 检查邀请码是否过期
    $data = self::fetch();
    if (empty($data)) {
      $result->res = false;
      $result->msg = '邀请码已过期';
      return $result;
    }

    self::update($data['id']);
    $ian = UserIAN::one($data['user_id']);
    if (isset($ian['user_id']))  unset($ian['user_id']);
    $result->data = $ian;
    $result->dkey = Seed::dkey();
    return $result;
  }

  static function fetch($check_sms=false){
    $map = array();
      $map['sn_code']  = $_REQUEST['sn_code'];
      $map['add_time'] = array('gt', time()-7*86400);
      $map['status']   = array('neq', self::YI_WAN_CHENG);
      if ($check_sms)
        $map['verify_code'] = '_OK_';
    return M('app_invite', NULL)->where($map)->find();
  }

  static function update($invite_id){
    $data = array();
        $data['id'] = $invite_id;
        $data['status'] = self::WEI_WAN_CHENG;
        $data['update_time'] = time();
    return M('app_invite', NULL)->data($data)->save();
  }
}
