# StudentHub Portal | CHARUSAT

## Project Overview
StudentHub Portal is a web-based student management system developed as part of the Web Development Frameworks practicals. It provides student and administrator access to academic information and management features.

## Technologies Used
- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- XAMPP
- JSON and Fetch API

## Features
- Student registration and login
- Role-based access for students and administrators
- Session management and logout protection
- Student record management (CRUD)
- Event management (CRUD)
- Event poster uploads
- AJAX-based event API
- CSRF protection for event-management requests
- Administrator audit logs
- Session inactivity timeout
- Responsive navigation and interface

## Requirements
- Windows
- XAMPP with Apache and MySQL
- A modern web browser

## Installation and Setup
1. Install and start Apache and MySQL through XAMPP.
2. Copy the project folder into the XAMPP `htdocs` directory.
3. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
4. Create or select the database named `studenthubportal`.
5. Import the provided SQL database backup.
6. Check the database credentials in `db.php`.
7. Open `http://localhost/Practical%20WDF/` in your browser.

## Security
- Password verification and secure password storage
- Role-based page access
- Session ID regeneration after login
- Session inactivity timeout
- CSRF token verification
- Prepared SQL statements
- Administrator action logging

## Project Status
Core features and integration checks have been completed and tested locally.

## Developer
Yashvi Matang

## Academic Context
CHARUSAT — B.Tech Information Technology
Course: Web Development Frameworks
