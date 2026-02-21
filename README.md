ZarMag — Warehouse Loading Manager (PHP/MySQL)

*** PROJECT IS CURRENTLY UNDER TRANSLATION FROM POLISH TO ENGLISH. ***

README for cloning, running, and presenting the project.

Overview
- Lightweight PHP application for managing warehouse orders and daily loadings.
- Features: user authentication, clients registry, orders CRUD, daily load groups, status tracking, and a simple dashboard.
- No external PHP frameworks; uses mysqli and the Intl extension for Polish date formatting.

Live Areas
- Login: loginWindow.php
- Dashboard: mainWindow.php
- Loadings: loading.php
- Orders: orders.php
- Clients: clients.php
- Admin Panel: panelAdministracjiWindow.php

Tech Stack
- PHP 8.x with Intl extension (for date localization)
- MySQL 5.7+ or 8.x
- Apache (XAMPP, WAMP or any LAMP stack)
- Frontend: plain HTML/CSS, Font Awesome via CDN

Requirements
- PHP 8.x, enabled extension: intl
- MySQL server
- Apache HTTP server
- Recommended: XAMPP (Windows/macOS) or a LAMP stack (Linux)

Getting Started
- Place the project
- Move the folder to your web server root, e.g.: C:\xampp\htdocs\ZarMagProject or /var/www/html/ZarMagProject.
- Create the database
  - Create a MySQL database named zarmagdb, suggested charset/collation: utf8mb4/utf8mb4_polish_ci.
  - If you have a SQL dump, import it now. If not, see “Database Schema (Referenced Fields)” below to prepare minimal tables.
- Configure DB credentials
  - Update helpers/dataBaseConnector.php to match your MySQL host/user/password/database.
- Start services
  - Ensure Apache and MySQL/MariaDB are running.
- Open the app
- http://localhost/ZarMagProject/loginWindow.php

Authentication
- No default credentials are provided in this repository.
- To log in for the first run, create a user record directly in the users table (passwords must be hashed with PHP’s password_hash), or import a prepared SQL dump that includes at least one user.

Project Structure
- helpers/ — DB connector and utilities (dataBaseConnector.php, dateHelper.php, logout.php)
- template/ — page chrome (header.php, footer.php)
- resources/ — images and logos
- *.php — top-level pages and forms (login, dashboard, orders, clients, admin)
- style.css — application styles

Key Files
- helpers/dataBaseConnector.php — MySQL connection helper
- helpers/dateHelper.php — Intl-based Polish date formatting
- template/header.php — navigation and layout
- loginWindow.php — authentication flow
- loading.php — daily loadings and status toggles
- orders.php / addOrders.php — orders listing, filtering, CRUD and auto-assigning to loads
- clients.php / addClient.php — clients listing, search, CRUD
- panelAdministracjiWindow.php / rejestracja.php — users list and registration

Database Schema (Referenced Fields)
These are the table and column names referenced by the code base. Your schema must include at least these fields.
- users
  - idPracownika, imie, nazwisko, plec, email, stanowisko, login, haslo, pytaniePomocnicze, dataUrodzenia, odpowiedz
  - The login and dashboard pages also reference IdPracownika, Login, Haslo, Imie (capitalized). On case-sensitive systems, standardize on lowercase columns or update queries accordingly.
- clients
  - Id, Nazwa, Adres, NrTelefonu, Email
- orders
  - Id, OrderNumber, ClientId, DeliveryAddress, OrderDate, DeliveryDate, Notes
- deliveries
  - Id, NumerZaladunku, OrderId, CzyZaladowane

Usage Notes
- Load Groups: when a new order is created, it is auto-assigned to a load (deliveries) per client and delivery date.
- Status Updates: warehouse staff can toggle CzyZaladowane per order within a selected loading number.
- Date Localization: formatPolishDate in helpers/dateHelper.php relies on the PHP Intl extension.

Configuration
- Database: helpers/dataBaseConnector.php (host, user, password, database name)
- Logo/branding: replace files in resources/ and adjust template/header.php if needed

Security and Portability
- This project uses mysqli with raw queries. If deploying beyond demo/educational use, consider input sanitization and prepared statements.
- Case sensitivity: Some files use mixed-case table and column names. On Linux/MariaDB with case-sensitive identifiers, align naming consistently or adjust queries.

Screenshots
- Add screenshots to resources/ and embed them here to showcase the UI.

License
- No license file is provided. Add a LICENSE file (e.g., MIT) if you plan to open-source the project.

Credits
- Built as an educational project for warehouse/order management.
