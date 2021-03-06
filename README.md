# TagsRestService
Тестовое Задание Tags
RestСервис для подсчета HTML тэгов на страницах

Необходимо используя php версии 7.2- 7.3 реализовать RESTful-сервис на Symfony 5, 
который позволит пользователю произвести следующую последовательность действий (в формате JSON):

1. Отправляем на endpoint `/tags/` POST-запрос, содержащий URL-адрес любой страницы в интернете.
 В ответе приходит идентификатор задания на выполнение.
2. По GET-запросу к endpoint `/tags/<идентификатор_задания>` 
получаем количество каждого типа HTML-тэгов (например, `{"html": 1, "head": 1, "body": 1, "p": 10, "img": 2}`)
 на веб-странице или ошибку, если URL оказался чем-то отличным от HTML-страницы, либо статус, что задание еще выполняется (формат произвольный).

Реализовать очередь не используя готовые решения такие как Rabbitmq



Для работы требуется 
<ul>
<li>PHP 7.2 </li>
<li>Symfony 5</li>
<li>npm</li>
<li>Composer</li>
</ul>

<b>Установите Symfony с оффициальной страницы  <a href="https://symfony.com/download">symfony.com</a> 
</b>

<b>Проверьте установку коммадой :symfony</b>

Должно ответить что то такое :
<p>C:\Server2\OSPanel\domains\tags_rest_service>symfony</p>
<p>Symfony CLI version v4.16.2 (c) 2017-2020 Symfony SAS</p>

<b>Клонируйте репозиторий коммандой </b>
<p>git clone git@github.com:Toutlemond/TagsRestService.git</p>

<b>Перейдите в папку TagsRestService</b>

cd TagsRestService

<b>Установите зависимости (хотя они врятли поребуются)</b>
<p>npm install</p>
<p>composer install</p>

<b>Создайте базу данных</b>

php bin/console doctrine:database:create

<p>В проекте используется sqlite но если требуется другая база данных отредактируйте .env файл строчку DATABASE_URL</p>

<b>Накатите миграцию на базу данных</b>
<p>php bin/console doctrine:migrations:migrate</p>

<b>Запустите встроенный Web-Server</b>
<p>symfony server:start</p>



<h2>Работа с сервисом </h2>
<p>Для работы с сервисом можно использовать приложение POSTMAN </p>

<b>POST-запрос на адрес endpoint/tags/  с параметром "url" в теле запроса - Создаст задание </b>

<b> GET-запрос к endpoint `/tags/tasks` - Будет выдан список всех заданий</b>

<b> GET-запрос к endpoint `/tags/<идентификатор_задания>` - Будет выдано количество каждого типа HTML-тэгов </b>
