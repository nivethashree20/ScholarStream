CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'student' CHECK(role IN ('student', 'admin')),
    organization TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS research_papers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    academic_year TEXT NOT NULL,
    semester TEXT NOT NULL,
    organization TEXT NOT NULL,
    department TEXT NOT NULL,
    research_area TEXT NOT NULL,
    title TEXT NOT NULL,
    guide_name TEXT NOT NULL,
    abstract TEXT NOT NULL,
    file_path TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending', 'Approved', 'Declined', 'Revision Required')),
    version INTEGER DEFAULT 1,
    parent_id INTEGER DEFAULT NULL,
    is_latest BOOLEAN DEFAULT 1,
    certificate_id TEXT UNIQUE DEFAULT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES research_papers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS paper_coauthors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paper_id INTEGER NOT NULL,
    student_email TEXT NOT NULL,
    FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS paper_feedback (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paper_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    is_admin_reply BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paper_id) REFERENCES research_papers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed an admin user (password: admin@123)
INSERT OR IGNORE INTO users (name, email, password, role) VALUES 
('Admin', 'admin@scholarstream.com', '$2y$10$Gg6O8ul6sSRyJLvlybcwreKQkmLzGuqlhcRgXC8l2XCxbFooDabfS', 'admin');
