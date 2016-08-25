<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Molecule\Goods\GoodsType;
use Common\Molecule\User\UserIsCollect;
use Common\Atom\Result;
use Common\Atom\Goods;

class GoodsListBehavior extends CommonBehavior{

  const FIELDS = 'id, name, photo, price';

  static function commit(){
    $result = new Result(true);
    $result->branch = array_values(GoodsType::type_name_dict());
    # 枚举 goods_type
    $goods_type = $_REQUEST['goods_type'];
    $type_dict = array_flip(GoodsType::type_name_dict());
    if(!empty($goods_type)){
      if (!isset($type_dict[$goods_type])) {
          $result->res = false;
          $result->msg = '不存在的goods_type'.$goods_type;
          return $result;
      }
    }
    self::pick_page_params();
    $data = self::fetch();
    $result->collect_sum = $data['collect_sum'];
    $result->count = $data['count'];
    $result->data = $data['rows'];
    return $result;
  }

  static function fetch(){
    #收藏数量
    if(empty($_REQUEST['my_user_id'])){
      $data['collect_sum'] = 0;
    }else{
      $where = array();
        $where['user_id'] = $_REQUEST['my_user_id'];
      $data['collect_sum'] = M('goods_user_collect', NULL)->where($where)->count();
    }
    #商品
    if(!empty($_REQUEST['goods_type'])){
      $type_dict = array_flip(GoodsType::type_name_dict());
      $option = array();
        $option['goods_type'] = $type_dict[$_REQUEST['goods_type']];
    }
    $option['total_row_sum'] = true;
    $data['count'] = Goods::all($_REQUEST['current_page'], $_REQUEST['page_size'], $option);
    if($data['count'] == 0){
      $data['rows'] = array();
      return $data;
    }
    unset($option['total_row_sum']);
    $rows = Goods::all($_REQUEST['current_page'], $_REQUEST['page_size'], $option);
    $data['rows'] = UserIsCollect::is_collect_by($_REQUEST['my_user_id'], $rows);
    return $data;
  }
}
