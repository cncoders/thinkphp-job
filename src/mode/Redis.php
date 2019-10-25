<?php
namespace cncoders\job\mode;

use cncoders\job\Dispatcher;
use cncoders\job\JobModeAbstruct;

class Redis extends JobModeAbstruct
{
    protected $redis = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->redis = new \Redis();
        $this->redis->connect($config['redis']['host'], $config['redis']['port'],$config['redis']['timeout']);
        if (!empty($config['redis']['auth'])) {
            $this->redis->auth($config['redis']['auth']);
        }
    }

    /**
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     * @return mixed|void
     */
    public function add($jobName, $jobClassName, $jobPar = [])
    {
        return $this->redis->rPush(
            $jobName,
            $this->toJson($jobClassName, $jobPar)
        );
    }

    /**
     * @param $jobName
     * @param int $size
     * @return bool|mixed
     */
    public function execute($jobName, $size = 1)
    {
        for($j = 0; $j < $size; $j++) {
            $jobData = $this->redis->lPop($jobName);
            $this->_execJob($jobName, $jobData, $j);
        }
        return true;
    }

    /**
     * @param $jobName
     * @param $jobData
     * @param int $key
     * @return mixed|null
     */
    protected function _execJob($jobName, $jobData, $key = 0)
    {
        $job = json_decode($jobData, true);
        if ( false === $this->hasLock(
                $jobName,
                $job['jobClassName'],
                $job['jobPar'],
                'exeJob_'.$key.'_')
        ) {

            return (new Dispatcher($job['jobClassName'], []))
                ->_call('exec', $job['jobPar']);
        }
    }

    public function size($jobName)
    {
        return $this->redis->lLen($jobName);
    }
}