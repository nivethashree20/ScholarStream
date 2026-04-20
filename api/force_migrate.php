<?php
require_once '../backend/config/db_connect.php';

try {
    $sql = "
    ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS version INT DEFAULT 1;
    ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL;
    ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS is_latest BOOLEAN DEFAULT TRUE;
    ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS certificate_id VARCHAR(255) UNIQUE DEFAULT NULL;
    ALTER TABLE research_papers ADD COLUMN IF NOT EXISTS admin_comments TEXT DEFAULT NULL;
    
    CREATE TABLE IF NOT EXISTS paper_coauthors (
        id SERIAL PRIMARY KEY,
        paper_id INT NOT NULL,
        student_email VARCHAR(255) NOT NULL,
        FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS paper_feedback (
        id SERIAL PRIMARY KEY,
        paper_id INT NOT NULL,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_admin_reply BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";
    
    $pdo->exec($sql);
    echo "Migration force applied successfully!";
} catch (\PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>
