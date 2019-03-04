<?php
// HTTP
define('HTTP_SERVER', 'http://eaglesvictoria.com/');

// HTTPS
define('HTTPS_SERVER', 'https://eaglesvictoria.com/');

// DIR
define('DIR_APPLICATION', '/var/www/html/eaglesvictoria/catalog/');
define('DIR_SYSTEM', '/var/www/html/eaglesvictoria/system/');
define('DIR_IMAGE', '/var/www/html/eaglesvictoria/image/');
define('DIR_STORAGE', '/var/www/html/eaglesvictoria/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'uZX5d18cm4');
define('DB_DATABASE', 'eaglesvictoria');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');