<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController
{
    private function resjson($data){
        //serializar datos con servicio de serializer
        $json = $this->get('serializer')->serialize($data, 'json');
        // response con http foundation
        $response = new Response();
        //asignar contenido a la respuesta
        $response->setContent($json);
        // indicar formato de respuesta
        $response->headers->set('Content-type', 'application/json');
        // devolver respuesta
        return $response;
    }

    public function index()
    {
        // utilizar el repositorio de una entidad para poder accerder a una cantidad de metodos e.t.c
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $user = $user_repo->find(1);
        $users = $user_repo->findAll();
        $videos = $video_repo->findAll();

        /*$users = $user_repo->findAll();
        foreach ($users as $user){
            echo "<h5>". $user->getName() ."</h5>";

            foreach ($user->getVideos() as $video){
                echo "<h5>". $video->getTitle() ."</h5>";
            }
        }
        die();*/

        $data=[
            'message'=> 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];
        return $this->resjson($videos);
    }
}
