<?php
/**
 * Created by PhpStorm.
 * User: Vadim B
 * Date: 16.06.2020
 * Time: 11:51
 */

// src/Controller/TagsController.php
namespace App\Controller;

use App\Entity\Tasks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TagsController extends AbstractController
{
    /**
     * Индексный метод
     *
     * @param String url
     *
     * @return Response
     * @Route("/tags")
     */
    public function index(): Response
    {


        $request = Request::createFromGlobals();
        $urlString = $request->request->get('url');


        preg_match_all('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $urlString, $result);

        $response = new Response();

        if ($result[0]) {
            $correctUrlStr = $result[0][0];

            $answer = $this->createtask($correctUrlStr);

            $response->setContent(json_encode([
                'task' => $answer['token'],
            ]));
        } else {
            $response->setContent(json_encode([
                'task' => null,
                'error' => true,
            ]));
        }


        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * Тестовый метод - не по заданию.
     *
     * Выводит все задания в базе - требовался для отладки
     *
     * @param null
     * @return Response
     * @Route("/tags/tasks")
     */
    public function tasks(): Response
    {

        $repository = $this->getDoctrine()->getRepository(Tasks::class);

        $tasks = $repository->findAll();

        $response = new Response();
        if ($tasks) {
            $ansver = [];
            foreach ($tasks as $oneTask) {
                $ansver[] = [
                    'task' => $oneTask->getToken(),
                    'url' => $oneTask->getUrl(),
                ];
            }
            $response->setContent(json_encode($ansver));

        } else {
            $response->setContent(json_encode([
                'tasks' => null,
                'error' => true,
            ]));
        }


        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * Получение информации о странице исходя из токена задания
     *
     * *
     * @param string $token
     *
     * @return Response
     *
     * @Route("/tags/{token}", name="tags_show")
     */
    public function show($token)
    {
        $task = $this->getDoctrine()
            ->getRepository(Tasks::class)
            ->findOneBy(['token' => $token]);

        $response = new Response();
        if (!$task) {
            $response->setContent(json_encode([
                'data' => 'No task found for token ' . $token,
                'error' => true,
            ]));
        } else {
            $url = $task->geturl();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


            $content = curl_exec($ch);
            $info = curl_getinfo($ch);

            $error = false;

            if ($content === false) {
                $data = false;
                $error["message"] = curl_error($ch);
                $error["code"] = self::$error_codes[curl_errno($ch)];

                $response->setContent(json_encode([
                    'data' => $error,
                    'error' => true,

                ]));

            } else {
                $data["content"] = $content;
                $data["info"] = $info;

                curl_close($ch);

                //Для начала найдем все теги какие есть
                $str = '/<(\w+)\s/';
                preg_match_all($str, $data["content"], $matches);

                $allTags = [];

                foreach ($matches[0] as $match) {
                    $match = (trim(str_replace("<", '', $match)));
                    if (!in_array($match, $allTags)) {
                        $allTags[$match] = null;
                    }
                }

                /*
                 * количество тэгов каждого типа на странице
                 */
                foreach ($allTags as $tag => &$number) {
                    $str = '/<\s*' . $tag . '[^>]*>(.*?)/';
                    preg_match_all($str, $data["content"], $matches);

                    $number = count($matches[0]);
                }

                $response->setContent(json_encode([
                    'data' => $allTags,
                ]));
            }

        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;

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
    private function createtask($url): Array
    {

        $entityManager = $this->getDoctrine()->getManager();
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
}
