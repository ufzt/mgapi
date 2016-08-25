<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;
use Common\Molecule\Users\UsersFollowUsers;

class userIAN2 extends userIAN{
  static function fields(){
    return 'userid as user_id, username as user_name, userface as user_avatar, introduction, fans';
  }
}

class TopMakersBehavior extends CommonBehavior{
  static function commit(){
    $result = new Result(true);
    self::pick_my_user_id();
    # 推荐创客列表
    $result->data = self::makers_list();
    return $result;
  }

  static function makers_list(){
    $where = array();
      $where['recommend_time'] = array('GT', 0);
    $user_list = M('user')->field('userId as user_id')->where($where)->order('fans desc')->limit('10')->select();
    foreach ($user_list as $key => $value) {
      $user_ids[] = $value['user_id'];
    }
    
    $user = userIAN2::dozen($user_ids);
    $user = array_values($user);
    foreach ($user as $key => $value){
      $user[$key]['is_follow'] = false;
      if(empty($user[$key]['introduction'])) $user[$key]['introduction']='';
    }
    #判断对这些用户是否关注
    if ($_REQUEST['my_user_id'] > 1){
      $user_unfollow = UsersFollowUsers::follow_makers($_REQUEST['my_user_id'], $user_ids);
      foreach ($user as $key => $value) {
        if(in_array($value['user_id'], $user_unfollow))
          $user[$key]['is_follow'] = false;
        else
          $user[$key]['is_follow'] = true;
      }
    }
    return $user;
  }
}
