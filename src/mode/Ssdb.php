<?php
namespace cncoders\job\mode;

use cncoders\job\Dispatcher;
use cncoders\job\JobModeAbstruct;

class Ssdb extends JobModeAbstruct
{
    /**
     * @var null 记录SSDB
     */
    protected $ssdb = null;

    public function __construct($config = [])
    {
        require_once __DIR__ . '../../SSDB.php';

        parent::__construct($config);

        try {
            $this->ssdb = new \SimpleSSDB($config['ssdb']['host'], $config['ssdb']['port'], $config['ssdb']['timeout']);
            if ( !empty( $config['ssdb']['auth']) ) {
                $this->ssdb->auth($config['ssdb']['auth']);
            }
        } catch (\SSDBException $e) {
            die($e->getMessage());
        }
    }

    /**
     * 添加任务到SSDB
     *
     * @param $jobName
     * @param $jobClassName
     * @param array $jobPar
     * @return mixed|void
     *
     */
    public function add($jobName, $jobClassName, $jobPar = [])
    {
        if (true === $this->hasLock($jobName, $jobClassName, $jobPar, 'addJob_')) {
            return true;
        }

        return $this->ssdb->qpush_front(
            $jobName,
            $this->toJson($jobClassName, $jobPar)
        );
    }

    /**
     * @param $jobName
     * @return mixed
     */
    public function size($jobName)
    {
        return $this->ssdb->qsize($jobName);
    }

    /**
     * 任务名称
     *
     * @param $jobName
     * @param int $size
     * @return mixed|void
     */
    public function execute($jobName, $size = 1)
    {
        $jobData = $this->ssdb->qpop_back($jobName, $size);
        if ( is_string($jobData) ) {
            $this->_execJob($jobName, $jobData, 0);
        }
        if ( is_array($jobData) ) {
            for($i = 0; $i < $size; $i ++) {
                $this->_execJob($jobName, $jobData[$i], $i);
            }
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
}