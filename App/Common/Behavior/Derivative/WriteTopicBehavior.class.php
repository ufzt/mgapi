<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;
use Common\Molecule\User\UserofFact;

class WriteTopicBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id', 'relation_id', 'type', 'comment');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 枚举 type 参数
    $type = C('type.'.strtolower($_REQUEST['type']) );
    if (empty($type) || 'comment' == strtolower($_REQUEST['type']) ) {
      $result = new Result();
      $result->msg = '不存在的TYPE:'.$_REQUEST['type'];
      return $result;
    }
    $result = self::addComment();
    return $result;
  }

  static function addComment(){
    $result = new Result();
    #type 表类型
    $type = strtolower($_REQUEST['type']);
    # 事务处理
    $trace_code = '';
    $model = M('newcomments');
    $model->startTrans();
    $data = array();
      $data['comment'] = $_REQUEST['comment'];
      $data['userId'] = $_REQUEST['my_user_id'];
      $data['topId'] = $_REQUEST['relation_id'];
      $data['relation_user_id'] = UserofFact::get_user_id(strtolower($_REQUEST['type']), $_REQUEST['relation_id']);
      $data['type'] = C('type.'.strtolower($_REQUEST['type']) );
      $data['addTime'] = time();
    $step1 = $model->data($data)->add();
    $step2 = false;
      $map = array('id'=>$_REQUEST['relation_id'], 'Id'=>$_REQUEST['relation_id']);
      if($type == 'video'){
        $step2 = M('app_video',NULL)->where($map)->setInc('topic_sum');
      }elseif($type == 'project'){
        $step2 = M('project')->where($map)->setInc('p_comments');
      }elseif($type == 'comment'){
        $step2 = M('newcomments')->where($map)->setInc('comment_sum');
      }
    #判断数据数量更新是否成功
    if (false === $step1 || false === $step2){
      $result->msg = '评论失败';
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
