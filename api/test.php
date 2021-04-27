<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\NotFoundException;
use Tuupola\Middleware\HttpBasicAuthentication;
use \Firebase\JWT\JWT;

require 'vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
$app = AppFactory::create();

const JWT_SECRET = "hugofuchs";

function addCorsHeaders (Response $response) : Response {

  $response = $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', '*')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
    ->withHeader('Access-Control-Allow-Credentials', 'true');

  return $response;
}

$options = [
  "attribute" => "token",
  "header" => "Authorization",
  "regexp" => "/Bearer\s+(.*)$/i",
  "secure" => false,
  "algorithm" => ["HS256"],
  "secret" => JWT_SECRET,
  "path" => ["/api"],
  "ignore" => ["/hello","/api/hello","/api/login","/api/createUser"],
  "error" => function ($response, $arguments) {
    $data = array('ERREUR' => 'Connexion', 'ERREUR' => 'JWT Non valide');
    $response = $response->withStatus(401);
    return $response->withHeader("Content-Type", "application/json")->getBody()->write(json_encode($data));
  }
];

$app->post('/login', function (Request $request, Response $response, $args) {
  $issuedAt = time();
  $expirationTime = $issuedAt + 60;
  $payload = array(
    'userid' => "12345",
    'email' => "hugo.haenel@live.fr",
    'pseudo' => "hugo67",
    'iat' => $issuedAt,
    'exp' => $expirationTime
  );

  $token_jwt = JWT::encode($payload,JWT_SECRET, "HS256");
  $response = $response->withHeader("Authorization", "Bearer {$token_jwt}");
  return $response;
});
