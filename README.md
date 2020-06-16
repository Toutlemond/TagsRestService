# TagsRestService
Тестовое Задание Tags
RestСервис для подсчета HTML тэгов на страницах


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



