# deploy

#### 介绍
自动化部署

#### 软件架构
脚本语言有PHP编写，利用Gitee webhook实现项目自动化部署发布


#### 安装教程

1.  将本项目部署到发布服务器，并保证可以正常被webhook访问；
2.  项目源码 push 到gitee仓库，在服务器上创建ssh访问公钥，保证可以免密访问；
    > <font style="color:#F56C6C">ssh秘钥钥文件一般存储在 `/home/www/.ssh` 下, 可以通过`finger www` 确定`www`用户目录</font> 

![img_1.png](img_1.png)

3.  进入gitee 项目 - 管理 - WebHooks 配置触发事件通知Hook, `WebHook 密码/签名密钥` 选择 `签名秘钥`，值需要和部署脚本 `config/Config.php` 下对应项目名称 package（名称不可以包含特殊`.`、`$` 等特殊符号，否则项目可能无法正常解析部署）的 `SECRET` 保持一致; URL: `http://您的域名/deploy.php?package=项目名称`
    
![img_2.png](img_2.png)

4.  项目配置 `config/Config.php`
    
![img.png](img.png)

5.  <font style="color:#F56C6C">重要：保证项目下所有文件和目录包括隐藏文件`.git`目录的所有者为`www`</font>
6.  项目部署脚本需要放到根目录下（即WEB_PATH），文件格式为 yaml，文件名必须为 `deploy-ci.yml`，具体可参考项目内文件。
7.  增加命令行管理工具：
    `php bin/grace project list [all]` 查看配置
    `php bin/grace project create` 创建配置
    `php bin/grace project modify project_name` 删除配置
    `php bin/grace project remove project_name` 修改配置
    
![img_3.png](img_3.png)
#### 使用说明

1.  推送测试发布结果；

#### 参与贡献

1.  zrone
