<?php
namespace cncoders\job;


use think\facade\Config;

/**
 * 增加任务
 * Class Job
 * @package job
 */
class Job
{
    /**
     * @var string 定义任务的名称
     */
    protected $jobName = '';

    /**
     * @var string 执行任务的文件名
     */
    protected $jobClassName = '';

    /**
     * @var array 任务传递的参数
     */
    protected $jobPar = [];

    /**
     * @var array 加载匹配信息
     */
    protected $config = [];

    /**
     * @var null 调用
     */
    protected $modeObject = null;

    /**
     * Job constructor.
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     */
    public function __construct($jobName, $jobClassName, $jobPar = [])
    {
        $this->jobName      = $jobName;
        $this->jobClassName = $jobClassName;
        $this->jobPar       = $jobPar;
        $this->config       = Config::get('job');
        $this->modeObject   = $this->factory( $this->config['type'] );
    }

    /**
     * 添加一个任务
     * @return mixed
     */
    public function add()
    {
        return $this->modeObject->add(
            $this->jobName,
            $this->fullJobClassName( $this->jobClassName ),
            $this->jobPar,
            $this->config
        );
    }

    /**
     * 简单工厂方式调用驱动类
     * @param string $modeName
     * @return mixed
     */
    public function factory($modeName = 'Sync')
    {
        $classNameSpace = '\\cncoders\\job\\mode\\'.ucfirst( $modeName );
        return new $classNameSpace($this->config);
    }

    /**
     * 返回全路径的任务地址
     * @param $jobClassName
     * @return string
     */
    public function fullJobClassName( $jobClassName )
    {
        return $this->config['namespace'] . $jobClassName;
    }
}