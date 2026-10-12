# Inventory Management System — Laravel + MySQL + XAMPP

This folder is an **overlay**: it contains every application file (migrations, models, controllers,
middleware, services, views, routes, seeders, tests). You drop it onto a fresh Laravel project.

**Requirements (Windows):** XAMPP with **PHP 8.2+** (Laravel 11/12), Composer, and an internet connection
(Bootstrap, Bootstrap Icons and Chart.js load from a CDN).

---
## 1. Setup on Windows (step by step)

1. **Install XAMPP** and open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and **MySQL**. Both names turn green and show port numbers
   (Apache 80/443, MySQL 3306). Open `http://localhost` — the XAMPP page confirms Apache works.
3. Add PHP to PATH so `php` works in a terminal: add `C:\xampp\php` to the *Path* environment variable,
   then open a new Command Prompt and run `php -v` (must show 8.2 or higher).
4. Open **phpMyAdmin**: `http://localhost/phpmyadmin` → *New* → name `inventory_management`,
   collation `utf8mb4_unicode_ci` → *Create*.
5. **Create the Laravel project** *outside* `htdocs` (safer — `.env` is never web-accessible):
   ```bat
   cd C:\
   mkdir projects
   cd projects
   composer create-project laravel/laravel inventory_management
   cd inventory_management
   ```
6. **Copy this overlay** into `C:\projects\inventory_management`, merging folders and **replacing** files when
   asked (`bootstrap/app.php`, `app/Models/User.php`, `app/Providers/AppServiceProvider.php`,
   `database/seeders/DatabaseSeeder.php`, `routes/web.php`, `tests/Feature/ExampleTest.php`).
7. **Configure `.env`** (edit the existing file):
   ```env
   APP_NAME="Inventory System"
   APP_URL=http://localhost:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   (XAMPP's default `root` user has an empty password. Optionally add `ADMIN_PASSWORD=` / `STAFF_PASSWORD=`
   to choose the seeded demo passwords.)
8. **Generate the key, migrate, seed:**
   ```bat
   php artisan key:generate
   php artisan config:clear
   php artisan migrate --seed
   ```
9. **Create your own administrator securely** (password is typed hidden, never stored in code):
   ```bat
   php artisan inventory:create-admin
   ```
   Then **delete or change the demo accounts** (see below) before real use.
10. **Run:** `php artisan serve` → open **http://localhost:8000**.
11. **Verify:** log in, add a product, record a stock-in, check *History* and phpMyAdmin → `stock_transactions`.

**Demo accounts (from the seeder):** `admin@example.com` / `Admin#12345` and `staff@example.com` / `Staff#12345`.
Change them from *Users* → edit (blank password = unchanged), or deactivate them.

### Optional: serve through Apache instead of `artisan serve`
Never point Apache at the project root — only at `public`.
1. In `C:\xampp\apache\conf\extra\httpd-vhosts.conf` add:
   ```apache
   <VirtualHost *:80>
       ServerName inventory.test
       DocumentRoot "C:/projects/inventory_management/public"
       <Directory "C:/projects/inventory_management/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Add `127.0.0.1 inventory.test` to `C:\Windows\System32\drivers\etc\hosts` (run Notepad as Administrator).
3. Restart Apache, set `APP_URL=http://inventory.test`, browse to `http://inventory.test`.

---
## 2. Troubleshooting

| Problem | Fix |
|---|---|
| **Apache won't start / port 80 or 443 in use** (Skype, IIS, World Wide Web Publishing Service, another web server) | Stop the other service, or Control Panel → Apache **Config → httpd.conf**: change `Listen 80` to `Listen 8080` (and `ServerName localhost:8080`); change 443 in `httpd-ssl.conf`. `artisan serve` does not need Apache at all. |
| **MySQL won't start / port 3306 in use** | Another MySQL service is running — stop it in *services.msc*, or change the port in `my.ini` and set `DB_PORT` in `.env`. If it crashes after power loss, back up `C:\xampp\mysql\data` before repairing. |
| `SQLSTATE[HY000] [2002] No connection could be made` | MySQL isn't running, or `DB_HOST`/`DB_PORT` are wrong. Use `127.0.0.1`. |
| `SQLSTATE[HY000] [1049] Unknown database` | Create `inventory_management` in phpMyAdmin; check `DB_DATABASE`. |
| `Access denied for user 'root'` | Fix `DB_USERNAME` / `DB_PASSWORD` in `.env`; then `php artisan config:clear`. |
| `.env` changes ignored | `php artisan config:clear` (and restart `artisan serve`). |
| `could not find driver` / missing extension (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`) | Edit `C:\xampp\php\php.ini`, remove the `;` before `extension=pdo_mysql` (etc.), restart Apache / the terminal. Check with `php -m`. |
| Composer: "requires php ^8.2" | Update XAMPP to a PHP 8.2+ build. |
| Composer SSL / memory / timeout errors | Enable `extension=openssl` and `curl`; run `composer clear-cache`; retry. |
| Migration fails "Specified key was too long" | Not expected on modern MySQL/MariaDB with Laravel 11+. If it occurs, upgrade XAMPP's MariaDB. |
| Migration partly ran | `php artisan migrate:fresh --seed` (**erases all data** — development only). |
| `No application encryption key` | `php artisan key:generate`. |
| 419 Page Expired | Session/CSRF issue: clear browser cookies, `php artisan config:clear`. |
| Page unstyled / charts missing | The browser can't reach the CDN. Check your connection (or host the assets locally). |

---
## 3. Architecture notes
* **Stock integrity:** `StockService::record()` locks the product row (`lockForUpdate`), validates, updates
  `products.quantity` and inserts the `stock_transactions` row inside **one DB transaction**.
  Quantity is never edited directly (the product form shows it read-only).
* **Audit trail:** `StockTransaction` throws on update/delete; FKs use `restrictOnDelete`, so products/users with
  history cannot be deleted. Categories/suppliers in use are blocked from deletion by controller checks.
* **Security:** hashed passwords, session regeneration, login throttling, CSRF on every form, `role:admin`
  middleware on admin routes (URL typing returns **403**), deactivated users are logged out on their next request,
  admins cannot change their own role or deactivate themselves, CSV export neutralises formula injection.
* **Low-stock:** computed live from `quantity <= min_stock`, so alerts update the instant stock changes.
* Currency symbol is `₱`; search/replace it in the views to change it.

### Roles
| Feature | Administrator | Staff |
|---|:--:|:--:|
| Dashboard, history, low-stock | ✔ | ✔ |
| View products & suppliers | ✔ | ✔ |
| Stock-in / Stock-out | ✔ | ✔ |
| Manage products, categories, suppliers | ✔ | ✖ |
| Reports & CSV export | ✔ | ✖ |
| User management | ✔ | ✖ |

(If you need finer "assigned permissions" for staff, wrap the stock routes in an additional middleware or Gate.)

---
## 4. Test cases

Automated: `php artisan test` (uses in-memory SQLite — enable `extension=pdo_sqlite` in `php.ini` if missing).

| # | Test case | Steps | Expected result |
|---|---|---|---|
| 1 | Login success | Valid email/password | Redirect to dashboard |
| 2 | Login failure | Wrong password | Error shown, still logged out |
| 3 | Logout | Click Logout | Back at login; dashboard URL redirects to login |
| 4 | Guest access | Open `/products` logged out | Redirect to login |
| 5 | Create product | Fill valid form | Appears in list; opening stock creates a Stock-In record |
| 6 | Duplicate SKU | Reuse an SKU | Validation error, nothing saved |
| 7 | Required fields | Submit empty product form | Per-field error messages |
| 8 | Edit product | Change price | Updated; stock unchanged |
| 9 | Delete product (no history) | Delete → confirm modal | Removed |
| 10 | Delete product (with history) | Delete | Blocked with error message |
| 11 | Category delete in use | Delete a category having products | Blocked; button disabled |
| 12 | Category unique name | Add existing name | Validation error |
| 13 | Supplier CRUD | Add / edit / search / delete unused | Works; used supplier blocked |
| 14 | Stock-in | Product stock 10, receive 5 | Stock 15; history shows 10 → 15 |
| 15 | Stock-out | Stock 15, release 4, reason Sale | Stock 11 |
| 16 | Over-release | Release more than available | Error "Insufficient stock"; no change |
| 17 | Invalid quantity | 0, negative, text | Validation error |
| 18 | Low-stock alert | Release until qty ≤ min | Dashboard + Low Stock page list it instantly |
| 19 | Out-of-stock | Release to 0 | Red "Out of stock" badge, counted on dashboard |
| 20 | Restock clears alert | Stock-in above minimum | Product leaves low-stock list |
| 21 | Search & filter | Name/SKU/barcode, category, status | Only matching rows |
| 22 | History filters | Type, date range, sorting | Correct rows/order |
| 23 | Reports | Each type + date range | Numbers match history; CSV downloads; Print layout hides sidebar |
| 24 | Staff restrictions | Staff opens `/users`, `/reports`, `/categories` | 403 Forbidden; menu hides them |
| 25 | Self-protection | Admin edits own role / deactivates self | Ignored / blocked |
| 26 | Deactivated user | Deactivate staff, staff clicks anything | Logged out; cannot log in |
| 27 | Password reset | Admin edits user, sets new password | User logs in with new password |
| 28 | Persistence | Stop/restart Apache, MySQL, PC | All data still present |
| 29 | Responsive | Resize to phone width | Sidebar becomes ☰ menu; tables scroll |
