-- Update research_papers table for versioning and feedback
ALTER TABLE research_papers 
MODIFY COLUMN status ENUM('Pending', 'Approved', 'Declined', 'Revision Required') NOT NULL DEFAULT 'Pending',
ADD COLUMN version INT DEFAULT 1,
ADD COLUMN parent_id INT DEFAULT NULL,
ADD COLUMN is_latest BOOLEAN DEFAULT TRUE,
ADD CONSTRAINT fk_parent_paper FOREIGN KEY (parent_id) REFERENCES research_papers(id) ON DELETE SET NULL;

-- Table for co-authors
CREATE TABLE IF NOT EXISTS paper_coauthors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE
);

-- Table for feedback threads
CREATE TABLE IF NOT EXISTS paper_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_admin_reply BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add unique certificate_id to approved papers (optional, can also be generated on the fly)
ALTER TABLE research_papers ADD COLUMN certificate_id VARCHAR(50) UNIQUE DEFAULT NULL;
