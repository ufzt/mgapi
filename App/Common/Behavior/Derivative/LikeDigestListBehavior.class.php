<?php
namespace Common\Behavior\Derivative;

use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class LikeDigestListBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    self::pick_page_params();
    $data = self::message_list();
    $result->count = $data['message_sum'];
    $result->data = $data['message'];
    self::read_message();
    return $result;
  }

  # 获取被点赞/评论的Fact的属性 ['photo_url','father_type','father_relation_id']
  static function get_prop($type, $relation_id){
    $prop = array();
    if ('video' == $type){
      $row = M('app_video',NULL)->field('video_url2, photo as photo_url')
             ->find($relation_id);
      # 有video_url2 就是UGC
      if (!empty($row['video_url2']))
        $prop['photo_url'] = $row['photo_url'];
      else
        $prop['photo_url'] = C('site').'/public/upload/app_video/'.$relation_id.'/'
                            .$row['photo_url'];
      $prop['father_type'] = '';
      $prop['father_relation_id']   = '';
    }
    else if ('project' == $type){
      $row = M('project')->field('p_photo as photo_url')
             ->find($relation_id);
      $prop['photo_url'] = C('site').'/public/upload/project/'.$relation_id.'/'
                          .$row['photo_url'];
      $prop['father_type'] = '';
      $prop['father_relation_id']   = '';
    }
    else if ('comment' == $type){
      // 一级评论id
      $topic_id = $relation_id;
      // 被评论的视频/众筹的id
      $row = M('newcomments')->field('topId, type')
             ->find($topic_id);
      $type = array_search($row['type'], C('type'));
      $result = self::get_prop($type, $row['topid']);
      $prop['photo_url']   = $result['photo_url'];
      $prop['father_type'] = $type;
      $prop['father_relation_id']   = $row['topid'];
    }
    return $prop;
  }

  private static function message_list(){
    $where = array();
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
    #获取消息总数
    $data['message_sum'] = M('user_liked')->where($where)->count();
    if(empty($data['message_sum']))
      return array(
        'message_sum' => 0,
        'message' => array(),
      );

    #获取消息列表
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $rows = M('user_liked')->where($where)
           ->order('add_time desc')->limit($limit)
           ->select();
    #获取赞我的用户ian
    foreach ($rows as $key => $value)
      $user_ids[] = $value['user_id'];
    $ians = UserIAN::dozen($user_ids);
    #mapping
    foreach ($rows as $key => $value) {
      if (isset($ians[$value['user_id']]) ) {
        $ian = $ians[$value['user_id']];
        $rows[$key]['user_name'] = $ian['user_name'];
        $rows[$key]['user_avatar'] = $ian['user_avatar'];
      } else {
        $rows[$key]['user_name'] = '';
        $rows[$key]['user_avatar'] = UserIAN::empty_avatar();
      }
      # 封面图
      $type = array_search($value['type'], C('type'));
      $prop = self::get_prop($type, $value['relation_id']);
      $rows[$key]['type'] = $type;
      $rows[$key]['photo_url']   = $prop['photo_url'];
      $rows[$key]['father_type'] = $prop['father_type'];
      $rows[$key]['father_relation_id']   = $prop['father_relation_id'];
      #unset
      $arr = array('id', 'relation_user_id', 'is_know');
      foreach ($arr as $k => $v)
        unset($rows[$key][$v]);
    }
    $data['message'] = $rows;
    return $data;
  }

  private static function read_message(){
    $where = array();
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
      $where['is_know'] = 0;
      $where['add_time'] = array('lt', time());
    M('user_liked')->where($where)->setField('is_know', 1);
  }
}
