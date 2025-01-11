<?php

$host = '172.18.3.182';
$db = 'dictionary';
$user = 'api';
$pass = '12345678';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Connection error: " . $e->getMessage());

}

$filePath = 'words_dictionary.json';

if (!file_exists($filePath)) {

    die("Error: JSON file not found.");

}

$jsonData = file_get_contents($filePath);
$words = json_decode($jsonData, true);

if ($words === null) {

    die("Error decoding JSON.");

}

try {

    $stmt = $pdo->prepare("INSERT INTO words_dictionary (words, frequency, deleted_at, created_at, updated_at) VALUES (:word, :frequency, null, sysdate, null)");

  
    foreach ($words as $word => $frequency) {

        $stmt->execute([':word' => $word, ':frequency' => $frequency]);

    }

    echo "Words entered successfully!";

} catch (PDOException $e) {

    die("Error when entering data: " . $e->getMessage());

}
