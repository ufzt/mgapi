<?php
namespace Common\Behavior\Activity;

use Common\Atom\Result;
use Common\Atom\Seed;
use Common\Behavior\CommonBehavior;
Vendor('alidayu.TopSdk');
Vendor('alidayu.TopSdk.top');
Vendor('alidayu.TopSdk.top.request');

class InviteSendSMSBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('sn_code', 'phone', 'dkey');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 检查dkey
    if (!seed::is_dkey($_REQUEST['dkey'])) {
      $result->res = false;
      $result->msg = '会话已过期';
      return $result;
    }

    # 检查邀请码是否过期
    $data = InviteVisitBehavior::fetch();
    if (empty($data)) {
      $result->res = false;
      $result->msg = '邀请码已过期';
      return $result;
    }

    # 检查手机号是不是已经被其他用户使用
    $where = array();
      $where['mobile'] = $_REQUEST['phone'];
      $where['userId'] = array('neq', $data['user_id']);
    $row = M('user')->where($where)->find();
    if (!empty($row)) {
      $result->res = false;
      $result->msg = '手机号已被用户'.$row['username'].'使用';
      return $result;
    }

    $code = rand(100000,999999);
    # 发送短信的安全检查
    $result = self::phone_check($code);
    if (false === $result->res)
      return $result;

    # 发送短信
    $status = self::alidayu($_REQUEST['phone'], "{'code':'{$code}'}");
    if($status == '-1'){
      $result->msg = '短信发送失败';
    }else{
      self::update($code);
      $result->success();
    }
    return $result;
  }

  static function update($code){
    #where
    $where['sn_code'] = $_REQUEST['sn_code'];
    #data
    $data = array();
      $data['phone'] = $_REQUEST['phone'];
      $data['verify_code'] = $code;
      $data['update_time'] = time();
    M('app_invite', NULL)->where($where)->save($data);
  }

  # 发送短信的安全检查
  static function phone_check($code){
    $result = new Result();
    $phone = $_REQUEST['phone'];
    if(!self::mobile($phone)){
      $result->msg = '手机号不符合规范';
      return $result;
    }

    $IP = get_client_ip();
    $IPcount = M('user_phone_verify')->where("IP='$IP'")->count();
    if($IPcount>15){
      $result->msg = '同一IP请求超过限制';
      return $result;
    }

    # 是否存在已发送记录
    $phone_verify = M('user_phone_verify')->where("mob='$phone'")->find();
    if($phone_verify){
      if(time()-$phone_verify["addtime"]<50){
        $result->msg = '频繁请求不能超过1分钟';
        return $result;
      }

      $data=array();
        $data['phonecode'] = $code;
        $data['addtime'] = time();
        $data['IP'] = $IP;
      M('user_phone_verify')->where("mob='$phone'")->data($data)->save();
    }else{
      $data=array();
        $data['mob']=$phone;
        $data['phonecode']=$code;
        $data['addtime']=time();
        $data['IP'] = $IP;
      M('user_phone_verify')->data($data)->add();
    }
    $result->success();
    return $result;
  }

  #发送短信
  static function alidayu($phone,$param,$signName='创客星球',$TemplateCode='SMS_6870032'){
    $c = new \TopClient;
    $c->appkey = '23334186';
    $c->secretKey = '0cf93c8bf67330b98692f80dc0d67638';
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    //$req->setExtend("123456");
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($signName);
    $req->setSmsParam($param);
    $req->setRecNum($phone);
    $req->setSmsTemplateCode($TemplateCode);
    $resp = $c->execute($req);
    return $resp;
  }
}
