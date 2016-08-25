<?php
namespace Common\Molecule\Users;

class UsersFollowUsers{
  const ABSORB = '-1';
  const FOLLOW =  '1';

  # 吸粉
  # user 吸粉 fans's ids
  static function absorb_fans($user_id, $fan_ids){
    return self::follow(self::ABSORB, $user_id, $fan_ids);
  }
  
  # 关注
  # user 关注 makers's ids
  static function follow_makers($user_id, $maker_ids){
    return self::follow(self::FOLLOW, $user_id, $maker_ids);
  }


  private static function follow($type, $single_user_id, $user_ids){
    $user_ids = self::filter($type, $single_user_id, $user_ids);
    return $user_ids;
  }

  private static function filter($type, $single_user_id, $user_ids){
    if (!is_numeric($single_user_id))
      trigger_error('$single_user_id is NOT legal, NOT a number!', E_USER_ERROR);
    if (!is_array($user_ids))
      trigger_error('$user_ids is NOT legal, NOT an Array!', E_USER_ERROR);

    # 查询已存在的follow
    $map = array();
      if ($type == self::ABSORB) {
        $map['followUser'] = $single_user_id;
        $map['userId']     = array('in', $user_ids);
      } else if ($type == self::FOLLOW){
        $map['userId']     = $single_user_id;
        $map['followUser'] = array('in', $user_ids);
      } else {
        trigger_error('$type is NOT legal!', E_USER_ERROR);
      }
    $rows = M('user_follow')->where($map)->select();
    if (!empty($rows)) {
      $existed_ids = array();
      foreach ($rows as $r) {
        if ($type == self::ABSORB)       $existed_ids[] = $r['userid'];
        else if ($type == self::FOLLOW)  $existed_ids[] = $r['followuser'];
      }
      # 过滤
      $user_ids = array_diff($user_ids, $existed_ids);
    }
    return $user_ids;
  }
}
