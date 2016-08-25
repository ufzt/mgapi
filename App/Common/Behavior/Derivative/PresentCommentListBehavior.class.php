<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Atom\UserIAN;
use Common\Molecule\User\UserIsLiked;

class PresentCommentListBehavior extends CommonBehavior {

  const FIELDS = 'id, userid as user_id, topid, type, 
                addtime as pub_time, comment as text, liked';

  static function commit(){
    # 检查必填参数
    $inquire_params = array('relation_id', 'type');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # type === comment
    if ('comment' != strtolower($_REQUEST['type']) ) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }

    self::pick_page_params();
    self::pick_my_user_id();
    $data = self::fetch();
    $result->top = self::fetch_topic();
    $result->data = $data;
    return $result;
  }

  static function fetch(){
    $map = array();
      $map['topId'] = $_REQUEST['relation_id'];
      $map['type'] = C('type.'.strtolower($_REQUEST['type']) );
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $rows = M('newcomments')->field(self::FIELDS)->where($map)
            ->order('Id desc')->limit($limit)
            ->select();
    if(empty($rows)) return array();
    $rows = self::mapping($rows);
    return $rows;
  }

  static function fetch_topic(){
    $map = array();
      $map['Id'] = $_REQUEST['relation_id'];
    $rows = M('newcomments')
            ->field(self::FIELDS)
            ->where($map)
            ->select();
    if(empty($rows)) return array();
    # 6个最新头像
    $map = array();
      $map['relation_id'] = $_REQUEST['relation_id'];
      $map['type'] = C('type.'.strtolower($_REQUEST['type']) );
    $count = M('user_liked')->where($map)->count();        
    $user_avatar = array();
    $user = M('user_liked')->field('user_id')
            ->where($map)
            ->order('add_time desc')
            ->limit('0,6')
            ->select();
    $user_ids = array();
    foreach ($user as $key => $value) {
      $user_ids[] = $value['user_id'];
    }
    $ians = UserIAN::dozen($user_ids);
    foreach ($ians as $key => $value) {
      $user_avatar[] = $value['user_avatar'];
    }
    $rows = self::mapping($rows);
    $data['liked_sum'] = $count;
    $data['liked'] = $user_avatar;
    $data['detail'] = $rows;
    return $data;
  }

  static function mapping($rows){
    foreach ($rows as $key => $value) {
      $rows[$key]['type'] = array_search($value['type'], C('type'));
      $comment_ids[] = $value['id'];
      $user_ids[] = $value['user_id'];
    }
    #添加字段：是否点赞
    $rows = UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $rows);
    #添加字段：user_name user_avatar
    $ians = UserIAN::dozen($user_ids);
    foreach ($rows as $i => $r) {
      if (isset($ians[$r['user_id']]) ) {
        $ian = $ians[$r['user_id']];
        $rows[$i]['user_name']   = $ian['user_name'];
        $rows[$i]['user_avatar'] = $ian['user_avatar'];
      }
      else {
        $rows[$i]['user_name']   = '';
        $rows[$i]['user_avatar'] = UserIAN::empty_avatar();
      }
    }
    return $rows;
  }
}
