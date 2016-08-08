<?
/**
 +------------------------------------------------------------------------------
 * Spring框架 控制台应用程序入口
 +------------------------------------------------------------------------------
 * @mobile      13183857698
 * @qq          78252859
 * @author  void <lkf5_303@163.com>
 * @version 3.1.4
 +------------------------------------------------------------------------------
 */
class IncludeApplication extends Application
{
    protected $routerId = 'router';

     /**
     * 处理请求
     *
     * @access      public
     * @return      void
     */
    public function process($mod='include')
    {
        $dispatcher             = Spring::getComponent($this->routerId);
        $dispatcher->rootPath   = Root;
        $dispatcher->errorMod   = ErrorMod;
        $dispatcher->defaultMod = DefaultMod;

        //解析URL参数,获取请求信息
        $dispatcher->parseReq($mod);
        $module = $dispatcher->getModule();
        return $module;
    }

}
?>
