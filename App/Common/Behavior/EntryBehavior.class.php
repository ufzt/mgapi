<?php
namespace Common\Behavior;

use Think\Log;
use Common\Atom\Result;

class EntryBehavior{

  const ILLEGAL_ACCESS_ALERT = '*** ILLEGAL ACCESS ***';

  static function commit(){
    # 0.检查jsonp
    if (!IS_POST && (!isset($_GET['callback']) || empty($_GET['callback'])) ) {
      $result = new Result();
      $result->code  = Result::EMPTY_PARAM;
      $result->param = 'callback';
      self::die_illegal($result);
    }

    # 1.检查签名
    $excpet = self::is_excpet();
    if (!$excpet) {
      $legal = isset($_POST['sign']);
      if ($legal) {
        $params = $_POST;
        unset($params['sign']);
        $legal = $_POST['sign']==self::sign($params);
      }
      // 没有签名或者签名不正确
      if (!$legal) {
        $result = new Result();
        $result->code = Result::ILLEGAL_SIGN;
        self::die_illegal($result);
      }
    }

    # 2.记录请求
    self::log();
    # 3.检查必填参数
    $inquire_params = array('order_no', 'channel', 'service');
    $result = CommonBehavior::inquire($inquire_params);
    return $result;
  }

  /*
   * 非法请求的处理
   * 1.json输出
   * 2.日志输出
   */
  private static function die_illegal($result){
    Log::write(self::ILLEGAL_ACCESS_ALERT."\n", Log::ALERT);

    header("Content-type: application/x-javascript; charset=utf-8");
    if (IS_GET && isset($_GET['callback']))
      die($_GET['callback'].'('.json_encode($result).')');
    else
      die(json_encode($result));
  }

  /*
   * 签名算法
   * a.将所有请求参数按照A-Z-a-z进行排序
   * b.排序完成后用&进行拼接
   * c.拼接完成后再在最后拼接上32位私钥
   * d.MD5加密最终的串得到签名
   */
  private static function sign(array $arr){
    // A-Z-a-z sort
    ksort($arr);
    // 签名
    $text = '';
      foreach ($arr as $k => $v) $text.= $k.'='.$v.'&';
      $text = substr($text, 0, strlen($text)-1);
      $text.= C('token');
    return md5($text);
  }

  /*
   * 记录请求
   * 1.数据库表 api_request_logs
   * 2.调试日志输出
   */
  private static function log(){
    $data = array();
      $data['add_time'] = time();
      if (IS_POST) {
        $data['params'] = json_encode($_POST);
        if (!empty($_POST['order_no']))  $data['order_no'] = $_POST['order_no'];
        if (!empty($_POST['service']))   $data['service']  = $_POST['service'];
        if (!empty($_POST['channel']))   $data['channel']  = $_POST['channel'];
      }
      else {
        $data['params'] = json_encode($_GET);
        if (!empty($_GET['order_no']))  $data['order_no'] = $_GET['order_no'];
        if (!empty($_GET['service']))   $data['service']  = $_GET['service'];
        if (!empty($_GET['channel']))   $data['channel']  = $_GET['channel'];
      }
    # 插入数据库
    M('api_request_logs', NULL)->add($data);
    # 记日志
    $data['add_time'] = date(DATE_RFC822);
    Log::record('API start with '.json_encode($data), Log::DEBUG);
  }

  /*
   * 绿色通道判定
   * 针对特定的 channel 和 service 不检查签名
   */
  private static function is_excpet(){
    # channel 绿色通道
    if (!isset($_REQUEST['channel'])) return false;
    $channel = $_REQUEST['channel'];
    if ('UFZT' == $channel)
      return true;

    # channel && service 绿色通道
    if (!isset($_REQUEST['service'])) return false;
    $service = $_REQUEST['service'];
    # H5
    if ('00HH51' == $channel &&
        in_array($service, array('video_detail',
                                 'tag_detail',
                                 #invite
                                 'activity.visit_invite',
                                 'activity.send_invite_sms',
                                 'activity.submit_invite_step1',
                                 'activity.submit_invite_step2',
                                 'activity.submit_invite_step3',
                                 )))
      return true;

    # WWW
    if ('00HH52' == $channel &&
        in_array($service, array('video_preview',
                                 'video_audit',
                                 #invite
                                 'activity.create_invite',
                                 )))
    {
      # 测试服正式服区分
      if (isset($_REQUEST['site']) && substr($_REQUEST['site'],0,7) == 'http://')
        C('site', $_REQUEST['site']);
      return true;
    }

    return false;
  }
}
