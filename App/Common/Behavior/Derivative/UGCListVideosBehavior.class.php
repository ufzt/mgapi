<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;

class UGCListVideosBehavior extends CommonBehavior{

  const FIELDS = 'id, title, photo, video_url2, update_time';

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $result->data = self::fetch_video();
    return $result;
  }

  static function fetch_video(){
    $where = array();
        $where['user_id']    = $_REQUEST['my_user_id'];
        $where['status']     = UserBrifeVideos::CAO_GAO;
        $where['video_url']  = '';
    $order = 'update_time desc';
    $rows = M('app_video', NULL)->field(self::FIELDS)
            ->where($where)->order($order)
            ->select();
    if (empty($rows)) $rows = array();
    return $rows;
  }
}
