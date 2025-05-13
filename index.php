<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/data.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-6">
        <?php include 'includes/header.php'; ?>
        <?php include 'includes/map.php'; ?>
        <?php include 'includes/sensor_cards.php'; ?>
        <?php include 'includes/footer.php'; ?>
    </div>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
