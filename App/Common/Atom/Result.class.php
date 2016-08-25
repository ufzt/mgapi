<?php
namespace Common\Atom;

class Result{

  const DDOS_ATTACK     = '911'; //DDos攻击
  const ILLEGAL_SIGN    = '001'; //签名错误
  const EMPTY_PARAM     = '002'; //非空参数没有值(参数不完整)

  public function __construct($default_result = false){
    $this->res = $default_result;
    if (true === $default_result)
      $this->success();
  }

  public function success($msg = 'ok'){
    $this->res = true;
    $this->msg = $msg;
  }
}
