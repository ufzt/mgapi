<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Molecule\Goods\GoodsType;
use Common\Molecule\User\UserIsCollect;
use Common\Atom\Result;
use Common\Atom\Goods;

class GoodsShopBehavior extends CommonBehavior{

  static function commit(){
    $result = new Result(true);
    $result->data = self::fetch();
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
    #banner
    $data['banner'] = ListBannerBehavior::fetch('goods');
    #branch
    $branch_dict = GoodsType::type_name_dict();
      # 取前7个
      $branch_dict = array_slice($branch_dict,0,7,true);
      # mapping 图片
      $img_dict = GoodsType::type_image_dict();
      foreach ($branch_dict as $k=>$v)
        $branch_dict[$k] = array('name'=>$v, 'image'=>$img_dict[$k]);
      # 加上更多
        $branch_dict['more'] = array('name'=>'更多', 'image'=>GoodsType::IMAGE_PRE.'all.png');
    $data['branch'] = array_values($branch_dict);
    #商品主题
    $where = array();
      $where['recommend_time'] = array('GT', 0);
    $rows = M('goods_collection', NULL)->field('name, good_ids')->where($where)->select();
    foreach ($rows as $key => $value) {
      $good_ids = json_decode($value['good_ids']);
      $goods = Goods::dozen($good_ids);
      $goods = UserIsCollect::is_collect_by($_REQUEST['my_user_id'], $goods);
      $count = count($goods);
      if($count >= 4){
        $goods = array_slice($goods, 0, 4);
      }else{
        $goods = array();
      }
      $data['theme'][$value['name']] = $goods;
    }
    return $data;
  }
}
