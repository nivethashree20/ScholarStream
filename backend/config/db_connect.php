<?php
$db_path = __DIR__ . '/../../database/scholarstream.sqlite';
$dsn = "sqlite:$db_path";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, null, null, $options);
     // Enable foreign key support for SQLite
     $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
