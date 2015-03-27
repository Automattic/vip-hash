# VIP Hash tool

This tool allows WordPress.com users to hash files and mark those files as ready or not ready for VIP. It's intended to help with reviewing code, and preventing duplication of work

## Commands

There are 3 commands:

### hash

This command takes a file as a parameter and generates a hash

Usage:

    php bin/viphash.php get file.php

## mark

This command takes 3 parameters:

 - A hash to mark, or a file to hash and mark
 - A WordPress.com username
 - Does this file meet VIP standards? true or false
 
Usage:

    php bin/viphash.php mark file.php tarendai true

 
## get

This command takes a hash and returns its status. Optionally it takes a WordPress.com username as an additional filter

Usage:

    php bin/viphash.php get file.php tarendai
