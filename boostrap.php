<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
date_default_timezone_set('Europe/Paris');
require_once "vendor/autoload.php";
$isDevMode = true;
$config = Setup::createYAMLMetadataConfiguration(array(__DIR__ . "/config/yaml"), $isDevMode);
$conn = array(
    'host' => 'ec2-54-74-156-137.eu-west-1.compute.amazonaws.com',
    'driver' => 'pdo_pgsql',
    'user' => 'bfckpqvgzyurze',
    'password' => 'f524e594661dba961efab6f1da99d332f6424499ac1c051a8ca507b72c093ec9',
    'dbname' => 'dfac2n8lpf7off',
    'port' => '5432'
);
$entityManager = EntityManager::create($conn, $config);
