<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeProjects;
use Common\Atom\UserBrifeVideos;
use Common\Atom\Goods;

class SearchFactsBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('type');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = array('video', 'project', 'goods');
    if (!in_array($_REQUEST['type'], $type)) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }
    self::pick_page_params();
    $data = self::search();
    #data
    $result->count = $data['count'];
    $result->data = $data['data'];
    return $result;
  }

  static function search(){
    $_REQUEST['keyword'] = str_replace(
      "%","\%",$_REQUEST['keyword']
    );
    $keyword = isset($_REQUEST['keyword'])? $_REQUEST['keyword']: '';
    if(empty($keyword)){
      $data = array();
        $data['count'] = 0;
        $data['data'] = array();
      return $data;
    }

    $data['count'] = 0;
    $data['data'] = array();

    $option = array();
      $option['title_like'] = $keyword;
      $option['total_row_sum'] = true;
    if($_REQUEST['type'] == 'video'){
      $option['status'] = UserBrifeVideos::YI_FA_BU;
      $data['count'] = UserBrifeVideos::all($_REQUEST['current_page'],
                                            $_REQUEST['page_size'],
                                            $option);
      if ($data['count'] > 0) {
        unset($option['total_row_sum']);
        $data['data'] = UserBrifeVideos::all($_REQUEST['current_page'],
                                             $_REQUEST['page_size'],
                                             $option);
      }
    }
    else if($_REQUEST['type'] == 'project'){
      $data['count'] = UserBrifeProjects::all($_REQUEST['current_page'],
                                            $_REQUEST['page_size'],
                                            $option);
      if ($data['count'] > 0) {
        unset($option['total_row_sum']);
        $data['data'] = UserBrifeProjects::all($_REQUEST['current_page'],
                                             $_REQUEST['page_size'],
                                             $option);
      }
    }
    else if($_REQUEST['type'] == 'goods'){
      $data['count'] = Goods::all($_REQUEST['current_page'],
                                  $_REQUEST['page_size'],
                                  $option);
      if ($data['count'] > 0) {
        unset($option['total_row_sum']);
        $data['data'] = Goods::all($_REQUEST['current_page'],
                                   $_REQUEST['page_size'],
                                   $option);
      }
    }
    return $data;
  }
}
