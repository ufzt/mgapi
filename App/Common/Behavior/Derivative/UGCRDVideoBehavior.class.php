<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;

class UGCRDVideoBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id','video_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 必填是本人的视频
    $map = array();
      $map['id']      = $_REQUEST['video_id'];
      $map['user_id'] = $_REQUEST['my_user_id'];
      $map['status']  = UserBrifeVideos::CAO_GAO;
      $map['video_url']  = '';
    $video = M('app_video', NULL)->where($map)->find();
    if (empty($video)) {
      $result->res = false;
      $result->msg = '不存在的视频id:'.$_REQUEST['video_id'];
      return $result;
    }

    if ($_REQUEST['service'] == 'fetch_draft_ugc_video')
      $result->data = self::fetch();
    else if ($_REQUEST['service'] == 'delete_draft_ugc_video')
      self::delete();
    return $result;
  }

  static function delete(){
    $video_id = $_REQUEST['video_id'];
    $where = array();
        $where['id'] = $video_id;
    $data = array();
        $data['status'] = UserBrifeVideos::YI_SHAN_CHU;
        $data['update_time'] = time();
        $data['update_user'] = 'self';
    M('app_video', NULL)->where($where)->save($data);
  }

  static function fetch(){
    $where = array();
        $where['id'] = $_REQUEST['video_id'];
    $rows = M('app_video', NULL)
            ->field(UGCListVideosBehavior::FIELDS)->where($where)
            ->select();
    return $rows;
  }
}
