<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;
use Common\Atom\UserIAN;

class ListMakerVideoBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('other_user_id', 'current_page', 'page_size');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 检查other_user_id是否存在
    if (false === UserIAN::is_user($_REQUEST['other_user_id']) ){
      $result->res = false;
      $result->msg = '不存在的用户id:'.$_REQUEST['other_user_id'];
      return $result;
    }
    # TA的视频和众筹
    $option = array('status'=>UserBrifeVideos::YI_FA_BU);
    $videos = UserBrifeVideos::one(
      $_REQUEST['other_user_id'],
      $_REQUEST['current_page'],
      $_REQUEST['page_size'],
      $option
    );
    $bag = ListMakerCenterBehavior::map_project($videos);
    $result->video = $bag;
    return $result;
  }
}
