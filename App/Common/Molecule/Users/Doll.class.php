<?php
namespace Common\Molecule\Users;

use Common\Atom\Result;

# 水军类
class Doll{

  const FIELDS = 'userid as doll_id, shuabang_bucket as bucket_id';

  # 给用户添加特定分组的水军
    # user_id   大V用户(需要加粉的用户)
    # bucket_id 水军分组
    # sum       加粉数量
  static function user_fill_dolls($user_id, $bucket_id, $sum){
    $result = new Result();
    $map = array();
      $map['followUser']      = $user_id;
      $map['shuabang_bucket'] = $bucket_id;
    $row = M('user_follow')->where($map)->find();
    if (!empty($row)) {
      $result->res = false;
      $result->msg = '当前分组已加粉';
      return $result;
    }

    # 分组的所有水军id
    $all_ids = self::bucket_dolls($bucket_id);
    $old_fan_ids = self::user_dolls_in_bucket($user_id, $bucket_id);
    $diff_ids = array_diff($all_ids, $old_fan_ids);
    if (count($diff_ids) > $sum){
      $keys = array_rand($diff_ids, $sum);
      $new_fan_ids = array();
      foreach ($keys as $key)
        $new_fan_ids[] = $diff_ids[$key];
    }
    $data = array();
      $end_time   = time();
      $start_time = time() - 86400*7;
    foreach ($new_fan_ids as $doll_id) {
      $row = array('userId'     => $doll_id,
                   'followUser' => $user_id,
                   'addTime'    => rand($start_time, $end_time),
                   'shuabang_bucket' => $bucket_id );
      $data[] = $row;
    }
    M('user_follow')->addAll($data);
    $result->success();
    return $result;
  }

  # 分组里 所有的水军ids
  static function bucket_dolls($bucket_id){
    $max = 512;# 512*4B = 2KB
    $map = array();
      $map['shuabang_bucket'] = $bucket_id;
      $map['shuabang']        = 1;
    $rows = M('user')->field(self::FIELDS)->where($map)
            ->select();
    if (empty($rows))
      return array();

    $ids = array();
    foreach ($rows as $r)
      $ids[] = $r['doll_id'];
    return $ids;
  }

  # 分组里 用户的 所有水军ids
  static function user_dolls_in_bucket($user_id, $bucket_id){
    $pieces = explode(',', self::FIELDS);
    foreach ($pieces as $i => $p)
      $pieces[$i] = 'f.'.trim($p);
    $fields = implode(',', $pieces);
    $map = array();
      $map['m.shuabang_bucket'] = $bucket_id;
      $map['m.shuabang']        = 1;
      $map['f.followUser']      = $user_id;
    $rows = M('user m')->field($fields)
            ->join('site_user_follow f on m.userId = f.userId')
            ->where($map)
            ->select();
    if (empty($rows))
      return array();

    ###在全面禁止水军的关注功能后，去掉以下代码>>>
      $fix_ids = array();
      foreach ($rows as $r) {
        if ($bucket_id != $r['bucket_id'])
          $fix_ids[] = $r['doll_id'];
      }
      if (!empty($fix_ids)) {
        M('user_follow')
        ->where(array('userId' => array('in', $fix_ids)) )
        -> save(array('shuabang_bucket' => $bucket_id)   );
      }
    ######################################<<<

    $ids = array();
    foreach ($rows as $r)
      $ids[] = $r['doll_id'];
    return $ids;
  }

  # 分片规则
  # static function map_dict($size, $unit_size=50){
  #   $map_dict = array();
  #   for ($i=1000; $i < $size; $i+=$unit_size) {
  #     $start = $i;
  #     $end   = $i + $unit_size -1;
  #     if ($end > $size) $end = $size;
  #     $map_dict[] = array($start, $end);
  #   }
  #   return $map_dict;
  # }
}
