# NOC Trouble Ticket System (PHP Edition)

A complete PHP/MySQL web application for managing Network Operations Center (NOC) trouble tickets. This is a conversion of the original Google Apps Script version into a self-hosted PHP application.

---

## Features

- **Dashboard** – live counts of total, open, in-progress, and closed tickets
- **Ticket list** – searchable and filterable by status, vendor, and regional
- **Create tickets** – with all required NOC fields
- **Edit tickets** – update status, description, root cause, and restoration action
- **Progress log** – append timestamped notes to any ticket
- **Close tickets** – automatically sets resolved time and logs closure
- **Delete tickets** – with confirmation prompt
- **Responsive design** – works on desktop and mobile

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ≥ 8.0 |
| MySQL / MariaDB | ≥ 5.7 / ≥ 10.3 |
| Web server | Apache / Nginx (or PHP built-in server for development) |

---

## File Structure

```
noc-ticket-system/
├── index.php                # Main application entry point
├── config.php               # Database configuration (create from config.example.php)
├── config.example.php       # Configuration template
├── database.sql             # Database schema & seed data
├── api/
│   ├── tickets.php          # Ticket CRUD REST API
│   └── options.php          # System options API (regional, vendor, status)
├── includes/
│   ├── db.php               # PDO database connection (singleton)
│   └── functions.php        # Shared helper functions
└── README.md
```

---

## Setup Instructions

### 1. Clone / download the repository

```bash
git clone https://github.com/ewapriyatna/noc-ticket-system.git
cd noc-ticket-system
```

### 2. Create the database

Log in to MySQL / MariaDB and run the provided schema:

```bash
mysql -u root -p < database.sql
```

Or from the MySQL shell:

```sql
SOURCE /path/to/noc-ticket-system/database.sql;
```

This creates:
- Database `noc_ticket_system`
- Table `tickets`
- Table `regional_options` (pre-seeded with 9 Indonesian regions)
- Table `vendor_options` (pre-seeded with 8 common vendors)

### 3. Configure the database connection

```bash
cp config.example.php config.php
```

Edit `config.php` and set your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'noc_ticket_system');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

Set `APP_DEBUG` to `false` in production to hide detailed error messages.

### 4. Deploy to a web server

**Apache** (with `mod_php` or `php-fpm`):  
Place all files inside your document root (e.g. `/var/www/html/noc-ticket-system/`).

**Nginx** – configure a `fastcgi_pass` block pointing at `php-fpm`.

**Local development** (PHP built-in server):

```bash
php -S localhost:8080 -t /path/to/noc-ticket-system
```

Then open [http://localhost:8080](http://localhost:8080) in your browser.

---

## API Reference

All endpoints return JSON: `{ "success": bool, "data": ..., "message": "..." }`.

### Options

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/options.php` | Returns regional, vendor, and status lists |

### Tickets

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/tickets.php` | List all tickets (optional: `?status=`, `?vendor=`, `?regional=`, `?search=`) |
| GET | `/api/tickets.php?id={id}` | Get single ticket |
| POST | `/api/tickets.php` | Create ticket (form-encoded or JSON body) |
| PUT | `/api/tickets.php?id={id}` | Update ticket |
| DELETE | `/api/tickets.php?id={id}` | Delete ticket |

#### Create ticket – required fields

`tt_customer`, `tt_tbg`, `tt_description`, `device_segment`, `regional`, `vendor`, `segment_problem`, `cid`, `start_time`

#### Update ticket – accepted fields

`status`, `tt_description`, `root_cause`, `responsibility`, `problem_coordinate`, `restoration_action`, `progress_update`

---

## Security Notes

- All database queries use **PDO prepared statements** to prevent SQL injection.
- User-supplied HTML output in `index.php` is escaped with `escHtml()` on the client side.
- Set `APP_DEBUG = false` in `config.php` before deploying to production.
- Restrict direct access to `config.php` in your web-server configuration (e.g. deny access in `.htaccess`).
- Consider adding HTTP authentication or a session-based login if the system will be publicly accessible.

---

## License

MIT
