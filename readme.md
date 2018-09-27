#dmmlogin

dmmlogin是一个网页游戏《艦隊これくしょん -艦これ-》的免代理登录系统。

只要你把该服务架设在日本服务器上，即可不用代理软件登录到《艦隊これくしょん -艦これ-》。

本项目基于PHP 5.4。

代码中使用到的开源项目许可证包含在LICENSES文件夹中。

为了您的账号安全请使用SSL连接到服务器并且不要随意使用自己的账号登录到他人的服务器。

登录流程来自[kancolle-broker](https://github.com/phoenixlzx/kancolle-broker)

swf获取流程来自[ooi3](https://github.com/acgx/ooi3)

###使用代理

复制根目录的```proxy.sample.php```并重命名为```proxy.php```，修改文件内定义的ip和端口即可。

###通过docker运行

在项目根目录执行。由于是运行在docker容器内，所以proxy会无法代理到本地的代理。
```
docker build -t local/dmmlogin .
docker run -d -p <PORT>:80 --name dmmlogin local/dmmlogin
```
PORT为外网端口。

###预览图

![preview](https://raw.githubusercontent.com/slime7/dmmlogin/master/asset/img/preview.png)
