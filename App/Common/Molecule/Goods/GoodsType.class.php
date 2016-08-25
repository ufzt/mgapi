<?php
namespace Common\Molecule\Goods;

class GoodsType{

  # 商品分类
  const ZHI_NENG_CHU_XING       = '11';               # 智能出行
  const ZHI_NENG_CHUAN_DAI      = '12';               # 智能穿戴
  const ZHI_NENG_JIA_JU         = '13';               # 智能家居
  const ZHI_NENG_JIAN_KANG      = '14';               # 智能健康
  const CHUANG_YI_SHE_JI        = '21';               # 创意设计
  const CHUANG_KE_DIY           = '22';               # 创客DIY
  const SHU_MA_PEI_JIAN         = '31';               # 数码配件
  const WU_REN_JI               = '32';               # 无人机
  const JI_QI_REN               = '33';               # 机器人
  const XU_NI_XIAN_SHI          = '34';               # 虚拟现实

  static function type_name_dict(){
    return array(self::ZHI_NENG_CHU_XING     => '智能出行',
                 self::ZHI_NENG_CHUAN_DAI    => '智能穿戴',
                 self::ZHI_NENG_JIA_JU       => '智能家居',
                 self::ZHI_NENG_JIAN_KANG    => '智能健康',
                 self::CHUANG_YI_SHE_JI      => '创意设计',
                 self::CHUANG_KE_DIY         => '创客DIY',
                 self::SHU_MA_PEI_JIAN       => '数码配件',
                 self::WU_REN_JI             => '无人机',
                 self::JI_QI_REN             => '机器人',
                 self::XU_NI_XIAN_SHI        => '虚拟现实', );
  }

  static function type_name($type){
    $dict = self::type_name_dict();
    if (!isset($dict[$type]))
      trigger_error($type.' is NOT a Goods Type!', E_USER_ERROR);
    return $dict[$type];
  }

  const IMAGE_PRE = 'http://app-10049250.file.myqcloud.com/static/goods_type/';
  static function type_image_dict(){
    return array(self::ZHI_NENG_CHU_XING  => self::IMAGE_PRE. self::ZHI_NENG_CHU_XING  .'.png',
                 self::ZHI_NENG_CHUAN_DAI => self::IMAGE_PRE. self::ZHI_NENG_CHUAN_DAI .'.png',
                 self::ZHI_NENG_JIA_JU    => self::IMAGE_PRE. self::ZHI_NENG_JIA_JU    .'.png',
                 self::ZHI_NENG_JIAN_KANG => self::IMAGE_PRE. self::ZHI_NENG_JIAN_KANG .'.png',
                 self::CHUANG_YI_SHE_JI   => self::IMAGE_PRE. self::CHUANG_YI_SHE_JI   .'.png',
                 self::CHUANG_KE_DIY      => self::IMAGE_PRE. self::CHUANG_KE_DIY      .'.png',
                 self::SHU_MA_PEI_JIAN    => self::IMAGE_PRE. self::SHU_MA_PEI_JIAN    .'.png',
                 self::WU_REN_JI          => self::IMAGE_PRE. self::WU_REN_JI          .'.png',
                 self::JI_QI_REN          => self::IMAGE_PRE. self::JI_QI_REN          .'.png',
                 self::XU_NI_XIAN_SHI     => self::IMAGE_PRE. self::XU_NI_XIAN_SHI     .'.png', );
  }
}
