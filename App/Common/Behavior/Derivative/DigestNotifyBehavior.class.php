<?php
namespace Common\Behavior\Derivative;

use Common\Atom\UserIAN;
use Common\Behavior\CommonBehavior;

class DigestNotifyBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;
    #假数据
    $push = array();
      $push['message_sum']  = 0;
      $push['last_message_time'] = '';
      $push['last_message_desc'] = '';
    #调函数
    $like = self::like();
    $follow = self::follow();
    $comment = self::comment();
    #data
    $data = array();
      $data['message_sum'] = $push['message_sum']+ $comment['message_sum']+
                            $like['message_sum']+ $follow['message_sum'];
      $data['push']    = $push;
      $data['comment'] = $comment;
      $data['like']    = $like;
      $data['follow']  = $follow;
    $result->data = $data;
    return $result;
  }

  static function follow(){
    $where = array();
      $where['isKnow'] = 0;
      $where['followUser'] = $_REQUEST['my_user_id'];
    #获取未读消息总数
    $data['message_sum'] = M('user_follow')->where($where)->count();

    #获取最新的一条记录
    unset($where['isKnow']);
    $row = M('user_follow')->field('userId, addTime')->where($where)
           ->order('addTime desc')->limit('1')
           ->select();
    if (empty($row)) {
      $data['last_message_time'] = '';
      $data['last_message_desc'] = '';
      return $data;
    }

    #最近一条消息的时间
    $data['last_message_time'] = $row[0]['addtime'];
    #获取userName
    $ian = UserIAN::one($row[0]['userid']);
    $user_name = $ian['user_name'];
    #最近一条消息的描述
    $data['last_message_desc'] = $user_name.'关注了你';
    return $data;
  }

  static function like(){
    $where = array();
      $where['is_know'] = 0;
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
    #获取未读消息总数
    $data['message_sum'] = M('user_liked')->where($where)->count();

    #获取最新的一条记录
    unset($where['is_know']);
    $row = M('user_liked')->where($where)
           ->order('add_time desc')->limit('1')
           ->select();
    if (empty($row)) {
      $data['last_message_time'] = '';
      $data['last_message_desc'] = '';
      return $data;
    }

    $data['last_message_time'] = $row[0]['add_time'];
    #获取userName
    $ian = UserIAN::one($row[0]['user_id']);
    $user_name = $ian['user_name'];
    #获取消息内容
    $data['last_message_desc'] = self::get_content($row[0]['type'], $row[0]['relation_id'], $user_name, 'like');
    return $data;
  }

  static function comment(){
    $where = array();
      $where['is_know'] = 0;
      $where['relation_user_id'] = $_REQUEST['my_user_id'];
    #获取未读消息总数
    $data['message_sum'] = M('newcomments')->where($where)->count();

    #获取最新的一条记录
    unset($where['is_know']);
    $row = M('newcomments')->where($where)
           ->order('addTime desc')->limit('1')
           ->select();
    if (empty($row)) {
      $data['last_message_time'] = '';
      $data['last_message_desc'] = '';
      return $data;
    }

    $data['last_message_time'] = $row[0]['addtime'];
    #获取userName
    $ian = UserIAN::one($row[0]['userid']);
    $user_name = $ian['user_name'];
    #获取消息内容
    $data['last_message_desc'] = self::get_content($row[0]['type'], $row[0]['topid'], $user_name, 'comment');
    return $data;
  }

  #获取消息内容
  static function get_content($row_type, $id, $user_name, $status){
    $type = C('type');
    $type_name = array_search($row_type, $type);
    $where = array();
      $map = array('id'=>$id,'Id'=>$id);
    if ('video' == $type_name)
      $content = M('app_video',NULL)->where($map)
                 ->getField('title');
    else if ('project' == $type_name)
      $content = M('project')->where($map)
                 ->getField('p_name');     
    else if ('comment' == $type_name)
      $content = M('newcomments')->where($map)
                 ->getField('comment');          
    if($status == 'comment'){
      $type_title = array(
        '10' => '评论了您的视频',
        '20' => '评论了您的项目',
        '30' => '回复了您的评论',
      );
    }else if($status == 'like'){
      $type_title = array(
        '10' => '赞了您的视频',
        '20' => '赞了您的项目',
        '30' => '赞了您的评论',
      );
    }
    
    $title = $type_title[$row_type];
    $last_message_desc = $user_name.$title.'“'.$content.'”';
    return $last_message_desc;
  }
}
