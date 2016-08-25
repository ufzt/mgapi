<?php
namespace Common\Molecule\User;

class UserFans{

  const FIELDS = 'userid as fan_id, addTime as follow_time';

  static function sum($single_user_id){
    $map = array();
      $map['followUser']  = array('in', $single_user_id);
    return M('user_follow')->where($map)
           ->count();
  }

  static function fetch($single_user_id, $size, $page=1){
    $offset = $size * ($page -1);
    $limit  = $offset.','.$size;
    $map = array();
      $map['followUser']  = array('in', $single_user_id);
    $rows = M('user_follow')->field(self::FIELDS)->where($map)
            ->order('addTime desc')->limit($limit)
            ->select();
    if (empty($rows))
      return array();

    return $rows;
  }
}
