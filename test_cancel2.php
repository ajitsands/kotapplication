<?php
require 'config.php';
require 'core/Database.php';
require 'core/Model.php';
require 'models/Order.php';
require 'core/Controller.php';
require 'controllers/ApiController.php';

$om = new Order();
$id = $om->createOrder('T2', 1, 'take_away', 'Test', '123456');

$c = new ApiController();
$c->cancelOrder(['id' => $id]);
