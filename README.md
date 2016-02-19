# lxc-containers
This repository equips your Linux machine with several utility *containers* of some sort, built entirely on LXC. Intended mainly for software developers, this allows you to run LAMP stacks and variations thereof on your computer without littering your desktop installation and without requiring heavily bloated virtualization solutions.

### LXC?
LXC is a thin layer on top of Linux cgroups, allowing you to 'slice up' your computer without full X86 emulation, but mere resource splitting. Containers live in `/var/lib/lxc` and have their own (tiny) root hierarchies, essential system binaries but share your main kernel instance.

You can compare this project a little bit with `vagrant-lxc` but this is intentionally simple, highly portable (git clone) and aimed at coders who need something **seriously fast**. Virtualization can be fast but isn't always necessarily so, and when its just a LAMP stack or ruby/python binaries of some version you need, this gives you the best of both.

# Installation
All you need is *Ubuntu 14.10/utopic* and LXC installed (`apt-get install lxc-templates`) on your computer.

To prevent any common pitfalls, make sure to clone this somewhere within (and/or underneath) your Linux home directory and as your own Linux user. Also make sure to uninstall any of the software the container you are going to use provides, so that for instance Apache won't claim a TCP port your main machine uses. It makes sense to keep `mysql-server` on your host installed and to use the containers that don't ship MySQL, as this makes it easy to switch containers but not databases.

```
git clone https://github.com/nielsvm/lxc-containers.git
cd lxc-containers/
./server <CONTAINERNAME>
```

# Inside this goodiebag

### apache-memcached-php55, drupal8
*Debian 7*, *Apache 2*, *Memcached*, *Drush*, *Composer*, *PHP 5.5*

Simple webserver that mounts your home directory into the container, and runs Apache as your user. PHP is mainly left default with `opcache` set to `128m` and various Xdebug settings enabled and memcached at `64m`. Once your container is fully started and prompts you with a login terminal, point your browser at http://10.0.2.10/ for further instructions.

### apache-memcached-mysql-php55, drupal8my
*Debian 7*, *Apache 2*, *MySQL*, *Memcached*, *Drush*, *Composer*, *PHP 5.5*

Simple webserver that mounts your home directory into the container, and runs Apache as your user. PHP is mainly left default with `opcache` set to `128m` and various Xdebug settings enabled and memcached at `64m`. This container ships with MySQL installed, which you can connect to with `root`/`root`. Once your container is fully started and prompts you with a login terminal, point your browser at http://10.0.2.10/ for further instructions.

### apache-memcached-php53, drupal7
*Debian 7*, *Apache 2*, *Memcached*, *Drush*, *Composer*, *PHP 5.3*

Simple webserver that mounts your home directory into the container, and runs Apache as your user. PHP is mainly left default with `apc` set to `128m` and various Xdebug settings enabled and memcached at `64m`. Once your container is fully started and prompts you with a login terminal, point your browser at http://10.0.2.10/ for further instructions.

### apache-memcached-mysql-php53, drupal7my
*Debian 7*, *Apache 2*, *MySQL*, *Memcached*, *Drush*, *Composer*, *PHP 5.3*

Simple webserver that mounts your home directory into the container, and runs Apache as your user. PHP is mainly left default with `apc` set to `128m` and various Xdebug settings enabled and memcached at `64m`. This container ships with MySQL installed, which you can connect to with `root`/`root`. Once your container is fully started and prompts you with a login terminal, point your browser at http://10.0.2.10/ for further instructions.

### bare
*Debian 7*

Empty Debian installation with apt-get and git at your disposal, the `/etc` directory has been tracked in GIT to make change tracking more convenient.

# Build your own
Each directory represents one container providing something. Every script (ending on `.sh`) inside it will get installed into the container as `/etc/init.d/SCRIPTNAME` service and marked to start at boot. All the shell script provides is a `sysvinit` compatible script but most bundled also work with a *phased provisioning mechanism*, what that means is that everything gets installed in steps which greatly helps writing your own. The best way to start is to copy a directory and to configure `config.ini`, adapt `provision.sh` and to start tweaking and running `./server CONTAINER` and `./server CONTAINER destroy` until its perfect and well-tested.
