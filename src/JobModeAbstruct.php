<?php
namespace cncoders\job;

use think\facade\Cache;

abstract class JobModeAbstruct
{
    /**
     * @var 定义配置项目
     */
    protected $config;

    /**
     * JobModeAbstruct constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 添加任务
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     * @param array $config
     * @return mixed
     */
    abstract public function add($jobName, $jobClassName, $jobPar = []);

    /**
     * 统计队列中的元素的个数
     *
     * @param $jobName
     * @return mixed
     */
    abstract public function size($jobName);

    /**
     * 异步执行这个任务
     *
     * @param $jobName
     * @param int $size
     * @return mixed
     */
    abstract public function execute($jobName, $size = 1);

    /**
     * 防止同一条数据多次加入
     *
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     * @param array $config
     * @return bool
     */
    public function hasLock($jobName, $jobClassName, $jobPar = [], $prefix = '')
    {
        $locKey = $prefix . md5( $jobName . '-'. $jobClassName . '-' . json_encode($jobPar) );
        if (Cache::get($locKey) == 1) {
            return true;
        }
        Cache::set($locKey, 1, 5);
        return false;
    }

    /**
     * 将参数处理成JSON数据
     *
     * @param $jobClassName
     * @param array $jobPar
     * @return false|string
     */
    public function toJson($jobClassName, $jobPar = [])
    {
        $return['jobClassName'] = $jobClassName;
        $return['jobPar'] = $jobPar;
        return json_encode($return);
    }
}