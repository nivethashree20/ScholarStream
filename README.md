# ScholarStream - Research Paper Management System

A streamlined web application for students to submit research papers and for administrators to review them.

## Prerequisites

- **Laragon** (Recommended) or any WAMP/XAMPP stack.
- **PHP 8.3+**
- **MySQL**

## Installation & Setup

1. **Database Setup**:
   - Open **HeidiSQL** (via Laragon).
   - Create a new database named `mini`.
   - Import the `sql/schema.sql` file to create the necessary tables.

2. **Configuration**:
   - Check `config/db_connect.php` to ensure the database credentials match your local setup (default is `root` with no password).

3. **Running the Application**:
   - The server is currently running via the terminal at: `http://localhost:8000`
   - If the server stops, you can restart it by running:
     ```bash
     php -S localhost:8000
     ```

## Usage Guide

### For Students
1. **Register/Login**: Create a student account at `/auth/register.php`.
2. **Dashboard**: View your submission statistics and list of papers.
3. **Submit Paper**: Click "Register New Paper". 
   - Available Semesters will automatically filter based on your chosen Academic Year.
   - Abstract is limited to 500 words.
4. **Track Status**: Monitor if your paper is "Pending", "Approved", or "Declined".

### For Admins
1. **Login**: Use administrative credentials.
2. **Review Papers**: See all student submissions in one place.
3. **Approve/Decline**: Click "Review" to see full details and update the status of papers.

## Features
- **Dark Mode UI**: Professional, modern aesthetic with a purple primary theme.
- **Dynamic Forms**: Semester options that change based on Academic Year.
- **Real-time Validation**: Abstract word counting and file type checking.
