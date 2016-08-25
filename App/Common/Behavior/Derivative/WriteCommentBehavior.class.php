<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;

class WriteCommentBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id', 'relation_id', 'type', 'comment');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # type === comment
    if ('comment' != strtolower($_REQUEST['type']) ) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }
    $result = WriteTopicBehavior::addComment('Comment');
    return $result;
  }
}
