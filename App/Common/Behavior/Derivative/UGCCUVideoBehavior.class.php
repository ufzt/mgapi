<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\UserBrifeVideos;
use Common\Atom\AddTagsVPC;
use Common\Atom\Result;

class UGCCUVideoBehavior extends CommonBehavior{

  const DRAFT    = 'draft';    #提交草稿
  const FINALIZE = 'finalize'; #提交定稿

  static function is_my_ugc_draft(){
    $map = array();
      $map['id']      = $_REQUEST['video_id'];
      $map['user_id'] = $_REQUEST['my_user_id'];
      $map['status']  = UserBrifeVideos::CAO_GAO;
      $map['video_url']  = '';
    $row = M('app_video', NULL)->where($map)->find();
    return !empty($row);
  }

  static function commit(){
    # 检查必填参数
    $inquire_params = array('type','my_user_id','title','photo_url','video_url');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = $_REQUEST['type'];
    if (!in_array($type, array(self::DRAFT, self::FINALIZE))) {
      $result->res = false;
      $result->msg = '不存在的TYPE:'.$type;
      return $result;
    }

    # 如果传了 video_id, 必填是本人的草稿视频
    if (isset($_REQUEST['video_id']) && false===self::is_my_ugc_draft() ) {
      $result->res = false;
      $result->msg = '不存在的视频id:'.$_REQUEST['video_id'];
      return $result;
    }

    # 检查UGC链接
    $cos_urls = array($_REQUEST['photo_url'],$_REQUEST['video_url']);
    $result = self::modify_cos_file($cos_urls);
    if (false === $result->res)
      return $result;

    # 保存数据
    if (isset($_REQUEST['video_id']))
      $result->video_id = self::update();
    else
      $result->video_id = self::add();
    return $result;
  }

  private static function modify_cos_file($cos_urls){
    $result = new Result();
    foreach ($cos_urls as $url) {
      $start = strpos($url, 'file.myqcloud.com');
      if ($start < 1) {
        $result->res = false;
        $result->msg = '非法的UGC链接'.$url;
        return $result;
      }

      # COS操作
      $bucket_name = C('cos.user_bucket');
      $bucket_path = substr($url, $start + strlen('file.myqcloud.com'));
      $response = COSUpload::update($bucket_name, $bucket_path);
      # debug log
      $log = 'COS File => '.$url."\n response => ".json_encode($response);
      self::mp_log(__METHOD__, $log, self::LOG_DEBUG);
      if (0 != $response['code']) {
        $result->res = false;
        $result->msg = 'COS更新文件'.$url
                      ."出错: code=>".$response['code']
                      .", message=>".$response['message'];
        return $result;
      }
    }
    $result->success();
    return $result;
  }

  static function update(){
    $data = array();
      $data['title']   = $_REQUEST['title'];
      $data['photo'] = $_REQUEST['photo_url'];
      $data['video_url2'] = $_REQUEST['video_url'];
      $data['status']  = UserBrifeVideos::CAO_GAO;
      if($_REQUEST['type'] == self::FINALIZE) {
        $data['status'] = UserBrifeVideos::DING_GAO;
        $data['pub_time'] = time();
      }
      $data['update_time'] = time();
      $data['update_user'] = 'self';
    $where = array();
      $where['id'] = $_REQUEST['video_id'];
    M('app_video', NULL)->where($where)->data($data)->save();
    #tag
    $tag = self::get_tag();
    if(!empty($tag)){
      $status = $data['status'];
      $data = array();
          $data['is_pub'] = $status;
          $data['fact_id'] = $_REQUEST['video_id'];
          $data['fact_type'] = 10;
      AddTagsVPC::add_tag($tag, $data);
    }
    return $_REQUEST['video_id'];
  }

  static function add(){
    $data = array();
      $data['user_id'] = $_REQUEST['my_user_id'];
      $data['title']   = $_REQUEST['title'];
      $data['photo'] = $_REQUEST['photo_url'];
      $data['video_url2'] = $_REQUEST['video_url'];
      $data['status']  = UserBrifeVideos::CAO_GAO;
      if($_REQUEST['type'] == self::FINALIZE) {
        $data['status'] = UserBrifeVideos::DING_GAO;
        $data['pub_time'] = time();
      }
      $data['update_time'] = time();
      $data['update_user'] = 'self';
    $video_id = M('app_video',NULL)->data($data)->add();
    if(!empty($video_id)){
        $tag = self::get_tag();
        if(!empty($tag)){
            $status = $data['status'];
            $data = array();
                $data['is_pub'] = $status;
                $data['fact_id'] = $video_id;
                $data['fact_type'] = 10;
            AddTagsVPC::add_tag($tag, $data);
        }
    }
    return $video_id;
  }

  #过滤获取title中传过来的标签
  static function get_tag(){
    $tag = array();
    $str = $_REQUEST['title'];
    $arr = explode('#', $str);
    foreach ($arr as $key => $value) {
        if($key%2 != 0 && $key != count($arr)-1 && !empty($value) && mb_strlen($value, 'UTF-8')<=12){
            $tag[] = $value;
        }
    }
    return $tag;
  }
}
