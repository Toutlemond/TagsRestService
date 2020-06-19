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
     * @return Array $task
     *
     */
    public function getTask($token): Array
    {

        $task = $this->em
            ->getRepository(Tasks::class)
            ->findOneBy(['token' => $token]);

        if (!$task) {
            return [
                'data' => 'No task found for token ' . $token,
                'error' => true,
            ];
        } else {
            if ($task->getReady() != true) {
                $this->executeTask($task);
            }

            return [
                'data' => $task->get(),
            ];

        }
    }

    /**
     * Получение информации о странице исходя из токена задания
     *
     * *
     * @param object $task
     *
     */
    public function executeTask($task)
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
    }


    /**
     * Тестовый метод - не по заданию.
     *
     *
     * Выводит все задания в базе - требовался для отладки
     *
     * @param null
     * @return Response
     * @Route("/tags/tasks")
     */
     //TODO Метод отсюда надо выкинуть ибо он не часть одной задачи
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
