<?php
/* default settings for session, used by fileman only
 */
//error_log('env:' . print_r($_SERVER, true));

if(!getenv('APP_KEY')) {
	//require_once $_ENV['HOME'] . "/vendor/autoload.php";
	//Dotenv::load($_ENV['HOME']);
	require_once __DIR__ . "/../../../vendor/autoload.php";
	Dotenv::load(__DIR__ . "/../../../");
	//error_log('app_key:' . getenv('APP_KEY'));
}

ini_set('memcached.sess_prefix', getenv('MEMCACHED_SESSION_PREFIX') ?: "sos.sess.key.");
session_start([
	'save_handler' => 'memcached',
	'save_path' => (getenv('MEMCACHED_HOST') ?: '127.0.0.1') . ':' . (intval(getenv('MEMCACHED_PORT')) ?: 11211),
	'name' => getenv('FILEMAN_COOKIE') ?: 'sos_fileman_sess',
]);

