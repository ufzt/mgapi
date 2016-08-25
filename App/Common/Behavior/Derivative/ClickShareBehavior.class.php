<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;

class ClickShareBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('type', 'relation_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = C('type.'.strtolower($_REQUEST['type']) );
    if (empty($type)) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }

    # 分享
    self::update();
    return $result;
  }

  static function update(){
    $map = array();
      $map['id'] = $_REQUEST['relation_id'];
    if ('video' == strtolower($_REQUEST['type']))
      M('app_video',NULL)->where($map)->setInc('collect_sum');
    else if ('project' == strtolower($_REQUEST['type']))
      M('project')->where($map)->setInc('collection');
    else if ('tag' == strtolower($_REQUEST['type']))
      M('app_tag', NULL)->where($map)->setInc('collect_sum');
  }
}
