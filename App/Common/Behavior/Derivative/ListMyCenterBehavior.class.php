<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeProjects;
use Common\Atom\UserBrifeVideos;
use Common\Molecule\User\UserIsLiked;
use Common\Atom\UserIAN;

class ListMyCenterBehavior extends CommonBehavior{

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

    $my_uid = $_REQUEST['my_user_id'];
    # 账户中心各项信息
    $data = self::user_info($my_uid);
    #统计视频草稿数
      $where = array();
        $where['status'] = UserBrifeVideos::CAO_GAO;
        $where['user_id'] = $my_uid;
        $where['video_url']  = '';
    $data['ugc_draft_sum'] = M('app_video',NULL)->where($where)->count();
    #商品收藏数
    $where = array();
      $where['user_id'] = $_REQUEST['my_user_id'];
    $data['collect_sum'] = M('goods_user_collect', NULL)->where($where)->count();
    $result->data = $data;
    # 我的视频
    $my_videos = array();
    if ($data['videos'] > 0){
      $option = array('status'=>UserBrifeVideos::YI_FA_BU);
      $my_videos = UserBrifeVideos::one($my_uid, 1, 20, $option);
    }
    $result->video = UserIsLiked::is_liked_by($my_uid, $my_videos);
    # 我的项目
    $my_projects = array();
    if ($data['projects'] > 0)
      $my_projects = UserBrifeProjects::one($my_uid, 1, 20);
    $result->project = UserIsLiked::is_liked_by($my_uid, $my_projects);
    return $result;
  }

  static function user_info($user_id, $is_me=true){
    #用户信息
    $fields = UserIAN::fields().', province, city, area, gender, fans, follow, introduction';
    $row = M('user')->field($fields)->where(array('userId'=>$user_id))
           ->find();
    #去除null
    foreach ($row as $key => $value) {
      if (empty($value)) {
        if ('fans' == $key || 'follow' == $key)
          $row[$key] = 0;
        else if ('gender' == $key)
          $row[$key] = '男';
        else
          $row[$key] = '';
      }
    }
    ###############################################
    # 第一版App临时方案
    #   fans follow 去log表 count
    # 下一版App迭代目标
    #   logbrief
      $fans = M('user_follow')->where(array('followUser'=>$user_id))->count();
      if ($fans < 1) $fans = 0;
      $row['fans'] = $fans;
      if($is_me){
        $follow = M('user_follow')->where(array('userId'=>$user_id))->count();
        if ($follow < 1)  $follow = 0;
        $row['follow'] = $follow;
      }
    ###############################################
    #pack
    if (empty($row['user_avatar']))
      $row['user_avatar'] = UserIAN::empty_avatar();
    else
      $row['user_avatar'] = C('site').'/public/upload/userface/'.$row['user_id']
                            .'/256_'.$row['user_avatar'];
    #创造力creative 众筹数videos 视频数projects
    $field = 'SUM(hot) as hot, COUNT(*) as total';
    $project = M('project')->field($field)
               ->where(array('userId'=>$user_id, 'p_status'=>1))
               ->find();
    $video = M('app_video',NULL)->field($field)
             ->where(array('user_id'=>$user_id, 'status'=>array('in',UserBrifeVideos::YI_FA_BU)))
             ->find();
    $row['creative'] = $project['hot'] + $video['hot'];
    $row['videos'] = $video['total'];
    $row['projects'] = $project['total'];
    #订单
    if($is_me)
      $row['orders'] = M('order')->where(array('userId'=>$user_id))->count('Id');
    return $row;
  }
}
