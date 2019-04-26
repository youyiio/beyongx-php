BeyongX 内容管理系统(简称BeyongX Cms)
===============

BeyongX Cms系统基于ThinkPHP5.1框架的轻量级内容管理系统，适用于企业Cms, 个人站长等，针对移动App、小程序优化；提供完善简洁的项目文档，方便开发人员进行二次开发。
支持模块式开发，方便平台扩展及第三方进行二次开发。专注于个人站长、中小企业客户，提供基础平台功能及丰富的应用扩展，
支持PC和移动场景，满足企业建站系统、后台管理框架、App后台开发、微信小程序开发、小程序开发框架、小程序API、小程序开发等实际二次开发场景。
其主要特性包括：

 + 基于ThinkPHP5.1框架
 + 用户系统
 + 文章系统
 + 灵活的角色权限控制
 + Composer第三方库支持
 + 插件系统
 + 主题系统
 + 针对App及小程序的api优化
 + 阿里云和七牛云OSS支持

支持官网: http://www.beyongx.com

> ThinkPHP5.1的运行环境要求PHP5.6以上，建议使用PHP7.0及以上。


## 目录结构

系统的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─module_name        模块目录
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  ├─view            视图目录
│  │  └─ ...            更多类库目录
│  │
│  ├─command.php        命令行定义文件
│  ├─common.php         公共函数文件
│  └─tags.php           应用行为扩展定义文件
│
├─config                应用配置目录
│  ├─module_name        模块配置目录
│  │  ├─database.php    数据库配置
│  │  ├─cache           缓存配置
│  │  └─ ...            
│  │
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─log.php            日志配置
│  ├─session.php        Session配置
│  ├─template.php       模板引擎配置
│  └─trace.php          Trace配置
│
├─route                 路由定义目录
│  ├─route.php          路由定义
│  └─...                更多
│
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写

~~~

> router.php用于php自带webserver支持，可用于快速测试
> 切换到public目录后，启动命令：php -S localhost:8888  router.php
> 上面的目录结构和名称是可以改变的，这取决于你的入口文件和配置参数。

## 版本发布

#### V1.0.1
新特性及更新列表：

* 网站Seo的sitemap.xml自动生成支持，php模块要求xmlwriter
* URL路由更新
* think\Config   => think\facade\Config （或者 Config ）
* think\Cookie   => think\facade\Cookie （或者 Cookie ）
* think\Debug    => think\facade\Debug （或者 Debug ）

原有的配置文件config.php 拆分为app.php cache.php 等独立配置文件 放入config目录。
原有的路由定义文件route.php 移动到route目录

#### V1.0.0
新特性及更新列表：

* 角色权限更新，扩展auth_rule表，去除auth_menu_nav
* 测试


## 命名规范

`ThinkPHP5`遵循PSR-2命名规范和PSR-4自动加载规范，并且注意如下规范：

### 目录和文件

*   目录不强制规范，驼峰和小写+下划线模式均支持；
*   类库、函数文件统一以`.php`为后缀；
*   类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
*   类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；

### 函数和类、属性命名
*   类的命名采用驼峰法，并且首字母大写，例如 `User`、`UserType`，默认不需要添加后缀，例如`UserController`应该直接命名为`User`；
*   函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 `get_client_ip`；
*   方法的命名使用驼峰法，并且首字母小写，例如 `getUserName`；
*   属性的命名使用驼峰法，并且首字母小写，例如 `tableName`、`instance`；
*   以双下划线“__”打头的函数或方法作为魔法方法，例如 `__call` 和 `__autoload`；

