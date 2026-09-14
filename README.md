# Adoption Pawtal

A PHP-based pet adoption system for managing pets, applications, admin actions, and email notifications.

## Project structure

- `pet-adoption-system/` - application source code
- `pet_adoption_system.sql` - database schema and seed data

## Requirements

- PHP 8.0+
- MySQL or MariaDB
- Apache or Nginx
- Composer

## Local setup

1. Start your local PHP/MySQL environment (XAMPP, WAMP, Laragon, or similar).
2. Create a database named `pet_adoption_system`.
3. Import the SQL file:

```bash
mysql -u root -p pet_adoption_system < pet_adoption_system.sql
```

4. Copy the example environment file:

```bash
copy .env.example .env
```

5. Update your database settings in the environment or in the app config if needed.
6. Install Composer dependencies:

```bash
cd pet-adoption-system
composer install
```

7. Run the app from `pet-adoption-system/public` using your local web server.

## Admin access

Use the admin login page under `pet-adoption-system/admin/login.php`.

## Notes

- The project currently stores database configuration in PHP config files, so local credentials should not be committed publicly.
- Uploads for pet images are stored under `pet-adoption-system/uploads/pets/`.

## GitHub publishing

Initialize the repository and push to GitHub:

```bash
git init
git branch -M main
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/adoption-pawtal.git
git push -u origin main
```

Replace `YOUR_USERNAME` with your GitHub username.
