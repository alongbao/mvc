#流光的php项目框架

----------
- 框架的运行环境要求：php>=5.3 ,推荐使用php5.4以上环境，使用数据库则需要开启pdo
- 框架命名规范
    - 方法名,变量名采用驼峰法命名: 如 `getPageNum()`,`getUserData($uid)`
	- 类名采用匈牙利命名法: 如 `UserInfo`,`PclZip`
	- 类文件名为:类名+`.class.php`
- 项目下的命名规范，可以适当放宽，但必须遵循第三条，某则可能会导致无法加载类

## 项目入口文件代码实例 ##
```php
<?php
/*定义项目路径*/
define('APP_PATH',__DIR__);
/*加载框架核心文件*/
include 'mvc/core.php';
/*项目启动*/
\liuguang\mvc\Application::init();
?>
```

## 项目常量定义 ##

- `APP_PATH` 项目路径，必须定义默认是`index.php`文件所在的文件夹绝对路径
- `APP_CONFIG_PATH` 可选常量 ,定义配置文件的绝对路径
- `APP_DEBUG` 可选常用，若定义，则表示当前使用调试模式,可能会影响到某些类的行为

## 项目启动方法 ##

 > 项目的启动方法有三个 分别用于在不同的层次调用

```php
<?php

/**
 * 项目启动器
 *
 * @return void
 */
public static function init();
 
/**
 * 从一个配置文件启动项目
 *
 * @param string $configFile
 *         配置文件路径
 * @return void
 */
public static function initFromFile($configFile);
 
/**
 * 从一个配置对象启动项目
 *
 * @param DataMap $config
 *         配置对象
 * @return void
 */
public static function initFromConfig(DataMap $config)

?>
```

其中`init`方法会调用` initFromFile`方法来启动项目,如果已经定义了`APP_CONFIG_PATH`常量,则传入此常量作为配置文件路径来启动项目.

若未定义此常量,则程序会假设配置文件的路径为:`APP_PATH`下的config.inc.php.

`initFromFile`方法,正如其名称一样,将会加载配置文件,然后将其转化为一个`DataMap`对象,然后再将这个参数传入第三个方法,启动项目.

`initFromConfig`方法,此方法接受一个DataMap类的实例，作为配置启动项目.

项目可以在入口文件中,调用三个方法中的任意一个启动项目

## 默认配置 ##

框架加载配置后,会将配置对象与框架的默认配置进行合并处理，默认的配置项说明如下:

- `app_context` 项目的上下文路径,即url中的path部分除去`/index.php`的部分,如果index.php位于网站根目录,则这个值为空字符串
- `app_entry` 入口文件名称,会自动读取
- `app_pub_context` 项目的`public`文件夹的url，默认值为`app_context`.'/public'
- `app_pub_path` 项目的`public`文件夹的绝对路径,默认为`APP_PATH`下的`public`文件夹的绝对路径
- `mvc_static_path` 用于存放框架静态文件的文件夹绝对路径,默认为框架下的static文件夹的物理路径，一般不需要更改
- `mvc_class_path` 存放框架类文件的基础路径,绝对路径,默认为框架下的class文件夹的物理路径，一般不需要更改
- `mvc_ext_path` 用于存放框架第三方类库的文件夹,绝对路径,默认为框架下的extclass文件夹的物理路径，一般不需要更改
- `app_class_path` 存放项目类文件的基础路径,绝对路径,默认为项目下的class文件夹的物理路径，一般不需要更改
- `app_ext_path` 用于存放项目第三方类库的文件夹,绝对路径,默认为项目下的extclass文件夹的物理路径，一般不需要更改
- `mvc_ext_route` 框架引用的第三方类库的路径映射
- `app_ext_route` 项目引用的第三方类库的路径映射
- `errHandler`	错误处理类，默认是liuguang\mvc\MvcErrHandler
- `urlHandler`	url处理类，默认是liuguang\mvc\MvcUrlHandler
- `dblist`	数据库连接列表
- `fslist`	文件存储配置列表
- `controllerNs`	项目控制器的默认命名空间
- `cKey`	控制器名在url中的键名，默认是c
- `aKey`	操作名在url中的键名，默认是a
- `defaultC`	默认的控制器名,默认是Index
- `defaultA`	默认的操作名，默认是index
- `404C`	404时，调用的控制器名,默认
- `time_zone` 默认时区,默认值为Asia/Chongqing
- `tpl_path` 模板基础路径,默认为`APP_PATH`下的tpl文件夹的绝对路径
- `tpl_compress` 是否开启输出压缩,默认为false

## 类库加载机制 ##
框架和项目使用的自动加载功能，不同的类建议使用不同的命名空间。
`liuguang\mvc`命名空间为框架所有类库的父空间，项目的类库请不要使用此空间以及其子空间，
以免发生命名冲突。
类库的加载路径为：类库基础路径+类名('空间分隔符会被替换为路径分隔符')+".class.php"。
第三方类库路径为:第三方类库基础路径+文件名
尝试加载时，第三方类库已经在配置文件中映射了路径地址的，会被优先尝试加载。
若失败则尝试以类名转换为路径进行加载
优先在框架目录下加载，其次是项目路径。

## 框架定义的第三方类库 ##

	'PHPMailer' => 'class.phpmailer.php',
	'SMTP' => 'class.smtp.php',
	'PclZip' => 'pclzip.lib.php',
	'HTMLPurifierLoader' => 'HTMLPurifier/HTMLPurifierLoader.php'

## 框架静态文件说明 ##

- `scws/dict.utf8.xdb` scws分词的utf-8词库
- `fonts/rcode.ttf` 验证码字体文件

## 开源声明 ##
本框架代码基于MIT协议开放源代码，联系信息67579722@qq.com