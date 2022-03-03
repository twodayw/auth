
# 基于Phalapi2的Auth扩展

Gitee镜像仓库：[https://gitee.com/jiangslee/phalapi-auth](https://gitee.com/jiangslee/phalapi-auth "Gitee镜像地址")

## 描述

基于Phalapi2的Auth扩展,从phalapi1移植

附上:

原项目GitHub地址:[https://github.com/twodayw/auth.git](https://github.com/twodayw/auth.git "原项目Git地址")

# 食用方法

> 前提条件，已安装好user组件

1. 修改composer.json

> 此步骤主要是修复了auth组件的namespace为PhalApi，如果官方源已修复namespace则可省略此步骤

参考文档：https://learnku.com/articles/64219

给composer添加自定义git源
```
	"repositories": [
		{
			"type": "composer",
			"url": "https://mirrors.aliyun.com/composer",
			"exclude": ["phalapi/auth"]
		},
		{
			"type": "git",
			"url": "https://github.com/jiangslee/auth.git"
		}
	],

    // 只复制上面部分，以下不要复制
	"require" : {
        ...
    }
```

2. 安装auth组件

```
composer require phalapi/auth:dev-master
```

3. 导入auth相关的数据表，sql文件在源码 data 目录

4. 导入测试用的权限数据
```
-- 添加权限组
INSERT INTO `phalapi`.`phalapi_auth_group` (`id`, `title`, `status`, `rules`)
 VALUES (NULL, '超级管理员', '1', '');

-- 用户1与权限组1关联
INSERT INTO `phalapi`.`phalapi_auth_group_access` (`uid`, `group_id`) VALUES ('1', '1');

-- 添加测试用的权限规则
INSERT INTO `phalapi`.`phalapi_auth_rule` (`id`, `name`, `title`, `status`, `add_condition`) VALUES 
(NULL, 'Default.Index', '默认接口', '1', ''),
(NULL, 'App.User_User.Profile', '用户-个人信息', '1', ''),
(NULL, 'App.User_User.CheckSession', '用户-检测是否登录', '1', '');

-- 权限组添加规则
UPDATE `phalapi`.`phalapi_auth_group` SET `rules` = '1,2,3' WHERE `phalapi_auth_group`.`id` = 1;
```

5. config/app.php添加相应配置

```
    // 接口服务白名单，格式：接口服务类名.接口服务方法名
    'service_whitelist' => array(
        'Site.Index',
        'App.User_User.Login', // 添加上
        'App.User_User.Register', // 添加上
    ),
    //请将以下配置拷贝到 ./Config/app.php 文件对应的位置中
    'auth' => array(
        'auth_on' => true, // 认证开关
        'auth_user' => 'phalapi_user', // 用户信息表,
        'auth_group' => 'phalapi_auth_group', // 组数据表名
        'auth_group_access' => 'phalapi_auth_group_access', // 用户-组关系表
        'auth_rule' => 'phalapi_auth_rule', // 权限规则表
        //跳过权限检测的用户id，如1，则用户id为1的用户免权限验证
        'auth_not_check_user' => [
            // 1,
            // 2,
        ], 
    )
```

6. 创建AuthFilter.php

文件保存在src\App\Common\AuthFilter.php

```
<?php
namespace App\Common;

use PhalApi\Exception\BadRequestException;
use PhalApi\Filter;

class AuthFilter implements Filter 
{
    public function check()
    {
        $di = \PhalApi\DI();
        $userid = $di->request->get('user_id');
        if(!empty($userid)) {

            $api = $di->request->get('s', $di->request->get('service', 'Site.Index'));
            $auth = $di->authLite->check($api, $userid);
    
            if(!$auth) throw new BadRequestException("auth权限提醒，没有 $api 接口的权限");
        }
    }
}
```


7. 在config/di.php添加相应的配置

```
$di->filter = new App\Common\AuthFilter();

$di->authLite = new \PhalApi\Auth\Lite(true);
```

# 测试步骤
1. App.User_User.Register 注册user01、user02用户，得到user_id=1、user_id=2的用户;
2. App.User_User.Login user01用户登录
3. App.User_User.Profile 用user_id=1访问,正常返回数据
4. App.User_User.Login user02用户登录
3. App.User_User.Profile 用user_id=2访问,报错提示：“非法请求：auth权限提醒，没有 App.User_User.Profile 接口的权限”


# 其它
具体信息可浏览Phalapi的文档：[Auth权限扩展使用文档](https://gitee.com/dogstar/PhalApi-Library/wikis/Auth-权限扩展使用文档 "Auth权限扩展使用文档")


**如果大家有更好的建议可以私聊或加入到PhalApi大家庭中前来一同维护PhalApi**
**注:笔者能力有限有说的不对的地方希望大家能够指出,也希望多多交流!**
