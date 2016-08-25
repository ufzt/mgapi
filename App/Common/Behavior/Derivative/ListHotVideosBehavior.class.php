<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;
use Common\Molecule\User\UserIsLiked;

class ListHotVideosBehavior extends CommonBehavior {

  static function commit(){
    $result = new Result(true);
    self::pick_page_params();
    self::pick_my_user_id();
    $data = self::fetch();
    $result->count = $data['count'];
    $result->data = $data['data'];
    return $result;
  }

  static function fetch(){
    #where
    $option = array();
      $option['is_recommend'] = 1;
      $option['status'] = UserBrifeVideos::YI_SHEN_HE;
    $rows['count'] = M('app_video',NULL)->where($option)->count();
    $videos = UserBrifeVideos::all($_REQUEST['current_page'], $_REQUEST['page_size'], $option);
    $rows['data'] = UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $videos);
    return $rows;
  }
}
