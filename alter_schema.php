<?php
require 'config.php';
require 'core/Database.php';
try {
    Database::getInstance()->getConnection()->exec("ALTER TABLE orders MODIFY status ENUM('active','closed','completed','cancelled') DEFAULT 'active'");
    echo 'OK';
} catch (Exception $e) {
    echo $e->getMessage();
}
