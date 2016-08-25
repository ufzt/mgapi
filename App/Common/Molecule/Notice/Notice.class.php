<?php
namespace Common\Molecule\Notice;

class Notice{

  # 通知类型Type
  const PRD_PAY_SUCCESS       = '21';               # 支付成功
  const PRD_SOLDOUT_SUCCESS   = '31';               # 众筹成功
    const PRD_SOLDOUT_FAILED  =   '30';             # 众筹失败
  const PRD_DELIVER_SUCCESS   = '41';               # 已发货
  const PRD_REFUND_SUCCESS    =   '101';            # 退款成功

  static function type_name_dict(){
    return array(self::PRD_PAY_SUCCESS     => 'product_pay_success',
                 self::PRD_SOLDOUT_SUCCESS => 'product_soldout_success',
                 self::PRD_SOLDOUT_FAILED  => 'product_soldout_failed',
                 self::PRD_DELIVER_SUCCESS => 'product_deliver_success',
                 self::PRD_REFUND_SUCCESS  => 'product_refund_success',);
  }

  # 模板换行符
  const SLA = "\n";
  # 模板变量符 => '?'
  static function type_tpl($type){
    switch ($type) {
      case self::PRD_PAY_SUCCESS:
        return '支持金额：'.                self::SLA.
               '￥?'.                      self::SLA.
               '订单号：?'.                 self::SLA.
               '支付时间：?'.               self::SLA.
               '支持项目：?'.               self::SLA;
        break;

      case self::PRD_SOLDOUT_SUCCESS:
        return '恭喜您，'.                                      self::SLA.
                                                               self::SLA.
               '您支持的项目“?”在规定的时间内众筹成功！'.           self::SLA.
                                                               self::SLA.
               '我们将会在?日前完成事先承诺的回报，请您耐心等待。'.   self::SLA;
        break;

      case self::PRD_SOLDOUT_FAILED:
        return '很遗憾，'.                                                                                       self::SLA.
                                                                                                                self::SLA.
               '您支持的项目“?”众筹失败！'.                                                                        self::SLA.
                                                                                                                self::SLA.
               '您的支持金额将自动退款至【创客星球余额】中。您可以支持其他项目，或登录网站个人中心【申请取现】至您的收款账户。'.self::SLA;
        break;

      case self::PRD_DELIVER_SUCCESS:
        return '您支持的“?”已经发货，您可以前往“我的订单”查看物流详情。'.         self::SLA;
        break;

      case self::PRD_REFUND_SUCCESS:
        return '您的订单“?”已经成功退款。'.                                                                            self::SLA.
                                                                                                                    self::SLA.
               '您的支持金额将自动退款至【创客星球余额】中。您可以支持其他项目，或登陆官网【申请取现】至您的支付宝或其他付款账户。'. self::SLA;
        break;

      default:
        trigger_error($type.' is NOT legal, NOT a Notice Type!', E_USER_ERROR);
        break;
    }
  }

  static function get_content($type, $pieces){
    $tpl = self::type_tpl($type);
    $count_pieces = substr_count($tpl, '?');
    if (count($pieces) != $count_pieces)
      trigger_error('pieces is NOT matching!', E_USER_ERROR);
    # replace ? with pieces
    $pattern = array();
      for ($i=0; $i < $count_pieces; $i++)
        $pattern[] = '/\?/';
    return preg_replace($pattern, $pieces, $tpl , 1);
  }

  static function get_pieces($type,$user_id,$assoc_id){
    $pieces = array();
    switch ($type) {
      case self::PRD_PAY_SUCCESS:
        $map = array();
          $map['Id']     = $assoc_id;
          $map['userId'] = $user_id;
        $order = M('order')->field('orderNo,orderPrice,payTime,topId')
               ->where($map)
               ->find();
        if (empty($order)) return array();
        $project_name = M('project')->where(array('Id'=>$order['topid']))
                        ->getField('p_name');
        if (empty($project_name)) return array();
        $pieces[] = $order['orderprice'];
        $pieces[] = $order['orderno'];
        $pieces[] = date('Y-m-d H:i:s', $order['paytime']);
        $pieces[] = $project_name;
        break;

      case self::PRD_SOLDOUT_SUCCESS:
        $project = M('project')->field('p_name,p_endtime')
                   ->find($assoc_id);
        if (empty($project)) return array();
        $pieces[] = $project['p_name'];
        $pieces[] = date('Y-m-d', $project['p_endtime']);
        break;

      case self::PRD_SOLDOUT_FAILED:
        $project = M('project')->field('p_name,p_endtime')
                   ->find($assoc_id);
        if (empty($project)) return array();
        $pieces[] = $project['p_name'];
        break;

      case self::PRD_DELIVER_SUCCESS:
        $project = M('project')->field('p_name,p_endtime')
                   ->find($assoc_id);
        if (empty($project)) return array();
        $pieces[] = $project['p_name'];
        break;

      case self::PRD_REFUND_SUCCESS:
        $map = array();
          $map['Id']     = $assoc_id;
          $map['userId'] = $user_id;
        $order = M('order')->field('orderNo,orderPrice,payTime,topId')
               ->where($map)
               ->find();
        if (empty($order)) return array();
        $pieces[] = $order['orderno'];
        break;

      default:
        trigger_error($type.' is NOT legal, NOT a Notice Type!', E_USER_ERROR);
        break;
    }
    return $pieces;
  }

  static function type_desc($type){
    switch ($type) {
      case self::PRD_PAY_SUCCESS:
        return '支付成功';break;

      case self::PRD_SOLDOUT_SUCCESS:
        return '众筹成功';break;

      case self::PRD_SOLDOUT_FAILED:
        return '众筹失败';break;

      case self::PRD_DELIVER_SUCCESS:
        return '已发货';break;

      case self::PRD_REFUND_SUCCESS:
        return '退款成功';break;

      default:
        trigger_error($type.' is NOT legal, NOT a Notice Type!', E_USER_ERROR);
        break;
    }
  }

  static function type_name($type){
    $dict = self::type_name_dict();
    return $dict[$type];
  }
}
