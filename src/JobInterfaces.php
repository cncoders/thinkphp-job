<?php
namespace cncoders\job;

interface JobInterfaces
{
    /**
     * 执行任务
     * @param array $param
     * @return mixed
     */
    public function exec( $param = []);
}