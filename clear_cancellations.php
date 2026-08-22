<?php
require 'config.php';
require 'core/Database.php';
$db = Database::getInstance()->getConnection();
$db->query("UPDATE kot_items SET refund_status = 'refunded' WHERE refund_status = 'pending'");
echo "Success: Cancelled items have been marked as refunded and cleared from the list.\n";
