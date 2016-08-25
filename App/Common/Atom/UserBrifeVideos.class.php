<?php
namespace Common\Atom;

# META data - Brife INFO of User(s)'s Videos
  #
  # 不传 user_id/user_ids   使用 all()
  # 传 user_ids数组         使用 dozen($user_ids)
  # 传 user_id             使用 one($user_id)
  #
  # 返回都是 videos数组
class UserBrifeVideos{

  const TYPE = 'video';
  const FIELDS = 'id, pub_time, user_id, branch_id, video_url,
                  video_url2, title, photo, intro as introduction,
                  view_sum    as view_times,
                  topic_sum   as comments,
                  like_sum    as liked,
                  collect_sum as collection,
                  hot';

  # dozen方法接受的user_ids数组的最大size
  const MAX_DOZEN = 100;

  # 视频分组状态
  const QUAN_BU     = '0,1,2';
  const YI_SHAN_CHU = '-1';
  const CAO_GAO     = '0';
  const DING_GAO    = '1';
  const YI_FA_BU    = '1,2';
  const YI_SHEN_HE  = '2';

  # $option Hash
    # $option['status'] = UserBrifeVideos::YI_SHEN_HE;
    # $option['branch_id'] = 5;
  static function all($current_page, $page_size, $option=array() ){
    return self::fetch(NULL, $current_page, $page_size, $option);
  }
  static function dozen($user_ids, $current_page, $page_size, $option=array() ){
    return self::fetch($user_ids, $current_page, $page_size, $option);
  }
  static function one($user_id, $current_page, $page_size, $option=array() ){
    return self::fetch($user_id, $current_page, $page_size, $option);
  }

  static function pack($rows){
    #补全字段 user_name user_avatar
    $user_ids = array();
    foreach ($rows as $r){
      if (!is_array($r)) continue;
      $user_ids[] = $r['user_id'];
    }
    $ians = UserIAN::dozen($user_ids);
    #填充字段 video_url photo type
    foreach ($rows as $i => $r) {
      if (!is_array($r)) continue;
      # 有video_url2 就是UGC
      if (!empty($r['video_url2'])){
        $rows[$i]['video_url'] = $r['video_url2'];
        $rows[$i]['introduction'] = $r['title'];
        $rows[$i]['content_url'] = '';
      }
      else {
        $rows[$i]['photo'] = C('site').'/public/upload/app_video/'
                             .$r['id'].'/'.$r['photo'];
        $rows[$i]['content_url'] = C('site').'/store/detail-'
                                  .$r['id'].'.html?content=ok&src=app_video';
      }
      unset($rows[$i]['video_url2']);
      # type
      $rows[$i]['type'] = 'video';
      # user_name user_avatar
      if (isset($ians[$r['user_id']]) ) {
        $ian = $ians[$r['user_id']];
        $rows[$i]['user_name'] = $ian['user_name'];
        $rows[$i]['user_avatar'] = $ian['user_avatar'];
      }
      else {
        $rows[$i]['user_name'] = '';
        $rows[$i]['user_avatar'] = UserIAN::empty_avatar();
      }
      #tag数组
      $rows[$i]['tag'] = self::get_tag($r['title']);
    }
    return $rows;
  }

  #过滤获取title中传过来的标签
  static function get_tag($str){
    $tag = array();
    $arr = explode('#', $str);
    if(empty($arr)) return array();
    foreach ($arr as $key => $value) {
        if($key%2 != 0 && $key != count($arr)-1 && !empty($value) && mb_strlen($value, 'UTF-8')<=12){
            $tag[] = $value;
        }
    }
    return $tag;
  }

  private static function fetch($user_ids = NULL, $current_page, $page_size, $option){
    if (is_string($user_ids) || is_numeric($user_ids))
      $user_ids = explode(',', $user_ids);
    if (is_array($user_ids) && empty($user_ids))
      return array();

    ### where
    $map = array();
      if (is_null($user_ids))
        $map['user_id'] = array('gt',0);
      else {
        if (count($user_ids) > self::MAX_DOZEN) #考虑 select in 效率
          $user_ids = array_slice($user_ids,0,self::MAX_DOZEN);
        $map['user_id'] = array('in', $user_ids);
      }
      # option map
      if (isset($option['status']))
        $map['status'] = array('in', $option['status']);
      if (isset($option['branch_id']))
        $map['branch_id'] = $option['branch_id'];
      if (isset($option['id']))
        $map['id'] = array('in', $option['id']);
      if (isset($option['is_recommend']))
        $map['is_recommend'] = $option['is_recommend'];
      if (isset($option['title_like']))
        $map['title'] = array('like', '%'.$option['title_like'].'%');

    ### count
    if (isset($option['total_row_sum']) && $option['total_row_sum'] = true) {
      return M('app_video',NULL)->field(self::FIELDS)
             ->where($map)->count();
    }

    ### order
    $order_prop = 'pub_time';
    $order_asc  = 'desc';
    # option order
    if (isset($option['order_prop']))
      $order_prop = $option['order_prop'];
    if (isset($option['order_asc']))
      $order_asc  = $option['order_asc'];
    $order  = $order_prop.' '.$order_asc;
    ### limit
    $offset = $page_size * ($current_page -1);
    $length = $page_size;
    $limit  = $offset.','.$length;
    $rows = M('app_video',NULL)->field(self::FIELDS)
            ->where($map)->order($order)->limit($limit)
            ->select();
    if (empty($rows))
      return array();

    return self::pack($rows);
  }
}
