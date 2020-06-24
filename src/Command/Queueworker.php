<?php
/**
 * Created by PhpStorm.
 * User: Vadim B
 * Date: 23.06.2020
 * Time: 14:23
 */

namespace App\Command;


use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SymfonyBundles\RedisBundle\Redis;
use App\Services\TasksService;
use Doctrine\ORM\EntityManagerInterface;


class Queueworker extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:queue-worker';
    protected $redis;
    private $TasksService;

    public function __construct(TasksService $TasksService)
    {
        $this->TasksService = $TasksService;

        parent::__construct();
        $this->redis = new Redis\Client(array(
            'host' => 'localhost',
            'port' => 6379,
            'database' => 0,

        ));

    }

    protected function configure()
    {
        $this
            ->setDescription('Start the worker to read queue')
            ->setHelp('This command allows start the worker to read queue');

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Start queue worker',
            '==================',
            '',
        ]);

        while (true) {

            list($queue, $message) = $this->redis->brPop(["message_queue"], 0);

            $output->writeln([
                'Get   Some   Job',
                '================',
                '',
            ]);
            $output->writeln($message);

            $task = json_decode($message);
            $task =  $this->TasksService->getTask($task->token);

            $result = $this->TasksService->executeTask($task);

            if($result == true){
                $output->writeln([
                    '',
                    '================',
                    '+++Job"s done+++'
                ]);
            }else{
                $output->writeln([
                    $task->token,
                    '',
                    '================',
                    '--Job"s broken--'
                ]);
            }
        }


        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        //return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }

}
