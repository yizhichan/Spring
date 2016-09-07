<?
//mysql数据库访问组件
$configs[] = array(
'id'         => 'mysql',
'enable'     => true,
'source'     => LibDir.'/Db/SpringMysql.php',
'className'  => 'SpringMysql',
'import'     => array(LibDir.'/Db/IDataSource.php'),
'initMethod' => 'load',
'property'   => array(
	'routeFile' => ConfigDir.'/Table/table.route.config.php',
));

//数据库日志记录
$configs[] = array(
'id'         => 'dbLog',
'enable'     => true,
'ignore'     => true,
'source'     => LibDir.'/Db/DbLog.php',
'className'  => 'DbLog',
);

//数据过滤
$configs[] = array(
'id'         => 'filter',
'enable'     => true,
'source'     => LibDir.'/MVC/Util/DataFilter.php',
'className'  => 'DataFilter',
);

//Solr全文检索组件
$configs[] = array(
'id'        => 'solr',
'enable'    => true,
'source'    => LibDir.'/Util/Tool/SolrSearch.php',
'className' => 'SolrSearch',
'property'  => array(
	'configFile' => ConfigDir.'/Db/solr.master.config.php',
));

//Sphinx全文检索组件
$configs[] = array(
'id'        => 'sphinx',
'enable'    => true,
'source'    => LibDir.'/Util/Tool/SphinxSearch.php',
'className' => 'SphinxSearch',
'property'  => array(
	'objRef' => array('sphinxClient' => 'sphinxClient'),
));

//全文索引客户端
$configs[] = array(
'id'        => 'sphinxClient',
'enable'    => true,
'source'    => LibDir.'/Util/Tool/SphinxClient.php',
'className' => 'SphinxClient',
'property'  => array(
	'_host'  => '127.0.0.1',
	'_port'  => 9312,
	'_arrayresult' => true,
	'_timeout' => 2,
));

//文件上传
$configs[] = array(
'id'         => 'upload',
'enable'     => true,
'source'     => LibDir.'/MVC/Util/UploadFile.php',
'className'  => 'UploadFile',
'property'   => array(
	'maxSize' => 10000000,
	'upType'  => 'doc|docx|rar|zip|txt|xls|xlsx|jpg|gif|png',
));

//数据分页
$configs[] = array(
'id'        => 'pager',
'enable'    => true,
'source'    => LibDir.'/MVC/Util/Page.php',
'className' => 'Page'
);

//数据分页
$configs[] = array(
'id'        => 'pagerNew',
'enable'    => true,
'source'    => LibDir.'/MVC/Util/PageNew.php',
'className' => 'PageNew'
);

//图片验证码
$configs[] = array(
'id'        => 'vi',
'enable'    => true,
'source'    => LibDir.'/MVC/Util/VerifyImg.php',
'className' => 'VerifyImg'
);

//钩子组件配置信息(可实现拦截操作:如控制登录)
$configs[] = array(
'id'         => 'appHook',
'enable'     => true,
'ignore'     => true,
'source'     => AppDir.'/Hook/app.hook.php',
'className'  => 'Hook',
);

//数据编码
$configs[] = array(
'id'         => 'encoding',
'enable'     => true,
'ignore'     => true,
'source'     => LibDir.'/Util/Tool/Encoding.php',
'className'  => 'Encoding',
);

//文件目录
$configs[] = array(
'id'        => 'io',
'enable'    => true,
'source'    => LibDir.'/Util/Tool/IO.php',
'className' => 'IO',
);

?>