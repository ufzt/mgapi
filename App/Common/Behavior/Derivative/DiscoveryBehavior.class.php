<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Atom\UserBrifeProjects;
use Common\Atom\UserBrifeVideos;
use Common\Molecule\User\UserIsLiked;
use Common\Behavior\CommonBehavior;

class DiscoveryBehavior extends CommonBehavior{

  #排序类型 sort_type
    const ZUI_XIN = 1; //最新
    const ZUI_RE  = 2; //最热
    const GUAN_ZHU = 3; //关注

  static function commit(){
    # 可选参数默认值
    self::pick();
    # 最新逻辑
      # 根据根据①时间倒序分页和②branch 查询VIDEO表
      # 查询结果的时间区间取出,并以①时间区间和②branch为条件 查询PROJECT表
      # 把VIDEO和PROJECT合并成一个数组,按时间倒序排序
    # 最热逻辑
      # 根据根据①热度倒序分页和②branch 查询VIDEO表
      # 查询结果的热度区间取出,并以①热度区间和②branch为条件 查询PROJECT表
      # 把VIDEO和PROJECT合并成一个数组,按热度倒序排序
    # 关注逻辑
      # 根据用户的my_user_id 取出最多100个关注的创客id
      # 根据根据①时间倒序分页和②branch③创客id 查询VIDEO表
      # 查询结果的时间区间取出,并以①时间区间和②branch③创客id为条件 查询PROJECT表
      # 把VIDEO和PROJECT合并成一个数组,按时间倒序排序
    $bag = array();
    $bag['follow_user'] = self::my_followed_users();
    $bag = self::fetch_video($bag);
    $bag = self::map_project($bag);
    $result = new Result(true);
    # banner
    if('recommend' == $_REQUEST['branch']){
      $result->banner = array();
      $result->banner2 = ListBannerBehavior::fetch();
    }
    # data
    $result->data = $bag['list'];
    if (isset($bag['count']))
      $result->count = $bag['count'];
    # user
    $result->user = $bag['follow_user'];
    # branch
    $branch_list = array();
    foreach (C('branch.name') as $key => $value)
      $branch_list[] = array($key => $value);
    $result->branch = $branch_list;
    return $result;
  }

  private static function fetch_video($bag){
    $my_followed_users = $bag['follow_user'];
    // 排序为关注时,没有关注人就不用查了
    if (self::GUAN_ZHU == $_REQUEST['sort_type']) {
      if (empty($my_followed_users)){
        $bag['list'] = array();
        $bag['count'] = 0;
        return $bag;
      }
    }
    // 查询video
    $map = array();
      # branch
      $branch = $_REQUEST['branch'];
      $branch_assoc = C('branch.assoc');
      if (isset($branch_assoc[$branch]))
        $map['branch_id'] = $branch_assoc[$branch]['video_class_id'];
      else
        $map['branch_id'] = array('gt',0);
      # user_id
      if (self::GUAN_ZHU == $_REQUEST['sort_type'])
        $map['user_id'] = array('in', $my_followed_users);
      else
        $map['user_id'] = array('gt', 0);
      # status
      $map['status'] = array('in', UserBrifeVideos::YI_SHEN_HE);
      # pub_time
      $map['pub_time'] = array('lt',time());
    $order = self::sort_order();
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $rows = M('app_video',NULL)->field(UserBrifeVideos::FIELDS)->where($map)
            ->order($order)->limit($limit)
            ->select();
    if (empty($rows)) $rows = array();
    //统计video总数
    if($_REQUEST['current_page'] == 1){
      if (!empty($rows))
        $bag['count'] = M('app_video',NULL)->where($map)->count();
      else
        $bag['count'] = 0;
    }
    //mapping video数据
    if (!empty($rows))
      $rows = UserBrifeVideos::pack($rows);
    $bag['list'] = $rows;
    return $bag;
  }

  private static function map_project($bag){
    if (empty($bag['list']))
      return $bag;

    // 查询project
    $videos = $bag['list'];
    $map = array();
      # branch
      $branch = $_REQUEST['branch'];
      $branch_assoc = C('branch.assoc');
      if (isset($branch_assoc[$branch]))
        $map['p_classid'] = $branch_assoc[$branch]['video_class_id'];
      else
        $map['p_classid'] = array('gt',0);
      # user_id
      if (self::GUAN_ZHU == $_REQUEST['sort_type']) {
        $my_followed_users = $bag['follow_user'];
        $map['userId'] = array('in', $my_followed_users);
      }
      else
        $map['userId'] = array('gt', 0);
      # between
      if (self::ZUI_RE == $_REQUEST['sort_type'])
        $map['hot'] = self::map_between($videos, 'hot');
      else
        $map['p_starttime'] = self::map_between($videos, 'pub_time');
      # status
      $map['p_status'] = 1;
    $order = self::sort_order();
    $rows = M('project')->field(UserBrifeProjects::FIELDS)->where($map)
            ->order($order)->limit(7)
            ->select();
    if (empty($rows)) $rows = array();
    //mapping project数据
    if (!empty($rows))
      $rows = UserBrifeProjects::pack($rows);
    #合并视频项目
    $videos_and_projects = array_merge($bag['list'], $rows);
    #增加当前用户的关联属性
    $videos_and_projects =
      UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $videos_and_projects);
    #所有视频项目按照热度/时间倒序
    if (self::ZUI_RE == $_REQUEST['sort_type'])
      usort($videos_and_projects, function($a, $b){
        $a_prop = $a['hot'];
        $b_prop = $b['hot'];
        if ($a_prop == $b_prop) return 0;
        return ($a_prop < $b_prop)? 1:-1;#逆序
      });
    else
      usort($videos_and_projects, function($a, $b){
        $a_prop = $a['pub_time'];
        $b_prop = $b['pub_time'];
        if ($a_prop == $b_prop) return 0;
        return ($a_prop < $b_prop)? 1:-1;#逆序
      });
    $bag['list'] = $videos_and_projects;
    return $bag;
  }

  private static function pick(){
    self::pick_page_params();
    self::pick_my_user_id();

    # sort_type 排序类型
    # 默认最新
    if (!isset($_REQUEST['sort_type'])
      || intval($_REQUEST['sort_type']) > 3 || intval($_REQUEST['sort_type']) < 1 )
      $_REQUEST['sort_type'] = self::ZUI_XIN;

    # branch 分类类型
    # 默认 recommend
    $branchs = C('branch.name');
    if (!isset($_REQUEST['branch']) || !isset($branchs[$_REQUEST['branch']]) )
      $_REQUEST['branch'] = 'recommend';
  }

  private static function my_followed_users(){
    if (empty($_REQUEST['my_user_id']))
      return array();

    $my_user_id = $_REQUEST['my_user_id'];
    $rows = M('user_follow')->field('followuser')
            ->where(array('userId'=>$my_user_id))
            ->limit(UserBrifeVideos::MAX_DOZEN)
            ->select();
    if (empty($rows))
      return array();

    foreach ($rows as $i => $r)
      $rows[$i] = $r['followuser'];
    return $rows;
  }

  private static function sort_order(){
    if (self::ZUI_RE == $_REQUEST['sort_type'])
      $order = 'hot desc';
    else
      $order = 'pub_time desc';
    return $order;
  }

  static function map_between($videos, $column){
    if ($column != 'hot' && $column != 'pub_time')
      trigger_error($column.' is NOT allowed!', E_USER_ERROR);

    list($max, $min) = array(0, 4294967295);
    foreach ($videos as $r) {
      if (!is_array($r)) continue;
      $max = $max>$r[$column]? $max:$r[$column];
      $min = $min<$r[$column]? $min:$r[$column];
    }
    return array('between', $min.','.$max);
  }
}
