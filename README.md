# think-job
基于thinkphp6的一个简单任务队列系统

# 使用方法

## 1.引用包文件

```
composer require cncoders\think-job *
```

## 2.复制配置文件

示例配置文件位于目录的config目录里面的job.php根据自己的项目需求配置

## 3. 建立工作文件

在配置文件设置的 “namespace” 对应的目录内建立自己需要的工作文件

文件示例如下

```
<?php
namespace app\job;

use cncoders\job\JobInterfaces;
use think\facade\Log;

class TestJob implements JobInterfaces
{
    public function exec($param = [])
    {
        Log::write(json_encode($param));
        return true;
    }
}

```
这是很简单的记录日志文件，将该传递给该队列的参数记录到日志

## 4.控制器中使用

```
<?php
namespace app\sys\controller;

use app\BaseController;
use auth\Auther;
use cncoders\job\JobTrait;

class Auth extends BaseController
{
    use JobTrait;

    /**
     * @param Auther $auther
     * @return \think\response\Json
     */
    public function index(Auther $auther)
    {
        $token = $auther->token([
            'user_id' => '100102',
            'nickname' => 'user昵称'
        ]);

        $this->addJob('test_job', 'TestJob', ['token' => $token]);

        return json([
            'token' => $token
        ]);
    }
}
```

## 5.异步执行
我们提供了同步与异步执行的方式 您只需要配置job.php type除sync以外都是异步执行，您需要将
cncoders\job\JobCommand加入到thinkphp的console.php内

```
php think job:boot test_job  执行一次任务  通过参数 --size 指定一次需要执行的多少条
php think job:boot test_job --info size 可以查看队列中有多少条未执行的

#test_job为自己定义的队列名称，在加入队列的时候定义
```

# 申明

本插件在中小型项目中比较实用 专业大型项目请使用市场上成熟的消息队列系统