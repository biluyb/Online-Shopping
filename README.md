# Online Shopping Registration System

## 📦 Project Overview
A full‑stack **online‑shopping** web application built with **PHP**, **MySQL**, **Bootstrap 5**, and a modern UI (glassmorphism, dark/light mode, dynamic navigation). The project runs on a standard XAMPP stack on Windows.

---

## 🛠 Prerequisites
| Requirement | Version |
|------------|---------|
| **Operating System** | Windows 10/11 |
| **XAMPP** | 8.2.0 or later (includes Apache, MySQL, PHP) |
| **PHP** | 8.0+ (bundled with XAMPP) |
| **Composer** *(optional, only if you add packages later)* | latest |
| **Browser** | Chrome/Firefox/Edge (any modern browser) |

---

## 📂 Repository Structure
```
online-shopping/
├─ assets/            # images, fonts, etc.
├─ css/               # custom styles (style.css, profile.css, responsive.css)
├─ includes/          # reusable PHP includes (header.php, db.php, ...)
├─ vendor/            # third‑party libraries (if any)
├─ *.php              # page templates (index.php, shop.php, ...)
├─ fullstack_db.sql   # complete database dump (drop‑create + data)
└─ README.md          # **you are here**
```

---

## 🚀 Quick Start Guide (for your teammate)
1. **Install XAMPP**
   - Download from https://www.apachefriends.org/index.html and install.
   - During installation, keep the defaults (Apache, MySQL, PHP).
   - After installation, open the **XAMPP Control Panel** and start **Apache** and **MySQL**.

2. **Place the project folder**
   - Copy the entire `online-shopping` directory to `C:\xampp\htdocs\`.
   - The final path should be: `C:\xampp\htdocs\online-shopping\`.

3. **Create the MySQL database**
   - Open **phpMyAdmin** (http://localhost/phpmyadmin) **or** use the MySQL CLI.
   - Run the supplied SQL dump:
   ```sql
   -- fullstack_db.sql (included in the project root)
   
   -- If using phpMyAdmin:
   --   1. Click "Import".
   --   2. Choose `fullstack_db.sql`.
   --   3. Click "Go".
   
   -- If using the command line:
   mysql -u root -p < C:\xampp\htdocs\online-shopping\fullstack_db.sql
   ```
   - This will:
     * Drop any existing `fullstack_db` database.
     * Re‑create the database with the correct character set (`utf8mb4`).
     * Populate all tables in the proper order (foreign‑key safe).

4. **Configure the DB connection**
   - Open `C:\xampp\htdocs\online-shopping\includes\db.php`.
   - Ensure the credentials match your XAMPP MySQL setup (default root with no password):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');          // leave empty if you didn't set a password
   define('DB_NAME', 'fullstack_db');
   ```
   - If you set a MySQL password, update `DB_PASS` accordingly.

5. **Test the application**
   - In your browser, navigate to: `http://localhost/online-shopping/`
   - You should see the home page with the navigation bar, dark/light toggle, and sample products.
   - Try logging in with a test account (you can create one via the **Register** page) or explore the catalogue.

---

## 📋 Additional Tips
- **Changing the site name / description** – edit the `settings` table via phpMyAdmin (`setting_key` = `site_name`, `site_description`).
- **File permissions** – on Windows XAMPP you normally don’t need to adjust permissions, but the `uploads/avatars/` folder should be writable for profile picture uploads.
- **Enabling URL rewriting** – the project does not rely on `.htaccess` rules, but if you add any later, ensure `mod_rewrite` is enabled in Apache.
- **Debugging** – enable error reporting temporarily by adding the following to `includes/db.php`:
  ```php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  ```
  Remember to remove/disable it in production.

---

## 📦 Deploying to Production (optional)
1. Move the folder to your web server’s document root.
2. Secure the `includes/` directory (e.g., add a `.htaccess` denying direct access).
3. Use a strong MySQL password and create a limited‑privilege DB user.
4. Enable SSL (HTTPS) on the server.
5. Optionally configure a virtual host for `online-shopping.test`.

---

## 🙋‍♀️ Need Help?
If anything is unclear or you run into an issue:
- Double‑check that **Apache** and **MySQL** services are running.
- Verify the database credentials in `includes/db.php`.
- Look at the browser console/network tab for any missing asset errors.
- Feel free to ping me here, and I’ll walk you through the problem.

*Happy coding!* 🎉
