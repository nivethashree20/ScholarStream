<?php
// Default options for PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Check for DATABASE_URL (common in Render/Heroku)
$database_url = getenv('DATABASE_URL');

try {
    if ($database_url) {
        // PostgreSQL connection
        $url = parse_url($database_url);
        
        $host = $url["host"];
        $port = $url["port"] ?? 5432;
        $user = $url["user"];
        $pass = $url["pass"];
        $db   = substr($url["path"], 1);
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$db";
        $pdo = new PDO($dsn, $user, $pass, $options);
    } else {
        // Fallback to SQLite (Local Development)
        $db_path = __DIR__ . '/../../database/scholarstream.sqlite';
        $dsn = "sqlite:$db_path";
        $pdo = new PDO($dsn, null, null, $options);
        
        // Enable foreign key support for SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
    }
} catch (\PDOException $e) {
    // In production, you might want to log this instead of throwing it directly
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
