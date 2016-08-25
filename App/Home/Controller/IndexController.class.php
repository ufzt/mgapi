<?php
namespace Home\Controller;

use Think\Log;
use Common\Controller\HomeBaseController;

class IndexController extends HomeBaseController {

  public function _diecho($data){
    Log::record('API end with '.json_encode($data), Log::DEBUG);

    if (IS_GET) {
      header("Content-type: application/x-javascript; charset=utf-8");
      die($_GET['callback'].'('.json_encode($data).')');
    }

    die(json_encode($data));
  }

  public function index(){}

}
