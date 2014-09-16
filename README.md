What are these?
=============
Summarized, this set of scripts provides you essentially lightweight LXC containers of some sort. These won't replace `vagrant`, `docker` nor will they make sense outside the software development use case, but they do make pure LXC containers a little more portable and reusable.

Why LXC?
-------------
Tools like Vagrant (+`varnish-lxc`), Docker and Virtualbox are all really great in what they were made for. The benefits of portability and isolation of binaries+configuration, should however not come at the price of performance. And although linux-only, LXC is fast... **bloody fast!** There's simply no virtualization but your *servers* are still isolated, making it easy to reinstall your computer or rebuild it when you changed things for a certain project.

How its organized
-------------
Each directory represents a reusable server doing something. The scripts wrap around `lxc-create`, `lxc-start`, `lxc-stop` and `lxc-destroy` and make starting servers as simple as `./server start`. Once it all ran for the first time and when `provision.sh` did its work, the container template and container will say in `/var` and stay at your disposal with fast rebuild and boot times.

The servers
=============

More coming...