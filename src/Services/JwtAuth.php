<?php
namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth{

    public $manager;
    public $key;

    public function __construct($manager)
    {
        $this->manager = $manager;
        $this->key = 'key';
    }

    public function signup($email, $password, $gettoken = null){
        //comproabar si el ususario existe
        $user_repo = $this->manager->getRepository(User::class);
        $user = $user_repo->findOneBy([
            'email' => $email,
            'password' => $password
        ]);

        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        // si existe el user generar el jwt
        if($signup){
            $token = [
                'sub' => $user->getId(),
                'name' => $user->getName(),
                'surname' => $user->getSurnamen(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'iat' => time(),
                'exp' => time() + (60 * 60)
            ];
            //comprobar el flag getttoken, condicion
            $jwt = JWT::encode($token, $this->key, 'HS256');

            if(!is_null($gettoken)){
                $data = $jwt;
            }else{
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }
        }else{
            $data = [
                'status' => 'error',
                'message' => 'login incorrecot',
                'code' => 404
            ];
        }
        //devolver datos
        return $data;
    }
}