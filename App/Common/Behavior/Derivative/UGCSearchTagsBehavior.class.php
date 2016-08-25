<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;

class UGCSearchTagsBehavior{

  static function commit(){
    # 检查必填参数
    $result = new Result(true);
    $result->tags = self::search_tags();
    return $result;
  }

  static function recommend_tags(){
    $order = 'pub_times desc';
    $where = array();
      $where['is_recommend'] = 1;
    return M('app_tag', NULL)->field('name')
           ->where($where)->order($order)
           ->select();
  }

  static function search_tags(){
    if (!isset($_REQUEST['tag']) || empty($_REQUEST['tag']))
      return self::recommend_tags();

    $tag = $_REQUEST['tag'];
    $order = 'pub_times desc';
    $where = array();
      $where['name'] = array('like',array("%{$tag}%"));
    $rows = M('app_tag', NULL)->field('name')->where($where)->order($order)
            ->select();
    if (empty($rows)) $rows = array();
    return $rows;
  }
}
