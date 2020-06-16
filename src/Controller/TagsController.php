<?php
/**
 * Created by PhpStorm.
 * User: Vadim B
 * Date: 16.06.2020
 * Time: 11:51
 */
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class LuckyController
{
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }
}
