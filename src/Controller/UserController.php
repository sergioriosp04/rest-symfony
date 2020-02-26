<?php

namespace App\Controller;

use PhpParser\Node\Expr\New_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\User;
use App\Entity\Video;
use App\Services\JwtAuth;

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

    public function register(Request $request){
        //recoger los datos por post
        $json = $request->get('json', null);
        $params = json_decode($json);

        //decodificar el json
        //respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Usuario no se ha creado',
            'params' => $params
        ];

        //comprobar y validar datos
        if(!empty($json)){
            $name = (!empty($params->name))? $params->name : null;
            $surname = (!empty($params->surname))? $params->surname : null;
            $email = (!empty($params->email))? $params->email : null;
            $password = (!empty($params->password))? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && !empty($password) && !empty($name) && count($validate_email ) == 0){
                // si la validacion es correcta, crear el objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setSurnamen($surname);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime('now'));
                //cifrar contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);
                $data = $user;

                //comprobar si el usuario existe(duplicado)
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));
                // si no existe, guardar en la db
                if(count($isset_user) == 0){
                    //guardar usuario
                    $em->persist($user);
                    $em->flush();
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario guardado con exito',
                        'user' => $user
                    ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Usuario ya existe',
                    ];
                }

            }else{
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Usuario no se ha creado',
                ];
            }
        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no se ha creado',
            ];
        }

        // hacer respuesta en json
        //return $this->resjson($data);
        return new JsonResponse($data);
    }

    public function login(Request $request, JwtAuth $jwtAuth){
        // recibir los datos por post
        $json = $request->get('json', null);
        $params = json_decode($json);

        //comprobar y validar datos
        if(!is_null($json)){
            $email = (!empty($params->email))? $params->email : null;
            $password = (!empty($params->password))? $params->password : null;
            $gettoken = (!empty($params->gettoken))? $params->gettoken : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && !empty($password) && count($validate_email) == 0){
            //cifrar contraseña
                $pwd = hash('sha256', $password);

            //llamada servicio para identificar al usuario jwt
                if($gettoken){
                    $signup = $jwtAuth->signup($email, $pwd, $gettoken);
                }else{
                    $signup = $jwtAuth->signup($email, $pwd);
                }

                return new JsonResponse($signup);
            }
        }else{
            $data = [
                'status'=> 'error',
                'code' => 400,
                'message' => 'el usuario no se ha podido identificar2'
            ];
        }

        return $this->resjson($data);
    }
    public function edit(Request $request, JwtAuth $jwtAuth){
        //recoger cabecera de authentification
        $token = $request->headers->get('Authorization');

        //crear metodo para comprobar si el token es correcto
        $authCheck = $jwtAuth->checktoken($token);

        //si el jwt es correcto hacer la actualizaciob user
        if($authCheck){
            //actualizar usuario
            //conseguir entity manager
            $em = $this->getDoctrine()->getManager();
            //conseguir los datos del usuario autentificado
            $identity = $jwtAuth->checktoken($token, true);
            //conseguir el usuario a actualizar completo
            //recoger los datos por POST
            //comprobar y validar datos
            //asignar nuevos datos al objeto del usuario
            //comprobar duplicados
            //guardar cambios en la db
        }else{

        }

        //...
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'error en el update del user',
            'token' => $token
        ];
        return $this->resjson($data);
    }
}
