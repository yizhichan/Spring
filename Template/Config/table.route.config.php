<?
//表前缀
$prefix		= '';

//数据库编号
$dbIds		= array(
	'1' => 'demo',		//数据库1
	'2' => 'demo2',		//数据库2
	'3' => 'demo3',		//数据库3(数据库个数无限)
);


/**
 * 主从配置说明
 *
 * $configFile['1']为数据库1配置文件数组
 * $configFile['1'][0]主服务器(写), $configFile['1'][1]从服务器(读)
 * 无主从数据库服务器(单一服务器提供服务),去掉$configFile['1'][1]即可
 */
$configFile	= array(
	'1' => array(Config.'/dbconfig/pdo/demo.master.config.php', Config.'/dbconfig/pdo/demo.slave.config.php'),
);


//数据表配置信息(建立数据表与数据库、数据库服务器之间的关联)

$tbl['article'] = array(
	'name'		=> $prefix.'article',
	'dbId'		=> $dbIds['1'], 
	'configFile'=> $configFile['1'],
);

$tbl['category'] = array(
	'name'		=> $prefix.'category',
	'dbId'		=> $dbIds['1'], 
	'configFile'=> $configFile['1'],
);
?>