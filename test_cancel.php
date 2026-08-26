<?php
require 'config.php';
require 'core/Database.php';
require 'core/Model.php';
require 'core/Controller.php';
require 'controllers/ApiController.php';
$c = new ApiController();
$c->cancelOrder(['id' => 34]);
