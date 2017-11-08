# VIP Hash tool

This tool allows WordPress.com users to hash files and mark those files as ready or not ready for VIP. It's intended to help with reviewing code, and preventing duplication of work

## Building and Installing

Run `php bin/compile` to build the `viphash.phar` file, rename to `viphash`, flag as executable, and place somewhere you can access it from anywhere. Be sure to repeat this process with newer versions of the tool.

TLDR:

```
git clone git@github.com:Automattic/vip-hash.git
cd vip-hash
php bin/compile
chmod +x viphash.phar
mv viphash.phar /usr/local/bin/viphash
```

## Commands

The tool has the following commands:

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


## scan

This command takes a folder as its parameter and outputs a json object showing data about the files and folders inside, e.g. at the time of writing, this is the output for this project:


```
❯ php bin/viphash.php scan .
{
    "folder": ".",
    "contents": [
        {
            "folder": ".\/bin",
            "contents": [
                {
                    "hash": "40ab5b7d5bdefb3afae42384ae676c17bd520c43",
                    "status": "unknown",
                    "file": ".\/bin\/viphash.php"
                }
            ]
        },
        {
            "folder": ".\/src",
            "contents": [
                {
                    "folder": ".\/src\/automattic",
                    "contents": [
                        {
                            "folder": ".\/src\/automattic\/vip",
                            "contents": [
                                {
                                    "folder": ".\/src\/automattic\/vip\/hash",
                                    "contents": [
                                        {
                                            "hash": "9233ef1ee996a35a642f8c67a73ce18890dd9243",
                                            "status": "unknown",
                                            "file": ".\/src\/automattic\/vip\/hash\/Application.php"
                                        },
                                        {
                                            "hash": "f9c1bc73f0797c97d98206eb9557ca4f09990123",
                                            "status": "unknown",
                                            "file": ".\/src\/automattic\/vip\/hash\/DataModel.php"
                                        },
                                        {
                                            "hash": "ccc6e6337624f98846fb6fcaec8bc1a076828a86",
                                            "status": "unknown",
                                            "file": ".\/src\/automattic\/vip\/hash\/HashRecord.php"
                                        },
                                        {
                                            "folder": ".\/src\/automattic\/vip\/hash\/console",
                                            "contents": [
                                                {
                                                    "hash": "f121c10c3b3b88afde90c5a74f69815671b94c28",
                                                    "status": "unknown",
                                                    "file": ".\/src\/automattic\/vip\/hash\/console\/GetCommand.php"
                                                },
                                                {
                                                    "hash": "94d7a19a2ebb78b0297946248a8c1cd9372e10d1",
                                                    "status": "unknown",
                                                    "file": ".\/src\/automattic\/vip\/hash\/console\/HashCommand.php"
                                                },
                                                {
                                                    "hash": "5b74dde150dc29ccb7bc64fdf1fe3f4366c54423",
                                                    "status": "unknown",
                                                    "file": ".\/src\/automattic\/vip\/hash\/console\/MarkCommand.php"
                                                },
                                                {
                                                    "hash": "ab143379283f6078d430452272eccafec685aacf",
                                                    "status": "unknown",
                                                    "file": ".\/src\/automattic\/vip\/hash\/console\/ScanCommand.php"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    "hash": "43c77b6ebf57fa20b39a1178849ed3553659ca41",
                    "status": "unknown",
                    "file": ".\/src\/bootstrap.php"
                }
            ]
        }
    ]
}
```
