<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Atom\UserIAN;

class RecommendMakersBehavior{

  static function commit(){
    $result = new Result(true);
    # 推荐创客列表
    $result->data = self::makers_list();
    return $result;
  }

  static function makers_list(){
    #用户信息
    $fields = UserIAN::fields().', introduction';
    $where = array();
      $where['recommend_time'] = array('gt', 0);
    $limit = '15';
    $rows = M('user')->field($fields)->where($where)
           ->order('recommend_time desc')->limit($limit)
           ->select();
    foreach($rows as $i=>$row){
      # null
      if(empty($row['introduction']))
        $rows[$i]['introduction'] = '';
      # user_avatar
      if (empty($row['user_avatar']))
        $rows[$i]['user_avatar'] = C('site').'/public/images/noface.png';
      else
        $rows[$i]['user_avatar'] = C('site').'/public/upload/userface/'
                                    .$row['user_id'].'/256_'.$row['user_avatar'];
    }
    return $rows;
  }
}
