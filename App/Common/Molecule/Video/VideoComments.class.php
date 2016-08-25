<?php
namespace Common\Molecule\Video;

use Common\Atom\UserIAN;

class VideoComments{

  # 统计子评论数量
  # 可接受comment_ids数组, 也可接受单个comment_id
  static function sum_reply($comment_ids){
    if (is_string($comment_ids) || is_numeric($comment_ids))
      $comment_ids = explode(',', $comment_ids);
    if (!is_array($comment_ids) || empty($comment_ids))
      return 0;

    $map = array();
      $map['topId'] = array('in', $comment_ids);
      $map['type'] = C('type.comment');
    $rows = M('newcomments')->field('topId, count(*) as sum')
            ->where($map)
            ->group('1')
            ->select();
    if (empty($rows)) $rows = array();
    $dict = array();
    foreach ($rows as $row) {
      $comment_id = $row['topid'];
      $dict[$comment_id] = $row['sum'];
    }
    return $dict;
  }

  const INIT_SIZE = 3;
  const FIELDS = 'id, userId as user_id, liked, comment as text,
                  addTime as pub_time, photo';

  static function fetch($video_id){
    $map = array();
      $map['topId'] = $video_id;
      $map['type'] = C('type.video');
    $rows = M('newcomments')->field(self::FIELDS)->where($map)
            ->order('Id desc')->limit(self::INIT_SIZE)
            ->select();
    if (empty($rows))
      return array();

    return self::pack($rows);
  }

  static function pack($rows){
    # 评论用户的头像和姓名
    $user_ids = array();
    # 评论对应的子评论数量
    $comment_ids = array();
    foreach($rows as $row){
      $comment_ids[] = $row['id'];
      $user_ids[] = $row['user_id'];
    }
    $ian_dict = UserIAN::dozen($user_ids);
    $reply_dict = self::sum_reply($comment_ids);
    foreach($rows as $i=>$r){
      if (isset($ian_dict[$r['user_id']])) {
        $arr = $ian_dict[$r['user_id']];
        $rows[$i]['user_name'] = $arr['user_name'];
        $rows[$i]['user_avatar'] = $arr['user_avatar'];
      }
      else {
        $rows[$i]['user_name'] = '';
        $rows[$i]['user_avatar'] = UserIAN::empty_avatar();
      }
      if (isset($reply_dict[$r['id']]))
        $rows[$i]['reply_count'] = $reply_dict[$r['id']];
      else
        $rows[$i]['reply_count'] = 0;
      # type
      $rows[$i]['type'] = 'comment';
      # photo
      if(!empty($r['photo'])){
        $photo = explode(',', $r['photo']);
        foreach($photo as $key=>$val)
          $photo[$key] = C('site').'/public/upload/comment/'.$val;
        $photo = array_values(array_unique($photo));
        $rows[$i]['photo'] = $photo;
      }
      else
        $rows[$i]['photo'] = array();
    }
    return $rows;
  }
}
