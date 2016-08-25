<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Goods;
use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class GoodsCollectListBehavior extends CommonBehavior{

  static function commit() {
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $user_id  = $_REQUEST['my_user_id'];
    # 枚举my_user_id
    if (false === UserIAN::is_user($user_id) ){
      $result->res = false;
      $result->msg = '不存在的用户id:'.$user_id;
      return $result;
    }
    self::pick_page_params();
    $data = self::fetch();
    $result->count = $data['count'];
    $result->data = $data['data'];
    return $result;
  }

  static function fetch(){
    $where = array();
      $where['user_id'] = $_REQUEST['my_user_id'];
    $rows = M('goods_user_collect', NULL)
            ->field('goods_id')
            ->where($where)
            ->order('collect_time desc')
            ->select();
    if(empty($rows)){
      $data['count'] = 0;
      $data['data'] = array();
      return $data;
    }
    foreach ($rows as $key => $value) 
      $good_ids[] = $value['goods_id'];
    $option = array();
      $option['total_row_sum'] = true;
      $option['recommend_time'] = '';
    $data['count'] = Goods::dozen($good_ids, '', '', $option);
    $data['data'] = Goods::dozen($good_ids, $_REQUEST['current_page'], $_REQUEST['page_size'], array('recommend_time'=>''));
    return $data;
  }
}
