<?php

date_default_timezone_set('UTC');

define("INSTALL_DONE", true);
define("SHOW_CONFIG_READ_ERROR", false);

define("ENVIRONMENT", 'development');

define("SITE_SUBFOLDER", 'date/');
define("SITE_PATH", 'C:\\xampp\\htdocs\\');
define("SITE_PHYSICAL_PATH", SITE_PATH . SITE_SUBFOLDER );
define("SITE_SERVER", 'http://localhost/');
define("MOBILE_SERVER", '');
define("COOKIE_SITE_SERVER", '');

define("SITE_VIRTUAL_PATH", SITE_SERVER . SITE_SUBFOLDER );

define("DB_HOSTNAME", 'localhost');
define("DB_USERNAME", 'root');
define("DB_PASSWORD", '');
define("DB_DATABASE", 'lombardi_dating');
define("DB_PREFIX", 'pg_');
define("DB_DRIVER", "pdo");

define("UPLOAD_DIR", "uploads/");
define("DEFAULT_DIR", "default/");
define("DATASOURCE_ICONS_DIR", "datasource_icons/");

define("FRONTEND_PATH", SITE_PHYSICAL_PATH . UPLOAD_DIR);
define("FRONTEND_URL", SITE_VIRTUAL_PATH . UPLOAD_DIR);

define("GENERATE_BACKTRACE", false);
define("USE_PROFILING", false);
if($_SERVER['REMOTE_ADDR'] == '91.210.252.222') { 
    define("DISPLAY_ERRORS", false);
    }else{
	define("DISPLAY_ERRORS", true);	
	}
	define("ADD_LANG_MODE", false);
	define("DEMO_MODE", false);
	define("CUSTOM_MODE", '');
	define("USE_MEMCACHE", false);
	define("TPL_USE_CACHE", false);
	define("TPL_PRINT_NAMES", false);
	define("TPL_DEBUGGING", false);
	
	define("PATH_TO_IMAGE_MAGIC", "/usr/bin/convert");
	define("USE_IMAGE_MAGIC", false);
	
	/**
	 * Set to true, if you use .htaccess rule to remove $config['index_page'] file 
	  * from the site URLs
	   */
	   define("HIDE_INDEX_PAGE", true);