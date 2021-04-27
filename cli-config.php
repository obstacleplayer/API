<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
require_once 'boostrap.php';
return ConsoleRunner::createHelperSet($entityManager);
