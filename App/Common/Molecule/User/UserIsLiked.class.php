<?php
namespace Common\Molecule\User;

class UserIsLiked{

  static function is_liked_by($single_user_id, $facts){
    if (empty($single_user_id)) {
      foreach ($facts as $i => $r) {
        if (!is_array($r)) continue;
        $facts[$i]['is_liked'] = false;
      }
      return $facts;
    }

    # 按同一类型分组
    $_facts = array();
    foreach ($facts as $r) {
      if (!is_array($r)) continue;
      $type = $r['type'];
      # 以 $type 为key的二位数组
      $group = array();
      if (isset($_facts[$type]))
        $group = $_facts[$type];
      $group[] = $r['id'];
      $_facts[$type] = $group;
    }
    # 查询用户是否关注
    foreach ($_facts as $type => $relation_ids) {
      $liked = self::liked_fetch($type, $relation_ids, $single_user_id);
      foreach ($facts as $i => $r) {
        if ($r['type'] != $type) continue;
        $facts[$i]['is_liked'] = in_array($r['id'], $liked);
      }
    }
    return $facts;
  }

  private static function liked_fetch($type, $relation_ids, $user_id){
    $relation_ids = array_unique($relation_ids);
    $map = array();
      $map['user_id'] = $user_id;
      $map['type'] = C('type.'.$type);
      $map['relation_id'] = array('in', $relation_ids);
    $rows = M('user_liked')->field('relation_id')->where($map)
            ->select();
    if (empty($rows)) $rows = array();
    foreach ($rows as $i => $r)
        $rows[$i] = $r['relation_id'];
    return $rows;
  }
}
