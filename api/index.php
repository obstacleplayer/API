<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;
use Slim\App;
use Slim\Container;

require_once '../boostrap.php';
require_once '../modeles/Product.php';
require_once '../modeles/Client.php';


const KEY = "Hello-world";
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new Container($configuration);
$app = new App($c);

$Jwt = new Tuupola\Middleware\JwtAuthentication([
    "attribute" => "token",
    "header" => "Authorization",
    "regexp" => "/Bearer\s+(.*)$/i",
    "secure" => false,
    "algorithm" => ["HS256"],
    "secret" => KEY,
    "path" => ["/api"],
    "error" => function($response, $args) {
        $data = array('error' => 'erreur' , 'error' => 'AUTO');
        return $response -> withHeader("Content-Type", "application/json")->getBody()->write(json_encode($data));
    }
]);


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->add($Jwt);

$app->get('/product', 'getProduct');

$app->get('/getClient', 'getClient');
$app->post('/client', 'addClient');
$app->put('/client/{id}', 'updateClient');
$app->delete('/client/{id}', 'deleteClient');
$app->get('/connexion', '');
$app->get('/product/{id}','getProductById');

function login($request, $response, $args){
    global  $entityManager;

    $body = (array)json_decode($request->getBody());
    $clientRepository = $entityManager->getRepository(Client::class);
    $user = $clientRepository->findOneBy(array('login' => $body['login'], 'password'=> $body['password']));

    if($user != null) {

      $issuedAt = time();
      $expiration = $issuedAt + 30;
      $payload = array(
        'clientId' => $user->getId(),
        'login' => $user->getLogin(),
        'issue' => $issuedAt,
        'expiration' => $expiration
      );
      $jwt = JWT::encode($payload, KEY, "HS256");

      $response = $response->withHeader("Authorization", "Bearer {$jwt}");
    }

  return $response->withHeader("Content-Type", "application/json;charset=utf-8");

}

$app->post('/login','login');

function setHeader($response) {
    return $response
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
}


function getProduct($resquest,$response,$args)
{
  global $entityManager;
  $productRepository = $entityManager->getRepository(Product::class);
  $products = $productRepository->findAll();
  $array = [];
  foreach($products as $product) {
    $array[] = array('id'=>$product->getId(),
      'category'=>$product->getCategory(),
      'name'=>$product->getName(),
      'price'=>$product->getPrice(),
      'size'=>$product->getSize(),
      'source'=>$product->getSource());
  }
  $response = $response->withHeader("Content-Type", "application/json;charset=utf-8");
  return $response->write(json_encode($array));
}

function addClient($request,$response,$args) {
    global $entityManager;
    $body = $request->getParsedBody();
    $client = new Client();
    $client->setFirstname($body['firstname']);
    $client->setLastname($body['lastname']);
    $client->setCivility($body['civility']);
    $client->setTel($body['tel']);
    $client->setCity($body['city']);
    $client->setAddress($body['address']);
    $client->setPostalcode($body['postalcode']);
    $client->setCity($body['country']);
    $client->setMail($body['mail']);
    $client->setLogin($body['login']);
    $client->setPassword(password_hash($body['password'], PASSWORD_DEFAULT));
    $entityManager->persist($client);
    $entityManager->flush();
    $data = array(
      "lastname" => $client->getFirstname(),
      "firstname" => $client->getLastname(),
      "civility" => $client->getCivility(),
      "tel" => $client->getTel(),
      "address" => $client->getAddress(),
      "postalcode" => $client->getPostalcode(),
      "country" => $client->getCity(),
      "mail" => $client->getMail(),
      "login" => $client->getLogin(),
      "password" => $client->getPassword()
    );

    $response = $response->withHeader("Content-Type", "application/json;charset=utf-8");
    return $response->write(json_encode($data));

}

function getProductById (Request $request, Response $response, $args): Response
{
  global $entityManager;
  $products = $entityManager->find(Product::class, $args['id']);
  $response->getBody()->write(json_encode($products));

  return $response->withHeader("Content-Type", "application/json;charset=utf-8");
};

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $array = [];
    $array ["nom"] = $args ['name'];
    $response->getBody()->write(json_encode ($array));
    return $response;
});

$app->run ();
