<?php

// service 配置
return array(

  # 发出邀请邮件调用接口
  'create_invite'                  =>   'InviteCreateBehavior',
  # 邮件链接 打开页面调用接口
  'visit_invite'                   =>   'InviteVisitBehavior',
  # 发送短信验证码
  'send_invite_sms'                =>   'InviteSendSMSBehavior',
  # 点击下一步
  'submit_invite_step1'            =>   'InviteSubmitStepOneBehavior',
  'submit_invite_step2'            =>   'InviteSubmitStepTwoBehavior',
  'submit_invite_step3'            =>   'InviteSubmitStepThreeBehavior',
);
