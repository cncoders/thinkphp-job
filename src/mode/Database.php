<?php
namespace cncoders\job\mode;

use cncoders\job\Dispatcher;
use cncoders\job\JobModeAbstruct;
use think\facade\Db;

/**
 * 先在相应的数据库里面建立该表
 *
 * CREATE TABLE `think_job` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`job_name` char(100) DEFAULT '' COMMENT '队列名称',
`job_data` text COMMENT '队列数据',
`job_status` tinyint(1) unsigned DEFAULT '0' COMMENT '队列状态 0=未执行 1=执行成功 2=执行失败',
`create_time` datetime DEFAULT NULL,
`update_time` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `index_job_name` (`job_name`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 * Class Database
 * @package job\mode
 */
class Database extends JobModeAbstruct
{
    protected $database = '';
    protected $table = 'think_job';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->database = $config['database']['config_name'];
    }

    public function add($jobName, $jobClassName, $jobPar = [])
    {
        if (true === $this->hasLock($jobName, $jobClassName, $jobPar, 'addJob_')) {
            return true;
        }

        return Db::connect($this->database)
            ->table($this->table)
            ->insertGetId([
                'job_name' => $jobName,
                'job_data' => $this->toJson($jobClassName, $jobPar),
                'job_status' => 0,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ]);
    }

    public function execute($jobName, $size = 1)
    {
        $jobData = Db::connect($this->database)
            ->table($this->table)
            ->where('job_name', $jobName)
            ->where('job_status',0)
            ->limit($size)
            ->order('id DESC')
            ->select();

        foreach($jobData as $job) {
            $execRes = $this->_execJob($jobName, $job['job_data'], $job['id']);

            if ($execRes === true) {
                Db::connect($this->database)
                    ->table($this->table)
                    ->where('id', $job['id'])
                    ->update([
                        'job_status' => 1,
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
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
            (new Dispatcher($job['jobClassName'], []))->_call('exec', $job['jobPar']);
            return true;
        }
    }

    public function size($jobName)
    {
        return Db::connect($this->database)
            ->table($this->table)
            ->where('job_name', $jobName)
            ->where('job_status',0)
            ->count();
    }
}