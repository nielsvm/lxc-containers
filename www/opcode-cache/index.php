<?PHP

if (function_exists('opcache_reset')) {
  // https://rtcamp.com/tutorials/php/zend-opcache/
  require "ocp.php";
}
elseif (extension_loaded('apc') && file_exists("/usr/share/doc/php-apc/apc.php")) {
  require "/usr/share/doc/php-apc/apc.php";
}
else {
  echo "No opcode caching extension found, sorry!";
}
