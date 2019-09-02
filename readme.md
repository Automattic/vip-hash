# VIP Hash tool

This tool allows WordPress.com users to hash files and mark those files as ready or not ready for VIP. It's intended to help with reviewing code, and preventing duplication of work

## Building and Installing

Run `php bin/compile` to build the `viphash.phar` file, rename to `viphash`, flag as executable, and place somewhere you can access it from anywhere. Be sure to repeat this process with newer versions of the tool.

TLDR:

```
git clone git@github.com:Automattic/vip-hash.git
cd vip-hash
composer install
cd bin
./install.sh
```

Note that the hash tool will try to store an SQLite database and config in the `.viphash` folder of your home directory, and store its information in that location.

If you encounter a message similar to `Failed to compile phar:`, you will need to set `phar.readonly = Off` in `php.ini` ( you can locate `php.ini` by running `php --ini`) -- this only needs doing once. Make sure to have the `curl`, `pdo` and `pdo-sqlite` addons installed to PHP as well.

## Commands

The tool has the following commands:

### hash

This command takes a file as a parameter and generates a hash

Usage:

    php bin/viphash.php get file.php

## mark

This command marks a file as passing or failing. It uses the username from your configuration, so you may need to run `viphash config set username` first.

This command takes 2 parameters:

 - A hash to mark, or a file to hash and mark
 - Does this file meet VIP standards? true or false

You can also pass 2 optional parameters, as either a string or a path to a file:

 - Offending item if marked as bad
 - Explanation in a human-readable format

Usage:

```shell
viphash mark file.php true
```

 
## get

This command takes a hash and returns its status. Optionally it takes a WordPress.com username as an additional filter

Usage:

```shell
    php bin/viphash.php get file.php tarendai
```

Or if installed to path:

```shell
    viphash get file.php tarendai
```

## scan

With the `format` parameter this can be used to auto-generate feedback. For example:

```shell
viphash scan . --format="markdown" > feedback.md
```

Will generate a markdown file with every issue in codeblocks with descriptions, ordered and separated by filenames in headings. It also adds some client friendly copy, and some prompts to make general notes. Great for attaching to Zendesk tickets, or copy pasting into github issues

This command takes a folder as its parameter.

Alternatively, passing `--format="json"`will output a json object showing data about the files and folders inside, e.g. at the time of writing, this is the output for this project:


```shell
❯ viphash scan --format="json" .
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

### Syncing Data

For sending and recieving data from a remote source, the hash tool tries to follow the `git` model of remotes that you add then sync to. The hash tool can add remote hosts using OAuth1, e.g.

```shell
viphash remote add origin https://example.com secret key
viphash sync origin
```
