<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 模型、控制器基类(MVC核心)
 +------------------------------------------------------------------------------
 * @mobile	13183857698
 * @qq		78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
abstract class ModelActionBase extends Object
{
	/**
	 * sql调试开关
	 */
	protected $debug  = false;

	/**
	 * 模型对象缓存
	 */
	private	  static $object = array();

	/**
	 * 加载model对象
	 *
	 * @access	protected
	 * @param	string	$objId	对象标识
	 * @param	int		$rule	构造规则[1引用关系构造、2模型名构造]
	 * @return	model
	 */
	protected function import($objId = null, $rule = 1)
	{
		//通过指定引用关系构造模型对象(推荐)
		if ( $rule == 1 && $objId && isset($this->models[$objId]) && $this->models[$objId] )
		{
			if ( isset(self::$object[$objId]) ) {
				return self::$object[$objId];
			}

			$mf                   = $this->com('mf');
			$mf->models['model']  = $this->models;
			$mf->suffix           = 'model';
			$model                = $mf->getObject($objId);
			self::$object[$objId] = $model;
			
			if ( method_exists($model, 'init') )
			{
				$model->init();
			}

			return $model;
		}

		//直接指定模型名构造模型对象(模型名不带后缀model,不推荐这种方式)
		if ( $rule == 2 && $objId )
		{
			$mf                  = $this->com('mf');
			$mf->models['model'] = array($objId => $objId);
			$mf->suffix          = 'model';
			$model               = $mf->getObject($objId);
			
			if ( method_exists($model, 'init') )
			{
				$model->init();
			}

			return $model;
		}

		return null;
	}
	
	/**
	 * 加载业务组件(Module)对象
	 *
	 * @access	protected
	 * @param	string	$name	业务组件类名(不带后缀module)
	 * @return	Module
	 */
	protected function load($name)
	{
		$name   = strtolower($name);
		$module = $name.'Module';
		static $modules = array();
		if ( isset($modules[$name]) )
		{
			return $modules[$name];
		}

		
		$destFile   = $name.'.module.php';
		$resultFile = FileSearcher::search(ModuleDir, $destFile);
		if ( !$resultFile )
		{
			throw new SpringException("找不到文件{$destFile}");
		}
		
		require_once($resultFile);
		if ( !class_exists($module) ) 
		{
			throw new SpringException("文件{$resultFile}中找不到类 $module");
		}

		$modules[$name] = new $module();
		
		return $modules[$name];
	}
}
?>