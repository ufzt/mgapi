<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Molecule\User\UserofFact;

class ClickLikedBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id', 'relation_id', 'type');
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

    # 已点赞？
    if (self::is_liked()) {
      $result = new Result();
      $result->msg = '该用户已点赞';
      return $result;
    }

    # 点赞
    $result = self::liked();
    return $result;
  }

  static function is_liked(){
    // 判断是否在user_liked表中存在
    $map = array();
      $map['user_id'] = $_REQUEST['my_user_id'];
      $map['relation_id'] = $_REQUEST['relation_id'];
      $map['type'] = C('type.'.strtolower($_REQUEST['type']) );
    $row = M('user_liked')->where($map)->find();
    return !empty($row);
  }

  static function liked(){
    $result = new Result();
    #type 表类型
    $type = strtolower($_REQUEST['type']);
    # 事务处理
    $trace_code = '';
    $model = M('user_liked');
    $model->startTrans();
    $data = array();
      $data['user_id'] = $_REQUEST['my_user_id'];
      $data['type']    = C('type.'.$type);
      $data['relation_id'] = $_REQUEST['relation_id'];
      $data['relation_user_id'] = UserofFact::get_user_id($type, $_REQUEST['relation_id']);
      $data['add_time'] = time();
    $step1 = $model->add($data);
    $step2 = false;
      $map = array('id'=>$_REQUEST['relation_id'],'Id'=>$_REQUEST['relation_id']);
      if ('video' == $type)
        $step2 = M('app_video',NULL)->where($map)->setInc('like_sum');
      else if ('project' == $type)
        $step2 = M('project')->where($map)->setInc('liked');
      else if ('comment' == $type)
        $step2 = M('newcomments')->where($map)->setInc('liked');
    if (false === $step1 || false === $step2){
      $result->msg = '点赞失败';
      // 回滚事务
      $model->rollback();
      // 写日志
      if (false === $step1) $trace_code.= '1'; else $trace_code.= '0';
      if (false === $step2) $trace_code.= '1'; else $trace_code.= '0';
      $log = ">>>TRANSACTION FAILED.\n\t>>>TRACE_CODE IS => ".$trace_code;
      self::mp_log(__METHOD__, $log);
    } else {
      $result->success();
      // 提交事务
      $model->commit();
    }
    return $result;
  }
}
