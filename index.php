<?php
require_once(__DIR__.'/core/Controller.php');

$config = require_once(__DIR__.'/config.php');

$c = new Controller($config);

$c->run();