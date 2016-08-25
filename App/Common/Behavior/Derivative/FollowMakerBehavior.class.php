<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;

class FollowMakerBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id', 'follow_user_id');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 关注/取消关注创客
    if ('unfollow_maker' == $_REQUEST['service'])
      $result = self::unfollow();
    else
      $result = self::follow();
    return $result;
  }

  static function follow(){
    #判断是否关注
    $map = array();
      $map['userId'] = $_REQUEST['my_user_id'];
      $map['followUser'] = $_REQUEST['follow_user_id'];
    $is_exist = self::is_exist($map);
    $result = new Result();
    if(!empty($is_exist)){
      $result->msg = '该用户已关注';
      return $result;
    }

    #关注
    $map['addTime'] = time();
    # 事务处理
    $trace_code = '';
    $model = M('user_follow');
    $model->startTrans();
    $step1 = $model->add($map);
    #用户更新关注数
    $step2 = M('user')->where(array('userId'=>$_REQUEST['my_user_id']))
              ->setInc('follow');
    #用户更新粉丝数（被关注数）
    $step3 = M('user')->where(array('userId'=>$_REQUEST['follow_user_id']))
              ->setInc('fans');
    #判断
    if (false === $step1 || false === $step2 || false === $step3){
      $result->msg = '关注失败';
      // 回滚事务
      $model->rollback();
      // 写日志
      if (false === $step1) $trace_code.= '1'; else $trace_code.= '0';
      if (false === $step2) $trace_code.= '1'; else $trace_code.= '0';
      if (false === $step3) $trace_code.= '1'; else $trace_code.= '0';
      $log = ">>>TRANSACTION FAILED.\n\t>>>TRACE_CODE IS => ".$trace_code;
      self::mp_log(__METHOD__, $log);
    }else{
      $result->success();
      // 提交事务
      $model->commit();
    }
    return $result;
  }

  static function unfollow(){
    $result = new Result();
    # 事务处理
    $trace_code = '';
    $model = M('user_follow');
    $model->startTrans();
    $map = array();
      $map['userId'] = $_REQUEST['my_user_id'];
      $map['followUser'] = $_REQUEST['follow_user_id'];
    $step1 = $model->where($map)->delete();
    #用户更新关注数
    $step2 = M('user')->where(array('userId'=>$_REQUEST['my_user_id']))
              ->setDec('follow');
    #用户更新粉丝数（被关注数）
    $step3 = M('user')->where(array('userId'=>$_REQUEST['follow_user_id']))
              ->setDec('fans');
    #判断
    if (false === $step1 || false === $step2 || false === $step3){
      $result->msg = '取消关注失败';
      // 回滚事务
      $model->rollback();
      // 写日志
      if (false === $step1) $trace_code.= '1'; else $trace_code.= '0';
      if (false === $step2) $trace_code.= '1'; else $trace_code.= '0';
      if (false === $step3) $trace_code.= '1'; else $trace_code.= '0';
      $log = ">>>TRANSACTION FAILED.\n\t>>>TRACE_CODE IS => ".$trace_code;
      self::mp_log(__METHOD__, $log);
    }else{
      $result->success();
      // 提交事务
      $model->commit();
    }
    return $result;
  }

  private static function is_exist($map){
    $row = M('user_follow')->where($map)->find();
    return $row;
  }
}
