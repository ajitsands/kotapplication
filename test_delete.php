<?php
require 'config.php';
require 'core/Database.php';
require 'core/Model.php';
require 'models/Kot.php';

$kotModel = new Kot();
var_dump($kotModel->deleteKotItem(43));
