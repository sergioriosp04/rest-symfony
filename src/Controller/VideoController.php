<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;
class VideoController extends AbstractController
{
    private function resjson($data)
    {
        //serializar datos con servicio de serializer
        $response = new Response();
        $json = $this->get('serializer')->serialize($data, 'json');
        // response con http foundation
        //asignar contenido a la respuesta
        $response->setContent($json);
        // indicar formato de respuesta
        $response->headers->set('Content-type', 'application/json');
        // devolver respuesta
        return $response;
    }

    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwtAuth){
        //RECOGER EL TOKEN

        $token = $request->headers->get('Authorization', null);
        //comprobar si es correccto
        $authCheck = $jwtAuth->checktoken($token);
        if($authCheck){
            //datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);
            //recoger objetos del usuario
            $identiy = $jwtAuth->checktoken($token, true);
            //comprobar  y validar datos
            if(!empty($json)){
                $user_id = ($identiy->sub != null) ? $identiy->sub : null;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $url = (!empty($params->url)) ? $params->url : null;
                if(!is_null($user_id) && !empty($title)){
                    //guardar el nuevo video favorito

                    $em = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);
                    //crear y guardar objeto
                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setUrl($url);
                    $video->setStatus('normal');
                    $createdAt = new \DateTime('now');
                    $updatedAt = new \DateTime('now');
                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);
                    //guardar en db
                    $em->persist($video);
                    $em->flush();
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Video guardado con exito',
                        'video' => $video
                    ];
                }
            }else{
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'no se enviaron datos o son incorrectos',

                ];
            }
            //guardar el nuevo video favorti
        }else{
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'el usuario no esta autentificado'
            ];
        }
        //retornar respuesta
        return $this->resjson($data);
    }

    public function videos(Request $request, JwtAuth $jwtAuth, PaginatorInterface $paginator){
        // recoger la cabecer de autentificacion
        $token = $request->headers->get('Authorization', null);
        //comprobar el token
        $authCheck = $jwtAuth->checktoken($token);
        if($authCheck){
        //si es valido
            //conseguir identidad usuario
            $identity = $jwtAuth->checktoken($token, true);
            $em = $this->getDoctrine()->getManager();

            //utilizar bundle paginacion
            //consulta dql para utilizar pagination
            $dql = "SELECT v FROM App\Entity\Video v WHERE v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $em->createQuery($dql);

            //recoger parametro page de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 5;

            //invocar pagination
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $page,
                $items_per_page
            );
            $total = $pagination->getTotalItemCount();

            $data=[
                'status'=> 'success',
                'code' => 200,
                'total' => $total,
                "page" => $page,
                "items_for_page" => $items_per_page,
                "total_pages" => ceil($total / $items_per_page),
                "video" => $pagination,
                "user_id" => $identity->sub
            ];
        }else{
            $data=[
                'status'=> 'error',
                'code' => 404,
                'message' => 'El usuario no esta autentificado'
            ];
        }

        return $this->resjson($data);
    }
}
