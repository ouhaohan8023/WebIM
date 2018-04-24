# WebIM
基于Swoole-WebSocket的多人在线聊天

本机运行环境如下：
* PHP 7.1.7
* Redis 3.1.0
* Swoole 2.1.3
* 集成环境为LNMP1.4.1
* 支持WebSocket的浏览器

使用方法
* 确保PHP拓展Redis，Swoole在php.ini中全部开启
* git clone 项目到本地
* 进入项目根目录`WebIm`，终端运行`php Socket.php`，保持窗口打开，不要关闭
* 打开谷歌浏览器，找到项目，例如：
`http://localhost/WebIm/Socket.html`
* 当项目第二行出现`hello,welcome`，即表示链接成功；如果没有出现，表示链接服务器失败
* 同理，可以查看运行中的终端