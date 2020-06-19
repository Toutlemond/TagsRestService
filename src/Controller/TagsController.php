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
use App\Services\TasksService;
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
    public function index(TasksService $Tasks): Response
    {

        $request = Request::createFromGlobals();
        $urlString = $request->request->get('url');

        preg_match_all('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si',
            $urlString,
            $result);

        $response = new Response();

        if ($result[0]) {
            $correctUrlStr = $result[0][0];
            $answer = $Tasks->createtask($correctUrlStr);

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
    public function tasks(TasksService $Tasks): Response
    {
        $allTasks = $Tasks->gettasks();

        $response = new Response();

        $response->setContent(json_encode($allTasks));

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
    public function show($token,TasksService $Tasks)
    {
        $task = $Tasks->getTask($token);
        $response = new Response();

        $response->setContent(json_encode($task));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


}
