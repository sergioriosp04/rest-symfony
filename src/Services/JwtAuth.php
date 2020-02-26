<?php
namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;
use http\Env\Request;

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

    public function checktoken($jwt, $identity = false){
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch (\UnexpectedValueException $e){
            $auth = false;
        }catch (\DomainException $e){
            $auth = false;
        }

        if(isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($identity != false){
            $auth = $decoded;
        }

        return $auth;
    }

}