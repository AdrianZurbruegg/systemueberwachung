<?php
try {
    $pdo = new PDO( 'mysql:host=10.0.100.104;dbname=systemueberwachung', 'admin', 'London99!' );
    $statement = $pdo->prepare("SET NAMES utf8");
    $statement->execute();
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

?>