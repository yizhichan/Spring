<?
/**
 * 组件配置信息:运行系统的核心组件
 * 当 'enable'  => true 表示开启该功能，
 * 当 'enable'  => false 表示禁用该功能,
 * 真真实现了针对接口编程的思想,让设计
 * 者在一侧修改而对另一侧不会产生不良影响
 */

//调度器(web)
$configs[] = array(
'id'         => 'dispatcher',
'enable'     => true,
'source'     => LibDir.'/MVC/Core/Dispatcher.php',
'className'  => 'Dispatcher',
'import'     => array(LibDir.'/MVC/Core/IDispatcher.php'),
'property'   => array(
	'configFile'=> ConfigDir.'/Extension/url.route.config.php',  //自定义路由配置文件
	'isRewrite' => true,
	'errorMod'  => ErrorMod,
	'defaultMod'=> DefaultMod,
	'actionPath'=> ActionDir,
	'objRef'	=> array('inputer' => 'inputer'),
));

//调度器(console)
$configs[] = array(
'id'         => 'router',
'enable'     => true,
'source'     => LibDir.'/MVC/Core/Router.php',
'className'  => 'Router',
'import'     => array(LibDir.'/MVC/Core/IDispatcher.php'),
'property'   => array(
	'errorMod'  => ErrorMod,
	'defaultMod'=> DefaultMod,
	'actionPath'=> ActionDir,
));

//模型工厂
$configs[] = array(
'id'         => 'mf',
'enable'     => true,
'source'     => LibDir.'/MVC/Core/ModelFactory.php',
'className'  => 'ModelFactory',
'property'   => array(
    'path'   => ModelDir,
));

//视图输出
$configs[] = array(
'id'         => 'view',
'enable'     => true,
'source'     => LibDir.'/MVC/Core/View.php',
'className'  => 'View',
'property'   => array(
    'tplDir' => ViewDir,
));

//数据接收
$configs[] = array(
'id'         => 'inputer',
'enable'     => true,
'source'     => LibDir.'/MVC/Util/Inputer.php',
'className'  => 'Inputer',
'property'   => array(
	'objRef'	=> array('filter' => 'filter'),
));

?>