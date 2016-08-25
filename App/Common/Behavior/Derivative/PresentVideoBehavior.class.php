<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Molecule\User\UserIsLiked;
use Common\Molecule\Video\VideoDetail;
use Common\Atom\UserBrifeProjects;
use Common\Atom\UserBrifeVideos;

use Common\Molecule\User\UserFans;
use Common\Atom\UserIAN;

use Common\Molecule\Video\VideoComments;

class PresentVideoBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('video_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    self::pick_my_user_id();
    $my_user_id = $_REQUEST['my_user_id'];
    # 视频详情
    $video = VideoDetail::fetch($_REQUEST['video_id']);
    if (empty($video)) {
      $result->res = false;
      $result->msg = '不存在的视频id:'.$_REQUEST['video_id'];
      return $result;
    }
    #当前用户 是否关注 当前视频详情的制作人 关注：true|未关注：false
    $video = self::is_follow($video, $video['user_id']);
    #增加当前用户的关联属性
    $rows = UserIsLiked::is_liked_by($my_user_id,array($video));
    $video = reset($rows);
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
    #增加当前用户的关联属性
    if (!empty($projects))
      $projects = UserIsLiked::is_liked_by($my_user_id,$projects);
    # 该视频的评论
    $comments = VideoComments::fetch($_REQUEST['video_id']);
    #增加当前用户的关联属性
    if (!empty($comments))
      $comments = UserIsLiked::is_liked_by($my_user_id,$comments);
    #str_replace 图片URL替换
    $video['content'] = self::pack_replace($video['content']);
    $video['video'] = C('site').'/store/detail-'.$video['id'].'.html?onlyvideo=ok&src=app_video';
    $result->video = $video;
    $result->project = $projects;
    $result->comment = $comments;
    $result->you_may_like = self::you_may_like($video);
    return $result;
  }
  
  #判断是否关注
  private static function is_follow($video,$follow_user_id){
    $map = array();
      $map['userId'] = $_REQUEST['my_user_id'];
      $map['followUser'] = $follow_user_id;
    $row = M('user_follow')->where($map)->find();
    $video['is_followed'] = !empty($row);
    return $video;
  }

  # 猜你喜欢
   # 取出热度排前10的
   # 排除自己
   # 从中随机选3个
  private static function you_may_like($video){
    $option = array();
      $option['status']     = UserBrifeVideos::YI_SHEN_HE;
      $option['branch_id']  = $video['branch_id'];
      if (empty($option['branch_id']))
        $option['branch_id'] = 7;
      $option['order_prop'] = 'hot';
    $other_videos = UserBrifeVideos::all(1,10,$option);
    foreach ($other_videos as $i => $v) {
      if ($v['id'] == $video['id'])
        unset($other_videos[$i]);
    }
    $random_keys=array_rand($other_videos,3);
    $result = array();
      foreach ($random_keys as $key)
        $result[] = $other_videos[$key];
    #增加当前用户的关联属性
    if (!empty($result))
      $result = UserIsLiked::is_liked_by($_REQUEST['my_user_id'],$result);
    return $result;
  }
}
