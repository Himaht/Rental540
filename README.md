Rental540 – Car Rental Management System

A full-stack rental management platform built with PHP, MySQL, HTML/CSS, JavaScript, and Azure cloud integration.

📌 Overview

Rental540 is a web-based car rental management system designed to make it easy for customers to book vehicles and for admins to manage rentals, vehicles, customers, and payments.
The project began as coursework and expanded into a realistic, functional system with:
Validation
Login security
Dashboards
Reservation workflows
Cloud connectivity

✨ System Features

SQL Server database on Azure
MySQL relational database structure (local version)
PHP backend with secure database connection
Real-time validation for forms
Clean and interactive UI
Reservation success messages with a close button
Auto-clearing alerts when the user leaves a page

🗄️ Tech Stack
Frontend
HTML5
CSS3
JavaScript

Backend
PHP
MySQL (phpMyAdmin/WAMP)
Azure SQL (Cloud)
Python (Flask for cloud analytics)

Tools
Microsoft Azure
WAMP
Git & GitHub
VSCode

⚙️ Installation Guide
1. Clone the Repository - git clone https://github.com/your-username/Rental540.git

2. Move the Project to Your Server Root - C:/wamp64/www/Rental540
3. Import the Database
Open phpMyAdmin
Create a new named rental540
Import the included MySQL file

4. Update Database Credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental540";
http://localhost/Rental540

6. Run the Project
   http://localhost/Rental540

☁️ Cloud Integration With Azure SQL (Flask Application)

To test the Rental540 database in the cloud, I built a small Flask web application that connects directly to my Azure SQL Database instead of a local MySQL or WAMP server. This helped me prove that the Rental540 system can run in a cloud environment and not just on localhost.

⭐ What This Flask App Does
Connects to the Rental540 database hosted on Azure SQL
Pulls a list of all rental locations

⭐ How the Azure Connection Works
The code uses Python + pyodbc to make a secure connection directly to your Azure SQL server.
🔑 Connection details included:

Azure server name
Database name
Username + password
ODBC SQL Server driver
   

🚀 Future Enhancements

Online payment integration
Admin analytics dashboard (charts & KPIs)
Email notifications
Vehicle image carousel
Export reports to Excel/PDF

👩🏽‍💻 Author
Rahimatu Yushawu
Data & IT Enthusiast

GitHub: https://github.com/Himaht
Email: yushawurahima@gmail.com

📄 License
This project is for educational and personal portfolio use.
