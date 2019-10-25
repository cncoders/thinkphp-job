<?php
namespace cncoders\job;

trait JobTrait
{
    /**
     * 添加一个任务到队列
     *
     * @param $jobName
     * @param $jobClassName
     * @param array $param
     */
    protected function addJob($jobName, $jobClassName, $param = [])
    {
        return (new Job($jobName, $jobClassName, $param))
            ->add();
    }
}