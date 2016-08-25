<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeProjects;
use Common\Molecule\User\UserIsLiked;
use Common\Atom\UserIAN;

class ListMyProjectBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 检查my_user_id是否存在
    if (false === UserIAN::is_user($_REQUEST['my_user_id']) ){
      $result->res = false;
      $result->msg = '不存在的用户id:'.$_REQUEST['my_user_id'];
      return $result;
    }

    # 我的视频
    self::pick_page_params();
    $result->count =
      UserBrifeProjects::one($_REQUEST['my_user_id'],
                             $_REQUEST['current_page'],
                             $_REQUEST['page_size'],
                             array('total_row_sum'=>true) );
    $my_facts =
      UserBrifeProjects::one($_REQUEST['my_user_id'],
                             $_REQUEST['current_page'],
                             $_REQUEST['page_size'] );
    $result->project =
      UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $my_facts);
    return $result;
  }
}
