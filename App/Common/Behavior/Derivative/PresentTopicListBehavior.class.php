<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Atom\UserIAN;
use Common\Molecule\User\UserIsLiked;

class PresentTopicListBehavior extends CommonBehavior {

  const FIELDS = 'id, userid as user_id, topid, type, 
                  addtime as pub_time, comment as text, liked';

  static function commit(){
    # 检查必填参数
    $inquire_params = array('relation_id', 'type');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = C('type.'.strtolower($_REQUEST['type']) );
    if (empty($type) || 'comment' == strtolower($_REQUEST['type']) ) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }

    self::pick_page_params();
    self::pick_my_user_id();
    $data = self::fetch();
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

  static function mapping($rows){
    foreach ($rows as $key => $value) {
      $rows[$key]['type'] = 'comment';
      $comment_ids[] = $value['id'];
      $user_ids[] = $value['user_id'];
    }
    #添加字段：是否点赞
    $rows = UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $rows);
    #添加字段：子评论数
    $dict = self::reply_sum($comment_ids);
    #添加字段：user_name user_avatar
    $ians = UserIAN::dozen($user_ids);
    foreach ($rows as $key => $value) {
      if(isset($dict[$value['id']]))
        $rows[$key]['reply_count'] = $dict[$value['id']];
      else
        $rows[$key]['reply_count'] = 0;
      if (isset($ians[$value['user_id']]) ) {
        $ian = $ians[$value['user_id']];
        $rows[$key]['user_name']   = $ian['user_name'];
        $rows[$key]['user_avatar'] = $ian['user_avatar'];
      }
      else {
        $rows[$key]['user_name']   = '';
        $rows[$key]['user_avatar'] = UserIAN::empty_avatar();
      }
    }
    return $rows;
  }

  static function reply_sum($comment_ids){
    $map = array();
    $map['topId'] = array('in', $comment_ids);
    $comments = M('newcomments')->field('topId, count(*) as sum')
            ->where($map)
            ->group('topId')
            ->select();
    if (empty($comments)) $comments = array();
    $dict = array();
    foreach ($comments as $row) {
      $comment_id = $row['topid'];
      $dict[$comment_id] = $row['sum'];
    }
    return $dict;
  }

}
