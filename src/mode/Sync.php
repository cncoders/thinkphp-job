<?php
namespace cncoders\job\mode;

use cncoders\job\Dispatcher;
use cncoders\job\JobModeAbstruct;

/**
 * 同步方式调度任务
 *
 * Class Sync
 * @package job\mode
 */
class Sync extends JobModeAbstruct
{
    /**
     * 添加到队列 同步方式则同步执行
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     */
    public function add($jobName, $jobClassName, $jobPar = [])
    {
        if (true === $this->hasLock($jobName, $jobClassName, $jobPar)) {
            return true;
        }

        return (new Dispatcher($jobClassName, []))
            ->_call('exec', $jobPar);
    }

    /**
     * Sync为同步执行 不需要异步执行
     *
     * @param $jobName
     * @param int $size
     * @return bool|mixed
     */
    public function execute($jobName, $size = 1)
    {
        return true;
    }

    /**
     * 同步执行不需要处理
     *
     * @param $jobName
     * @return int|mixed
     */
    public function size($jobName)
    {
        return 0;
    }
}