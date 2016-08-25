<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;
use Common\Molecule\User\UserIsLiked;

class PresentTagBehavior extends CommonBehavior{

  const FIELDS = 'id, name, intro, photo_url, bg_photo_url, view_sum, collect_sum';

  static function commit(){
    # 检查必填参数
    $inquire_params = array('tag_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # tag_id可以是id也可是name
    if (!is_numeric($_REQUEST['tag_id'])) {
      $tag_id = M('app_tag', NULL)
                ->where(array('name' => $_REQUEST['tag_id']))
                ->getField('id');
    } else {
      $tag_id = M('app_tag', NULL)
                ->where(array('id' => $_REQUEST['tag_id']))
                ->getField('id');
    }
    if ($tag_id < 1) {
      $result->res = false;
      $result->msg = '不存在的话题id:'.$_REQUEST['tag_id'];
      return $result;
    }

    # 话题详情
    self::pick_page_params();
    self::pick_my_user_id();
    $tag = self::fetch($tag_id);
    $result->tag = $tag['tag'];
    $result->count = $tag['count'];
    $result->video = $tag['video'];
    M('app_tag', NULL)->where(array('id' => $tag_id))->setInc('view_sum', rand(1,5));
    return $result;
  }

  static function tag_pack($tag){
    if(empty($tag)) return array();
    $photo_url = C('cos.admin_bucket_url').'/sample/tag/coverImage/sp10.png';
    $bg_photo_url = C('cos.admin_bucket_url').'/sample/tag/bgImage/sp'.rand(10,20).'.png';
    $tag['photo_url'] = empty($tag['photo_url'])? $photo_url: $tag['photo_url'];
    $tag['bg_photo_url'] = empty($tag['bg_photo_url'])? $bg_photo_url: $tag['bg_photo_url'];
    return $tag;
  }

  static function fetch($tag_id){
    $rows['video'] = array();
    $rows['count'] = 0;
    $tag = M('app_tag', NULL)
           ->field(self::FIELDS)
           ->find($tag_id);
    $rows['tag'] = self::tag_pack($tag);
    if(!empty($rows['tag'])){
      #vid
      $where = array();
        $where['fact_type'] = '10';
        $where['tag_id'] = $tag_id;
      $video_ids = M('app_taged_facts', NULL)->field('fact_id')->where($where)->select();
      foreach ($video_ids as $key => $value) {
        $vid[] = $value['fact_id'];
      }
      #video
      if(!empty($vid)){
        $option = array();
          $option['id'] = $vid;
          $option['status'] =  UserBrifeVideos::YI_SHEN_HE;
          $option['order_prop'] = 'hot';
          $option['order_asc'] = 'desc';
        $where = array();
          $where['id'] = array('in', $vid);
          $where['status'] = UserBrifeVideos::YI_SHEN_HE;
        $rows['count'] = M('app_video', NULL)->where($where)->count();
        $my_facts = 
          UserBrifeVideos::all($_REQUEST['current_page'], $_REQUEST['page_size'], $option);
        $rows['video'] =
          UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $my_facts);
      }
    }
    return $rows;
  }
}
