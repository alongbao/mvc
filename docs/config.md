# 项目配置文件说明 #

----------
项目的各项配置保存在$config变量内
#### 各项配置的说明如下 ####

- errHandler 项目发生错误时，调用的错误处理类的类名，此类必须实现`liuguang\mvc\ErrHandler`接口
- urlHandler 项目负责解析URL和生成URL的类，，此类必须实现`liuguang\mvc\UrlHandler`接口
- dblist 数据库连接列表，每个数组成员的结构为pdo构造方法的参数
		array(
			'dsn'=>'',
			'username'=>null,
			'password'=>null,
			'options'=>null
			);
- fslist 文件存储配置列表，每个数组成员的结构如下
		array(
			'type'=>'Local',//文件存储驱动名(liuguang\lib\fs+驱动名+Driver=驱动类名),
			'config'=>array(...)//根据不同的驱动提供不同的参数
			);
- extClass 第三方类库映射数组 类名 => 文件相对于基础类库基础路径的路径名,以**/**作为路径分隔符
- controllerNs 项目控制器的命名空间
- cKey 控制器名在URL中的键名
- aKey 操作名在URL中的键名
- defaultC 没有指定控制器名时，默认的控制器名
- defaultA 没有指定操作名时，默认的操作名
- 404C 当指定的控制器名不存在时，调用的控制器名