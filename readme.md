# VIP Hash tool

This tool allows WordPress.com users to hash files and mark those files as good or bad. It's intended to help with reviewing code

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
 - The status of the file, good or bad
 
 Usage:
 
     php bin/viphash.php mark file.php tarendai good
 
 
## get

This command takes a hash and returns its status. Optionally it takes a WordPress.com username as an additional filter

Usage:

    php bin/viphash.php get file.php tarendai