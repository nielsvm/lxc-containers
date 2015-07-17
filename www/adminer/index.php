<?php
require "/etc/lxc-containervars.php";

/**
 * Define a AdminerSoftware object providing the LXC credentials.
 */
function adminer_object() {
  class AdminerSoftware extends Adminer {

    function name() {
      global $lxc_container_ipv4;
      global $lxc_container_name;
      return "<a href='http://$lxc_container_ipv4' target='_blank' id='h1'>$lxc_container_name</a>";
    }

    function permanentLogin() {
      return "permanent";
    }

    function credentials() {
      global $lxc_mysqlclient_host;
      global $lxc_mysqlclient_user;
      global $lxc_mysqlclient_password;
      return array($lxc_mysqlclient_host, $lxc_mysqlclient_user, $lxc_mysqlclient_password);
    }
  }

  return new AdminerSoftware;
}

/**
 * Fake ?username and load up Adminer.
 */
$_GET['username'] = '';
require "adminer-4.2.1.php";
