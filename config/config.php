<?php

define('APP_NAME', 'Certificate Request Application');
define('DB_DRIVER', 'mysql'); // Use 'mysql' in production. SQLite fallback is used when PDO MySQL is unavailable.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'certificate_requests');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_SQLITE_PATH', __DIR__ . '/../database/database.sqlite');
define('PDF_TEMPLATE_PATH', __DIR__ . '/../pdf/template.pdf');
define('ADMIN_USERNAME', 'adminuser');
define('ADMIN_PASSWORD_HASH', '$2y$12$MOOdwetLmOEveDjCAbDRI.uxEBGzpLFle89gkLdQqSc9uNR5Tyy1S');
define('CSRF_SESSION_KEY', 'csrf_token');
