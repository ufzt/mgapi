<?php
namespace Common\Behavior\Derivative;

use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class GoodsCollectDoUndoBehavior extends CommonBehavior{

  static function commit() {
    # 检查必填参数
    $inquire_params = array('my_user_id', 'goods_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $user_id  = $_REQUEST['my_user_id'];
    $goods_id = $_REQUEST['goods_id'];
    # 枚举my_user_id
    if (false === UserIAN::is_user($user_id) ){
      $result->res = false;
      $result->msg = '不存在的用户id:'.$user_id;
      return $result;
    }
    # 枚举goods_id
    $row = M('goods', NULL)->find($goods_id);
    if (empty($row)) {
      $result->res = false;
      $result->msg = '不存在的goods_id'.$goods_id;
      return $result;
    }

    $dao = M('goods_user_collect', NULL);
    $map = array();
      $map['user_id'] = $user_id;
      $map['goods_id'] = $goods_id;
    $row = $dao->where($map)->find();
    # 收藏
    if ('collect_goods' == $_REQUEST['service']){
      if (!empty($row)) {
        $result->res = false;
        $result->msg = '用户已收藏该商品';
        return $result;
      }
      $map['collect_time'] = time();
      $dao->add($map);
    }
    # 取消收藏
    else if ('uncollect_goods' == $_REQUEST['service']){
      if (empty($row)) {
        $result->res = false;
        $result->msg = '用户尚未收藏该商品';
        return $result;
      }
      $dao->where($map)->delete();
    }

    return $result;
  }
}
