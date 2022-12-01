# moon-shop

#### Laravel 重构企业级电商项目
基于 [litemall](https://github.com/linlinjava/litemall) 开源项目，使用 Laravel 重构后端 API

#### 技术架构
Laravel 8 后端 + Vue 用户移动端

- Laravel 8.x
- Vue
- Redis
- MySQL

相关扩展库：

- [image](https://github.com/Intervention/image)
- [simple-qrcode](https://github.com/SimpleSoftwareIO/simple-qrcode)

- [jwt-auth](https://github.com/tymondesigns/jwt-auth)
- [easy-sms](https://github.com/overtrue/easy-sms)
- [pay](https://github.com/yansongda/pay)
- [laravel-query-logger](https://github.com/overtrue/laravel-query-logger)

#### 商城功能

- 专题列表、专题详情
- 分类列表、分类详情
- 品牌列表、品牌详情
- 新品首发、人气推荐
- 优惠券列表、优惠券选择
- 商品详情、商品评价、商品分享
- 购物车
- 下单
- 订单列表、订单详情、订单售后


#### 安装教程

1. 开发环境

   - [PHP](https://www.php.net/releases/) ^7.3|^8.0
   - [Laravel](https://learnku.com/docs/laravel/8.x) 8.83.25
   - [MySQL](https://dev.mysql.com/downloads/mysql/) ^5.7
   - [Redis](https://redis.io/download/)
   - [Nodejs](https://nodejs.org/en/download/)

2. 数据库依次导入 sql 下的数据库文件

   - litemall_schema.sql
   - litemall_table.sql
   - litemall_data.sql

3. Laravel 项目安装、配置

   - composer 依赖安装

     ```shell
     # 配置阿里云 composer 全量镜像
     composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
     
     # 安装依赖
     cd moon-shop
     composer install -vvv # -vvv 查看镜像源
     ```

   - `.env` 配置

     ```shell 
     cp .env.example .env
     ```

     ```bash
     # 项目配置
     APP_NAME=moon-shop
     APP_URL=http://laravel.test
     
     # 数据库配置
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=litemall
     DB_USERNAME=litemall
     DB_PASSWORD=litemall123456
     DB_PREFIX=litemall_
     
     # Redis 配置
     CACHE_DRIVER=redis
     QUEUE_CONNECTION=redis
     REDIS_HOST=127.0.0.1
     REDIS_PASSWORD=xxx
     REDIS_PORT=6379
     
     # Mail
     MAIL_MAILER=smtp
     MAIL_HOST=smtp.163.com
     MAIL_PORT=465
     MAIL_USERNAME=xxx
     MAIL_PASSWORD=xxx
     MAIL_ENCRYPTION=ssl
     MAIL_FROM_ADDRESS=xxx
     MAIL_FROM_NAME="${APP_NAME}"
     
     # 新订单通知邮箱
     NOTIFY_EMAIL_USERNAME=xxx@163.com
     
     # JWT
     JWT_TTL=120
     JWT_SECRET=X-Litemall-Token
     JWT_ISSUER=LITEMALL
     
     # EasySMS
     EASYSMS_ALIYUN_ACCESS_KEY_ID=xxx
     EASYSMS_ALIYUN_ACCESS_KEY_SECRET=xxx
     EASYSMS_ALIYUN_SIGN_NAME=测试
     
     # Express
     EXPRESS_APP_ID=xxx
     EXPRESS_APP_KEY=xxx
     EXPRESS_APP_URL=https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx
     
     # alipay 支付配置
     ALI_APP_ID=
     ALI_PUBLIC_KEY=
     ALI_PRIVATE_KEY=
     
     # wechat 支付配置
     WECHAT_APP_ID=
     WECHAT_MINIAPP_ID=
     WECHAT_APPID=
     WECHAT_MCH_ID=
     WECHAT_KEY=
     
     # SQL日志监听
     LISTENING_SQL_LOG=false
     
     # H5前端地址
     H5_URL=http://localhost:8080
     ```

4. H5 前端启动

   ```shell
   cd h5
   npm run dev
   ```

#### 订单状态

![订单状态机](README.assets/%E8%AE%A2%E5%8D%95%E7%8A%B6%E6%80%81%E6%9C%BA.png)

#### Redis 队列

本项目使用了 Laravel 队列的延迟队列、任务调度处理订单、支付消息通知等相关问题，**推荐采用 Redis 作为队列驱动程序**

1. 运行队列处理器

   ```shell
   php artisan queue:work redis --daemon
   ```

2. 启动调度器

   ```shell
   # 指定用户添加 Crontab 定时任务
   crontab -u www -e
   
   # 该 Cron 会每分钟调用一次 Laravel 的命令行调度器
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

Laravel 队列使用以及更多详情内容请参考 [Laravel 8 中文文档](https://learnku.com/docs/laravel/8.x/queues)

#### 项目说明

1. 本项目仅用于学习、练习、参考
2. 本项目处于不断完善中开发中，不承担任何使用后果
3. 开发者有问题或者好的建议可以使用 Issues 反馈交流

