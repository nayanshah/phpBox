Welcome to PHP-GTK 2.0.1 for Windows
================================

The win32 binary distribution for PHP-GTK 2 is a zip file with this structure:

php-gtk2		->	PHP binary files - standard build from php.net
			->	GTK+ 2.12.8 runtime environment
php-gtk\ext		->	PHP-GTK2 extension
php-gtk\demos		->	a few samples to demonstrate PHP-GTK usage
php-gtk\debug        	->	debug symbols for PHP-GTK 2
php-gtk\etc
php-gtk\lib
php-gtk\share		->	Additional GTK+ 2.12.9 runtime files

Licensing:
=========

Both PHP-GTK and GTK+ are covered by the LGPL license - PHP is covered by the PHP license.

You may obtain the sources for PHP either by downloading the non-binary
distribution tarball at http://php.net/download.php or via the php.net CVS
repository following the instructions on the same page.

You may obtain the sources for PHP-GTK 2 either by downloading the non-binary
distribution tarball at http://gtk.php.net/download.php or via the php.net CVS
repository following the instructions on the same page.

You may obtain the sources for GTK+ by downloading the source distribution
tarballs from ftp://ftp.gtk.org.

This distribution of PHP-GTK 2 was built against GTK+ 2.12.9 binaries from gtk.org


How to install PHP-GTK 2:
========================

Unzip the archive in a place of your choice. It will create a directory named
php-gtk2 containing everything you need.

Check the php-cli.ini in a text editor and make sure the settings there are sane for your system. You
may add further PHP-specific settings anywhere between [PHP] and [PHP-GTK].

The version of PHP included is 5.2.6 Non-thread safe and is a standard non-thread safe build

How to use PHP-GTK 2:
====================

PHP-GTK applications can be started from the command line (or a shortcut) with this syntax:

	php demos\phpgtk2-demo.php

Using Additional Extensions:
==========================

You may use additional php extensions from php.net compiled to match this php.exe binary

For additional extensions please visit http://php.net/downloads and get the NON-THREAD SAFE (nts)
downloads of PHP 5.2.6 - the regular zip or the PECL pack will work

For additional php-gtk extensions please visit http://gtk.php.net/download.php
and get the php-gtk-win32-extensions-pack-nts.zip
It includes both extensions and the required gtk runtime files