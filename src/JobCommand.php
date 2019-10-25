<?php
namespace cncoders\job;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;

/**
 * php think job:boot job_name --size 1 一次弹出一条数据处理
 * php think job::boot job_name --info size 返回队列中待处理的数目
 * Class JobCommand
 * @package job
 */
class JobCommand extends Command
{
    /**
     * 配置定时任务脚本
     */
    protected function configure()
    {
        $this->setName('job:boot')
            ->addArgument('job_name', Argument::OPTIONAL, "任务名称")
            ->addOption('size', null, Option::VALUE_REQUIRED, '同时执行几个任务')
            ->addOption('info', NULL, Option::VALUE_REQUIRED,'获取队列的基本信息')
            ->setDescription('这是一个简单的定时异步执行脚本，用于中小网站处理中小任务使用大型项目请使用成熟消息队列');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int|null
     */
    protected function execute(Input $input, Output $output)
    {
        $jobName   = trim($input->getArgument('job_name'));
        $size       = $input->hasOption('size') ? (int) $input->getOption('size') : 1;
        $info       = $input->hasOption('info');

        $config     = Config::get('job');

        if ( empty($jobName) ) {
            $output->writeln('请填写需要执行的队列!');
            die;
        }

        if (in_array($config['type'], ['ssdb', 'redis','database'])) {
            try {
                $jobMode = ( new Job($jobName, '', []) )->factory($config['type']);

                if ($info == 'size') {
                    $output->writeln($jobMode->size($jobName));
                    die;
                }

                if ( $jobMode->size($jobName) > 0 ) {
                    $jobMode->execute($jobName, $size);
                    $output->writeln('success!');
                } else {
                    throw new \Exception('the job is empty!');
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        } else {
            $output->writeln('the config mode must ssdb and redis!');
        }
    }
}