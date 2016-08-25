<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Molecule\Video\VideoDetail;
use Common\Atom\UserBrifeProjects;
use Common\Molecule\User\UserFans;
use Common\Atom\UserIAN;

class PreviewVideoBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('video_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 视频详情
    $video = VideoDetail::fetch($_REQUEST['video_id']);
    if (empty($video)) {
      $result->res = false;
      $result->msg = '不存在的视频id:'.$_REQUEST['video_id'];
      return $result;
    }
    # 最多显示6个粉丝
    $video_maker_id = $video['user_id'];
    $video['fan_sum'] = UserFans::sum($video_maker_id);
    $fan_ids = UserFans::fetch($video_maker_id, 6);
    $video['fan_ids'] = $fan_ids;
    # 补全头像
    $fan_list = array();
      foreach ($fan_ids as $row)
        $fan_list[] = $row['fan_id'];
      $ians = UserIAN::dozen($fan_list);
      foreach ($fan_list as $i=>$fan_id) {
        if(isset($ians[$fan_id]))
          $fan_list[$i] = $ians[$fan_id];
        else
          unset($fan_list[$i]);
      }
    $video['fan_list'] = array_values($fan_list);
    # 该视频用户正在众筹的项目
    $projects = UserBrifeProjects::one($video_maker_id,1,60);
    foreach ($projects as $key => $value) {
      if($value['p_days'] == 0){
        unset($projects[$key]);
      }
    }
    $projects = array_slice(array_values($projects),0,3);
    # 该视频的评论
    $comments = array();
    # 猜你喜欢
    $video_2 = array();
    #str_replace 图片URL替换
    $video['content'] = self::pack_replace($video['content']);
    $video['video'] = C('site').'/store/detail-'.$video['id'].'.html?onlyvideo=ok&src=app_video';
    $result->video = $video;
    $result->project = $projects;
    $result->comment = $comments;
    $result->you_may_like = $video_2;
    return $result;
  }
}
