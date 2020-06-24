<?php
/**
 * Created by PhpStorm.
 * User: Vadim B
 * Date: 19.06.2020
 * Time: 13:30
 */

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tasks;
use PhpParser\Node\Expr\Cast\Object_;
use SymfonyBundles\RedisBundle\Redis;


class TasksService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Создание задания в базе
     *
     *
     * @param string $url
     *
     * @return array $answer
     *
     */
    public function createTask($url): Array
    {

        $entityManager = $this->em;
        // Для генерации токена просто возьмем время и рандомное число
        $token = sha1(time() + rand(100, 1000));


        $task = new Tasks();
        $task->setToken($token);
        $task->setUrl($url);

        $entityManager->persist($task);
        $entityManager->flush();

        $this->setTask($task);

        $answer = [
            'id' => $task->getId(),
            'token' => $token
        ];

        return $answer;
    }

    /**
     * Получение информации о странице исходя из токена задания
     *
     * *
     * @param String $token
     *
     * @return Object $task
     *
     */
    public function getTask($token): Object
    {

        $task = $this->em
            ->getRepository(Tasks::class)
            ->findOneBy(['token' => $token]);

        if (!$task) return null;
        return $task->get();


    }

    /**
     * Постановка задания в очередь
     *
     * @param Object $task
     *
     * @return void
     *
     */
    protected function setTask($task)
    {
        $redis = new Redis\Client(array(
            'host' => 'localhost',
            'port' => 6379,
            'database' => 0,

        ));
        if (isset($task)) {
            $redis->lPush("message_queue", json_encode($task));
        }
    }

    /**
     * Выполнение задания
     *
     * *
     * @param object $task
     *
     * @return boolean
     *
     */
    public function executeTask($task): bool
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $task->geturl());
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $content = curl_exec($ch);
        $info = curl_getinfo($ch);

        $error = false;

        if ($content === false) {
            $data = false;
            $error["message"] = curl_error($ch);
            $error["code"] = self::$error_codes[curl_errno($ch)];

            $ansver = 'error';

        } else {
            $data["content"] = $content;
            $data["info"] = $info;

            curl_close($ch);

            //Для начала найдем все теги какие есть
            $str = '/<(\w+)\s/';
            preg_match_all($str, $data["content"], $matches);

            $ansver = [];
            //Для иммитации длительной обработки задания нужно раскомментаровать задержку в 20 секунд
            sleep(20);
            
            foreach ($matches[0] as $match) {
                $match = (trim(str_replace("<", '', $match)));
                if (!in_array($match, $ansver)) {
                    $ansver[$match] = null;
                }
            }

            /*
             * количество тэгов каждого типа на странице
             */
            foreach ($ansver as $tag => &$number) {
                $str = '/<\s*' . $tag . '[^>]*>(.*?)/';
                preg_match_all($str, $data["content"], $matches);
                $number = count($matches[0]);
            }
        }

        $task->setReady(true);
        $task->setAnswer(json_encode($ansver));

        $entityManager = $this->em;
        $entityManager->persist($task);
        $entityManager->flush();
        if ($error == false) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Тестовый метод
     *
     *
     * Выводит все задания в базе - требовался для отладки
     *
     * @param null
     * @return Response
     * @Route("/tags/tasks")
     */
    //TODO Метод отсюда надо выкинуть ибо он не часть единственной задачи
    public function getTasks(): Array
    {
        $repository = $this->em->getRepository(Tasks::class);

        $allTasks = $repository->findAll();

        if ($allTasks) {
            $ansver = [];
            foreach ($allTasks as $oneTask) {
                $ansver[] = [
                    'task' => $oneTask->getToken(),
                    'url' => $oneTask->getUrl(),
                    'ready' => $oneTask->getReady(),
                    'answer' => $oneTask->getAnswer(),
                ];
            }

            return $ansver;
        } else {
            $ansver = [
                'task' => null,
                'error' => true,
            ];
            return $ansver;


        }


    }


}
