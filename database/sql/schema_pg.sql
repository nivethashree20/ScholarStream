-- PostgreSQL Schema for ScholarStream
-- Note: PostgreSQL uses SERIAL for auto-incrementing IDs

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) CHECK (role IN ('student', 'admin')) NOT NULL DEFAULT 'student',
    organization VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Drop dependent tables to allow clean recreation of research_papers without losing users
DROP TABLE IF EXISTS paper_feedback;
DROP TABLE IF EXISTS paper_coauthors;
DROP TABLE IF EXISTS research_papers;

CREATE TABLE IF NOT EXISTS research_papers (
    id SERIAL PRIMARY KEY,
    student_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    research_area VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    guide_name VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status VARCHAR(20) CHECK (status IN ('Pending', 'Approved', 'Declined', 'Revision Required')) NOT NULL DEFAULT 'Pending',
    version INT DEFAULT 1,
    parent_id INT DEFAULT NULL,
    is_latest BOOLEAN DEFAULT TRUE,
    certificate_id VARCHAR(255) UNIQUE DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES research_papers(id) ON DELETE SET NULL
);

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

-- Seed an admin user (password: admin@123)
-- Only insert if email doesn't exist
INSERT INTO users (name, email, password, role)
SELECT 'Admin', 'admin@scholarstream.com', '$2y$10$Gg6O8ul6sSRyJLvlybcwreKQkmLzGuqlhcRgXC8l2XCxbFooDabfS', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@scholarstream.com');
