<?php
namespace Common\Atom;

# META data - User(s)'s [id,avatar,name]
  # 提供两个公用方法
  # 1.is_user($user_id)      判断user_id 是否存在
  # 2.one($user_id)          根据user_id 取出用户的IAN
  #   dozen($user_ids)       增加对 单个用户 和 一组用户 的查询支持
class UserIAN{

  const MAX_DOZEN = 100;

  static function fields(){
    return 'userid as user_id, username as user_name, userface as user_avatar';
  }

  static function empty_avatar(){
    return C('site').'/public/images/noface.png';
  }

  static function dozen($user_ids){
    if (is_array($user_ids))
      $user_ids = array_unique($user_ids);
    else if (is_string($user_ids) || is_numeric($user_ids))
      $user_ids = explode(',', $user_ids);
    else return array();

    if (empty($user_ids))
      return array();

    $map = array();
      if (count($user_ids) > self::MAX_DOZEN) #考虑 select in 效率
          $user_ids = array_slice($user_ids,0,self::MAX_DOZEN);
      $map['userId']  = array('in', $user_ids);
      $u_id = implode(',', $user_ids);
      $order = "field(userId,{$u_id})";
    //query
    $rows = M('user')->field(static::fields())->where($map)
            ->order($order)
            ->select();
    if (empty($rows)) $rows = array();
    //mapping
    $data = array();
    foreach ($rows as $r) {
      if (empty($r['user_avatar']))
        $r['user_avatar'] = self::empty_avatar();
      else
        $r['user_avatar'] = C('site').'/public/upload/userface/'
                            .$r['user_id'].'/256_'.$r['user_avatar'];
      $data[$r['user_id']] = $r;
    }
    return $data;
  }

  static function one($user_id){
    $data = self::dozen($user_id);
    if (!isset($data[$user_id]))
      return array();

    return $data[$user_id];
  }

  static function is_user($user_id){
    $row = M('user')->field('userid')
           ->where(array('userId'=>$user_id))
           ->find();
    return !empty($row);
  }
}
