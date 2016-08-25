<?php
namespace Common\Molecule\Video;

use Common\Atom\UserIAN;
use Common\Atom\UserBrifeVideos;

class VideoDetail{

  static function fetch($video_id){
    $map = array();
      $map['id'] = $video_id;
      $map['user_id'] = array('gt',0);
      $map['status'] = array('in', UserBrifeVideos::YI_FA_BU);
      if ($_REQUEST['service'] == 'video_preview')
        $map['status'] = array('in', UserBrifeVideos::CAO_GAO);
      else if ($_REQUEST['service'] == 'video_audit')
        $map['status'] = array('in', UserBrifeVideos::QUAN_BU);
    $row = M('app_video',NULL)->field(UserBrifeVideos::FIELDS.', content')
           ->where($map)
           ->find();
    if(empty($row))
      return array();
    else if ($_REQUEST['service'] == 'video_detail'){
      # 更新浏览数
      M('app_video', NULL)->where(array('id'=>$video_id))->setInc('view_sum');
      $view_sum = M('app_video', NULL)->where(array('id'=>$video_id))
                  ->getField('view_sum');
      # 热度值＝浏览数+2*UP数+3*分享数+4*评论数
      if (0 == $view_sum %5) {
        $sql = ' UPDATE __TABLE__ SET hot =';
        $sql.= ' view_sum + 2*like_sum + 3*collect_sum + 4*topic_sum';
        $sql.= ' WHERE id = '.$video_id;
        M('app_video', NULL)->execute($sql);
      }
    }

    return self::pack($row);
  }

  static function pack($single_video){
    # 塞进
    $rows = UserBrifeVideos::pack( array($single_video));
    # 取回
    $row = reset($rows);
    # tags
    $tag_rows = M('app_taged_facts as m',NULL)->field('t.name')
              ->join('app_tag as t on t.id = m.tag_id')
              ->where(array('m.fact_id'=>$row['id'], 'm.fact_type'=>10))
              ->select();
    $tags = array();
    if (!empty($tag_rows)) {
      foreach ($tag_rows as $r)
        $tags[] = $r['name'];
    }
    $row['tags'] = array_values(array_unique($tags));
    return $row;
  }
}
