<?php
namespace cncoders\job;

/**
 * 调度处理所有需要处理的任务
 *
 * Class Dispatcher
 * @package job
 */
class Dispatcher
{
    /**
     * @var null 反向代理
     */
    protected $reflectClass = null;

    /**
     * @var array 实例参数
     */
    protected $constructPar = [];

    /**
     * 调度器
     *
     * Dispatcher constructor.
     * @param string $jobClassName
     * @param array $param
     */
    public function __construct($jobClassName = '', $constructPar = [])
    {
        $this->constructPar = $constructPar;
        $this->reflectClass = new \ReflectionClass($jobClassName);
    }

    /**
     * 调度器需要调度的方法
     * @param $method
     * @param array $methodParam
     */
    public function _call($method, $methodParam = [])
    {
        if ( $this->reflectClass->hasMethod($method) ) {
            $methodObject = $this->reflectClass->getMethod($method);
            if ( $methodObject->isPublic() ) {
                return $methodObject->invokeArgs(
                    $this->reflectClass->newInstanceArgs( $this->constructPar ),
                    $methodParam
                );
            }
        }
        return null;
    }
}