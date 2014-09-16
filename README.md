What is this and why should I care?
-------------
I don't know if you should care honestly. But summarized, this is a set of scripts we wrote that essentially provide (reusable) servers of some sort. Mostly webservers, these scripts would wrap around commands like `lxc-create`, `lxc-start`, `lxc-stop` and `lxc-destroy` and by doing so, making our lightweight server provisioning scripts all a little more usable and reusable.

The servers are not intended as per-project instances nor are they meant to replace tools like `vagrant` or `virtualbox`. However, LXC (linux only) is fast.. **bloody fast** as it is not virtualization but a mere compartimization of system resources and isoliation of binaries and configuration. The scripts allow the containers to be disposable making them more portable across systems, as long as home directories are kept.

The ``provision.sh`` scripts for each server, are deliberately simplistic (but organized) shell scripts so that its easy for everyone to change and adapt to their specific needs. Puppet, chef and even Vagrant are pretty much overkill for the usecases we needed.

{more coming}
=============