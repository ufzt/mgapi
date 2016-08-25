<?php
namespace Common\Behavior\Derivative;

use Common\Atom\Result;
use Common\Behavior\CommonBehavior;

class FollowListMakerBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('my_user_id', 'follow_user_ids');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 关注创客
    $result = self::add();
    return $result;
  }

  static function add(){
    $result = new Result();
    #关注用户筛选
    $follow_user_ids = self::follow_screen($_REQUEST['follow_user_ids']);
    if(empty($follow_user_ids)){
      $result->msg = '用户已关注';
      return $result;
    }
    #关注记录
    $data = array();
    foreach($follow_user_ids as $key=>$val)
      $data[] = array('userId'=>$_REQUEST['my_user_id'],
                      'followUser'=>$val,
                      'addTime'=>time() );
    # 事务处理
    $trace_code = '';
    $model = M('user_follow');
    $model->startTrans();
    #关注记录添加
    $step1 = $model->addAll($data);
    #用户更新关注数
    $count = count($follow_user_ids);
    $step2 = M('user')->where(array('userId'=>$_REQUEST['my_user_id']))
              ->setInc('follow', $count);
    #用户更新粉丝数（被关注数）
    $where = array();
      $where['userId'] = array('in', $follow_user_ids);
    $step3 = M('user')->where($where)
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

  /**
   * [follow_screen 筛选关注用户数据]
   * @param  [array] $follow_user_ids [要关注的用户]
   * @return [array] $follow_user_ids [经过筛选后的关注用户]
   */
  static function follow_screen($follow_user_ids){
    $follow_user_ids = explode(',', $follow_user_ids);
    #数组去重
    $follow_user_ids = array_values(array_unique($follow_user_ids));
    #判断要关注的用户是否存在
    $map = array();
      $map['userId'] = $_REQUEST['my_user_id'];
      $map['followUser'] = array('in', $follow_user_ids);
    $rows = M('user_follow')->field('followUser as follow_user')->where($map)
            ->select();
    if(!empty($rows)){
      #数组重新整合 二维变一维数组
      $follow_exist = array();
      foreach ($rows as $key => $value)
        $follow_exist[] = $value['follow_user'];
      #去除已关注的用户
      foreach ($follow_user_ids as $key => $value) {
        if(in_array($value,$follow_exist))
          unset($follow_user_ids[$key]);
      }
    }
    return $follow_user_ids;
  }
}
