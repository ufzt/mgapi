<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\Result;
use Common\Atom\UserBrifeProjects;
use Common\Molecule\User\UserIsLiked;

class ListHotProjectsBehavior extends CommonBehavior{

  static function commit(){
    $result = new Result(true);
    self::pick_my_user_id();
    $result->data = self::fetch();
    return $result;
  }

  #p_classid 
  static function fetch(){
    #累计筹资和支持人数
    $where = array();
      $where['p_endtime'] = array('lt',time());
    $fields = 'SUM(p_current_money) as money, SUM(p_supporters) as support';
    $total = M('project')->field($fields)->where($where)->select();
    $data['total']['money'] = round($total[0]['money']/10000, 1);
    $data['total']['support'] =  sprintf("%.1f", substr(sprintf('%.3f',  (float)$total[0]['support']/10000), 0, -2));
    if(substr($data['total']['support'], -1) == 0)
      $data['total']['support'] = floor($data['total']['support']);
    #底部图片
    $data['bottom']['photo_url'] = C('cos.admin_bucket_url')
                                  .'/sample/ad/201607272459.png';
    $data['bottom']['jump_url'] = 'http://www.taobao.com';
    $data['branch_name'] = C('branch.name');
    $data['branch_image'] = C('branch.image');
    #where
    $option = array();
      $option['p_endtime'] = time();
    $project = UserBrifeProjects::all(1, 4, $option);
    $data['hot'] = self::pack($project);
    #branch分类
    $dict = C('branch.assoc');
    $branch_assoc = array('smart_hardware', 'virtual_reality', 'robot', 'designe');
    foreach ($branch_assoc as $key => $value) {
        $option = array();
          $option['p_classid'] = $dict[$value]['project_class_id'];
        $project = UserBrifeProjects::all(1, 4, $option);
        $data[$value] = self::pack($project);
    }
    return $data;
  }

  static function pack($project){
    $count = count($project);
    if($count == 3){
      $project = array_slice($project, 0, 2);
    }else if($count == 1){
      $project = array();
    }
    #is_liked
    $project = UserIsLiked::is_liked_by($_REQUEST['my_user_id'], $project);
    #截取%
    foreach ($project as $key => $value) {
      $project[$key]['progress'] = substr($value['progress'], 0, strlen($value['progress'])-1);
    }
    return $project;
  }
}
