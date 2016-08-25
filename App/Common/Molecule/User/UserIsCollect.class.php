<?php
namespace Common\Molecule\User;

class UserIsCollect{

  static function is_collect_by($single_user_id, $facts){
    if (empty($single_user_id)) {
      $facts = self::set_false($facts);
      return $facts;
    }
    $goods_ids = array();
    foreach ($facts as $key => $value) {
        $goods_ids[] = $value['id'];
    }
    #获取该用户收藏的商品id
    $where = array();
        $where['goods_id'] = array('in', $goods_ids);
        $where['user_id'] = $single_user_id;
    $rows = M('goods_user_collect', NULL)->field('goods_id')->where($where)->select();
    if(empty($rows)){
        $facts = self::set_false($facts);
        return $facts;
    }
    #判断商品id是否收藏
    foreach ($rows as $key => $value)
        $user_collect[] = $value['goods_id'];
    foreach ($facts as $key => $value) {
      if(in_array($value['id'], $user_collect))
          $facts[$key]['is_collected'] = true;
      else
          $facts[$key]['is_collected'] = false;
    }
    return $facts;
  }

  static function set_false($facts){
    foreach ($facts as $i => $r) {
    if (!is_array($r)) continue;
        $facts[$i]['is_collected'] = false;
    }
    return $facts;
  }
}
