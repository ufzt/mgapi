<?php
namespace Common\Molecule\User;

class UserofFact{
  static function get_user_id($type, $fact_id){
    $map = array('id'=>$fact_id,'Id'=>$fact_id);

    if ('video' == $type)
      $user_id = M('app_video',NULL)->where($map)
                 ->getField('user_id');
    else if ('project' == $type)
      $user_id = M('project')->where($map)
                 ->getField('userid');
    else if ('comment' == $type)
      $user_id = M('newcomments')->where($map)
                 ->getField('userid');
    else $user_id = 0;

    return $user_id;
  }
}
