# Adoption Pawtal

Adoption Pawtal is a PHP-based pet adoption platform designed to connect prospective adopters with animals seeking permanent homes. The system provides a public-facing adoption experience for browsing available pets, submitting applications, and managing adoption-related workflows through an administrative dashboard.

---

## Overview

The application enables visitors to view current pets available for adoption, filter listings by relevant criteria, and access detailed profiles for each animal. Administrators can manage pet records, review application submissions, update adoption statuses, and communicate with applicants through the built-in email workflow.

This project was developed for the LBYCPG2 laboratory subject and serves as a practical implementation of a web-based adoption management system.

---

## Features

- Responsive and user-friendly public interface for pet browsing
- Search and filtering by animal type, size, sex, and name or breed
- Detailed pet profiles with age, breed, size, and sex information
- Adoption application form for interested users
- Administrative dashboard for managing pets and applications
- Application status tracking and approval workflow
- Email notifications for updates and communication
- Pet image upload support for profile listings
- Secure database-backed architecture using PHP and MySQL

---

## Contributors

- Melanie Dotollo - Coding
- Romela Angeline Galono - Coding
- Xhane Batiller - Documentation
- Zamanttha Zyrah Sahidulla - UI/UX Design

---

## Screenshot

![Screenshot](image1.png)

---

## Requirements

- PHP 8.0 or later
- MySQL or MariaDB
- Apache or Nginx web server
- Composer
- Web browser with JavaScript enabled

---

## Installation

1. Clone the repository:

   ```bash
   git clone <repository-url>
   cd adoption-pawtal
   ```

2. Start your local PHP and MySQL environment using XAMPP, WAMP, Laragon, or another preferred stack.

3. Create a database named `pet_adoption_system`.

4. Import the SQL file:

   ```bash
   mysql -u root -p pet_adoption_system < pet_adoption_system.sql
   ```

5. Install Composer dependencies:

   ```bash
   cd pet-adoption-system
   composer install
   ```

6. Run the project using your local web server and open the application from the `pet-adoption-system/public` directory.

---

## Configuration

The database connection settings are defined in the project configuration files under `pet-adoption-system/config/`. Ensure that your local credentials match your environment before running the application.

Uploaded pet images are stored in the `pet-adoption-system/uploads/pets/` directory.

---

## Admin Access

The administrative login page is available at:

`pet-adoption-system/admin/login.php`

Use valid admin credentials configured in the database to access the management dashboard.

---

## Project Structure

```text
adoption-pawtal/
├── pet-adoption-system/
│   ├── admin/
│   ├── config/
│   ├── includes/
│   ├── public/
│   ├── uploads/
│   ├── composer.json
│   ├── composer.lock
│   └── vendor/
├── pet_adoption_system.sql
├── README.md
├── image1.png
├── .gitignore
└── index.html
```

---

## Notes

- Database credentials should not be committed to a public repository.
- The system is intended for local or controlled deployment environments.
- GitHub Pages is used only for the static landing page; the full application requires a PHP server and a MySQL database.

---

## GitHub Publishing

```bash
git init
git branch -M main
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/adoption-pawtal.git
git push -u origin main
```

Replace `YOUR_USERNAME` with your actual GitHub username.
