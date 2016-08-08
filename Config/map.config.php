<?
/**
 * 组件配置信息:级联配置文件
 * 构造组件对象时,按照配置文件排列的先后顺序进行遍历
 */

//框架核心组件配置文件
$source[]['source'] = LibDir.'/Config/core.config.php';

//扩展组件配置文件
$source[]['source'] = file_exists(ConfigDir.'/Extension/extension.config.php')
                      ? ConfigDir.'/Extension/extension.config.php'
					  : LibDir.'/Config/extension.config.php';


//常用组件配置文件
$source[]['source'] = LibDir.'/Config/component.config.php';
?>