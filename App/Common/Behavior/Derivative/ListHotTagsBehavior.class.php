<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;

class ListHotTagsBehavior extends CommonBehavior {

  const FIELDS = 'id, name, intro, photo_url, view_sum';

  static function commit(){
    $result = new Result(true);
    self::pick_page_params();
    $data = self::fetch();
    $result->count = $data['count'];
    $result->data = $data['data'];
    return $result;
  }

  static function tag_pack($tag){
    if(empty($tag)) return array();
    $photo_url = C('cos.admin_bucket_url').'/sample/tag/coverImage/sp10.png';
    foreach ($tag as $key => $value) {
      if(empty($value['photo_url']))
        $tag[$key]['photo_url'] = $photo_url;
      $tag[$key]['name'] = '#'.$value['name'].'#';
    }
    return $tag;
  }

  static function fetch(){
    #where
    $where = array();
      $where['is_hot'] = 1;
    #limit
    $offset = $_REQUEST['page_size'] * ($_REQUEST['current_page'] -1);
    $length = $_REQUEST['page_size'];
    $limit  = $offset.','.$length;
    $count = M('app_tag', NULL)->where($where)->count();
    #select
    $tag = M('app_tag', NULL)->field(self::FIELDS)
            ->where($where)
            ->order('pub_times desc')//使用次数排序
            ->limit($limit)
            ->select();
    $rows['data'] = self::tag_pack($tag);
    $rows['count'] = $count;
    return $rows;
  }
}
