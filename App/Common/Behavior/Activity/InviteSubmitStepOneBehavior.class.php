<?php
namespace Common\Behavior\Activity;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;

class InviteSubmitStepOneBehavior extends CommonBehavior{

  static function commit() {
    # 检查必填参数
    $inquire_params = array('sn_code','verify_code','phone','dkey');
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
    $data = InviteVisitBehavior::fetch();
    if (empty($data)) {
      $result->res = false;
      $result->msg = '邀请码已过期';
      return $result;
    }

    # 一致性确认
    $where = array();
      $where['sn_code'] = $_REQUEST['sn_code'];
      $where['phone'] = $_REQUEST['phone'];
      $where['verify_code'] = $_REQUEST['verify_code'];
    $row = M('app_invite', NULL)->where($where)->find();
    if (empty($row)) {
      $result->res = false;
      $result->msg = '验证码不正确';
      return $result;
    }

    # 保存数据
    self::update($row['user_id']);
    $result->success();
    return $result;
  }

  static function update($user_id){
    #app_invite
    $where = array();
      $where['sn_code'] = $_REQUEST['sn_code'];
    $data = array();
      $data['update_time'] = time();
      $data['verify_code'] = '_OK_';
    M('app_invite', NULL)->where($where)->save($data);
    #site_user
    $where = array();
      $where['userId'] = $user_id;
    $data = array();
      $data['mobile'] = $_REQUEST['phone'];
    M('user')->where($where)->save($data);
  }
}
