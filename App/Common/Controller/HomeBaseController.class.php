<?php
namespace Common\Controller;

use Think\Controller;
use Common\Atom\Result;
use Common\Behavior\EntryBehavior;

class HomeBaseController extends Controller {

  # 终止输出方法(die & echo)
  public function _diecho($data){
    # TO BE OVERRIDDEN
  }

  # 不存在的service
  public function _die_with_unknow_service($name){
    $result = new Result();
      $result->res = false;
      $result->msg = '不存在的SERVICE:'.$name;
    $this->_diecho($result);
  }

  public function _initialize(){
    # EntryBehavior
    $result = EntryBehavior::commit();
    if (false === $result->res)
      $this->_diecho($result);

    # Specify Behavior
    $service = $_REQUEST['service'];
    $klass = C('service.'.$service);
    if (empty($klass))
      $this->_die_with_unknow_service($service);

    if (is_array($klass)) {
      # 分组接口
      list($group, $key, $rest) = explode('.', $service.'.', 3);
      if (empty($key) || !isset($klass[$key]))
        $this->_die_with_unknow_service($service);

      $klass = '\\Common\Behavior\\'.ucfirst($group).'\\'.$klass[$key];
    }
    else
      $klass = '\\Common\Behavior\Derivative\\'.$klass;

    # Load Behavior
    $Behavior = new $klass;
    $result = $Behavior->commit();
      $result->service  = $_REQUEST['service'];
      $result->order_no = $_REQUEST['order_no'];
    $this->_diecho($result);
  }
}
