<?
/**
 * 组件对象配置信息
 */


//mysql数据库访问组件(原生函数访问方式)
$configs[] = array(
'id'         => 'mysql',
'enable'     => true,
'source'     => Lib.'/Db/SpringMysql.php',
'className'  => 'SpringMysql',
'import'     => array(Lib.'/Db/IDataSource.php'),
'initMethod' => 'load',
'property'   => array(
	'routeFile' => Config.'/table.route.config.php',
));

//mysql数据库访问组件(pdo访问方式)
$configs[] = array(
'id'         => 'pdo',
'enable'     => true,
'source'     => Lib.'/Db/SpringDbPdo.php',
'className'  => 'SpringDbPdo',
'import'     => array(Lib.'/Db/IDataSource.php'),
'initMethod' => 'load',
'property'   => array(
	'dbType'	=> 'mysql',
	'routeFile' => Config.'/table.route.config.php',
));


//mongo数据库访问组件
$configs[] = array(
'id'         => 'mongo',
'enable'     => true,
'source'     => Lib.'/Db/SpringMongo.php',
'className'  => 'SpringMongo',
'property'   => array(
	'configFile'  => Config.'/mongo.config.php',
));


//Memcache数据缓存
$configs[] = array(
'id'        => 'mem',
'enable'    => true,
'source'    => Lib.'/Util/Cache/MmCache.php',
'className' => 'MmCache',
'import'    => array(Lib.'/Util/Cache/ICache.php'),
'property'  => array(
    'expire'     => 1800,
	'compressed' => false,
	'configFile' => Config.'/memcache.config.php'
));


//内存数据库(Redis)
$configs[] = array(
'id'        => 'redisDb',
'enable'    => true,
'source'    => Lib.'/Util/Cache/RedisDb.php',
'className' => 'RedisDb',
'import'    => array(Lib.'/Util/Cache/ICache.php'),
'property'  => array(
    'name'     => 1,
	'compressed' => false,
	'configFile' => Config.'/redis.mdb.config.php'
));

//内存数据库(TTServer)
$configs[] = array(
'id'        => 'tt',
'enable'    => true,
'source'    => Lib.'/Util/Cache/MmCache.php',
'className' => 'MmCache',
'import'    => array(Lib.'/Util/Cache/ICache.php'),
'property'  => array(
	'compressed' => false,
	'configFile' => Config.'/tt.mdb.config.php'
));


//消息队列访问组件(Redis)
$configs[] = array(
'id'        => 'queue',
'enable'    => true,
'source'    => Lib.'/Util/Queue/RedisQ.php',
'className' => 'RedisQ',
'import'    => array(Lib.'/Util/Queue/IQueue.php'),
'property'  => array(
	'configFile' => Config.'/redis.queue.config.php'
));


//消息队列访问组件(memcacheq)
$configs[] = array(
'id'        => 'memcacheq',
'enable'    => true,
'source'    => Lib.'/Util/Queue/MemcacheQ.php',
'className' => 'MemcacheQ',
'import'    => array(Lib.'/Util/Queue/IQueue.php'),
'property'  => array(
	'configFile' => Config.'/memcacheq.queue.config.php'
));

//全文索引(sphinx)
$configs[] = array(
'id'        => 'q',
'enable'    => true,
'source'    => Lib.'/Util/Tool/sphinxapi.php',
'className' => 'SphinxClient',
'property'  => array(
	'_host'  => '127.0.0.1',
	'_port'  => 9312,
));


//数据分页
$configs[] = array(
'id'        => 'pager',
'enable'    => true,
'source'    => file_exists(App.'/Util/Page.php') ? App.'/Util/Page.php' : Lib.'/Util/Tool/Page.php',
'className' => 'Page',
'property'   => array(
	'point' => 10,
	'style' => 'on',
));


//文件上传
$configs[] = array(
'id'         => 'upload',
'enable'     => true,
'source'     => Lib.'/Util/Tool/UploadFile.php',
'className'  => 'UploadFile',
'property'   => array(
	'maxSize' => 1073741824,
	'upType'  => 'docx|rar|zip|txt|xls|xlsx|jpg|gif|png',
));


//参数验证
$configs[] = array(
'id'        => 'para',
'enable'    => true,
'source'    => Lib.'/Util/Tool/Parameter.php',
'className' => 'Parameter'
);


//图片验证码
$configs[] = array(
'id'        => 'vi',
'enable'    => true,
'source'    => Lib.'/Util/Tool/VerifyImg.php',
'className' => 'VerifyImg'
);


//数组分页
$configs[] = array(
'id'        => 'dp',
'enable'    => true,
'source'    => Lib.'/Util/Tool/DataPage.php',
'className' => 'DataPage',
'property'   => array(
	'objRef'      => array('pager'=>'pager'),
));


//钩子组件配置信息(可实现拦截操作:如控制登录)
$configs[] = array(
'id'         => 'appHook',
'enable'     => true,
'ignore'     => true,
'source'     => App.'/Hook/app.hook.php',
'className'  => 'Hook',
'property'   => array(
	'objRef' => array('com'=>'com')
));


//数据库日志记录
$configs[] = array(
'id'         => 'dbLog',
'enable'     => true,
'ignore'     => true,
'source'     => App.'/Hook/DbLog.php',
'className'  => 'DbLog',
'property'   => array(
	'objRef' => array('com'=>'com')
));


//代码生成器
$configs[] = array(
'id'        => 'am',
'enable'    => true,
'source'    => Lib.'/Util/Tool/AutoMake.php',
'className' => 'AutoMake',
'property'  => array(
	'objRef' => array('dbObj'=> 'mysql')
));
?>