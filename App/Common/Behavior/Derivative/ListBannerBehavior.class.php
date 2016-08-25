<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;

class ListBannerBehavior{

  static function commit(){
    $result = new Result(true);
    $result->data = self::fetch();
    return $result;
  }

  static function fetch($status = ''){
    $where = array();
    if($status != 'goods'){
      $where['type'] = array('in', array(10, 20, 40, 50));
    }
      $where['is_recommend'] = 1;
    $rows = M('app_banner', NULL)->field('type, value, photo_url')
            ->where($where)->order('recommend_time desc')
            ->select();
    $type = array_flip(C('type'));
    foreach ($rows as $key => $value) {
      $rows[$key]['type'] = $type[$value['type']];
    }
    return $rows;
  }
}
