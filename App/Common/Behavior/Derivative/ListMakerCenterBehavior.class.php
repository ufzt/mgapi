<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeProjects;
use Common\Atom\UserBrifeVideos;
use Common\Molecule\User\UserIsLiked;
use Common\Atom\UserIAN;

class ListMakerCenterBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('other_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # pick params
    self::pick_my_user_id();

    # 检查other_user_id是否存在
    if (false === UserIAN::is_user($_REQUEST['other_user_id']) ){
      $result->res = false;
      $result->msg = '不存在的用户id:'.$_REQUEST['other_user_id'];
      return $result;
    }
    $other_uid = $_REQUEST['other_user_id'];
    # 账户中心各项信息
    $data = ListMyCenterBehavior::user_info($other_uid, false);
    $result->data = self::mapping($data);
    # TA的视频和众筹
    $videos = array();
    if ($data['videos'] > 0){
      $option = array('status'=>UserBrifeVideos::YI_FA_BU);
      $videos = UserBrifeVideos::one($other_uid, 1, 20, $option);
    }
    $bag = self::map_project($videos);
    $result->video = $bag;
    return $result;
  }

  static function map_project($videos){
    if (empty($videos))
      return array();

    // 查询project
    $map = array();
      $map['p_starttime'] = DiscoveryBehavior::map_between($videos, 'pub_time');
      # status
      $map['p_status'] = 1;
      $map['userId'] = $_REQUEST['other_user_id'];
    $rows = M('project')->field(UserBrifeProjects::FIELDS)->where($map)
            ->order('pub_time desc')->limit(7)
            ->select();
    if (empty($rows)) $rows = array();
    //mapping project数据
    if (!empty($rows))
      $rows = UserBrifeProjects::pack($rows);
    #合并视频项目
    $videos_and_projects = array_merge($videos, $rows);
    #增加当前用户的关联属性
    $videos_and_projects =
      UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $videos_and_projects);
    #所有视频项目按照时间倒序
    usort($videos_and_projects, function($a, $b){
      $a_prop = $a['pub_time'];
      $b_prop = $b['pub_time'];
      if ($a_prop == $b_prop) return 0;
      return ($a_prop < $b_prop)? 1:-1;#逆序
    });
    return  $videos_and_projects;
  }

  static function mapping($data){
    # 我是否关注TA
    $data['is_followed'] = false;
    if(!empty($_REQUEST['my_user_id'])){
      $map = array();
        $map['userId'] = $_REQUEST['my_user_id'];
        $map['followUser'] = $_REQUEST['other_user_id'];
      $is_exist = M('user_follow')->where($map)->find();
      $data['is_followed'] = !empty($is_exist);
    }
    #过滤
    $arr = array('user_id', 'province', 'city', 'area', 'gender', 'follow', 'projects', 'orders');
    foreach($arr as $val)
      unset($data[$val]);
    return $data;
  }
}
