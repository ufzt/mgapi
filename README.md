## 接口简介

`mgapi接口服务`基于ThinkPHP 3.2.3 { Fast & Simple OOP PHP Framework }开发

ThinkPHP遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。

## 可伸缩的架构设计特性

结合最新的ThinkPHP、及其为WEB开发提供的强有力支持，`mgapi接口服务`在设计时采用了

*  多层模型特性-基于（Behavior、Atom、Molecule）的设计模式，严格的DRY原则，Behavior的mixin特性等等
*  命名空间支持-采用了命名空间，更灵活地支持面向对象设计
*  RESTFul特性-接口实现了POST/GET(jsonp)的自适应
*  Mock测试支持-提供了接口模拟测试方法和数据
*  可伸缩特性-接口开放基于service配置，可根据实际需求修改配置文件，开放部分接口
*  安全性特性-接口请求基于私钥签名，防SQL注入，输入数据过滤等等

## 初始化项目

请使用如下命令完成项目的初始化

*  cp index.php.sample index.php
*  cp App/Common/Conf/db.php.ut_test    App/Common/Conf/db.php
*  cp App/Common/Conf/config.php.sample App/Common/Conf/config.php
*  cp ThinkPHP/Library/Vendor/cos-php-sdk/Qcloud_cos/Conf.php.ut_test ThinkPHP/Library/Vendor/cos-php-sdk/Qcloud_cos/Conf.php
*  sudo chmod -R 777 Runtime/
