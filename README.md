Rewrite Toolset
===============

This repository contains a set of magerun commands for solving core url rewrite issues.

| Master  | [![Build Status](https://travis-ci.org/frosit/magerun-rewritetoolset.svg?branch=master)](https://travis-ci.org/frosit/magerun-rewritetoolset)  |
|:--------|:------------------------------------------------------------------------------------------------------------------------------------------|
| Staging | [![Build Status](https://travis-ci.org/frosit/magerun-rewritetoolset.svg?branch=staging)](https://travis-ci.org/frosit/magerun-rewritetoolset) |


__Note: work in progress.__

# Installation

The easiest way to install is by using modman:

    `modman clone https://github.com/frosit/magerun-rewritetoolset.git`

Also git is an option, this could be installed in {magento_root}/lib/n98-magerun/modules or ~/.n98-magerun/modules.
Make sure the directory exists:

* `mkdir -p lib/n98-magerun/modules` or `mkdir -p  ~/.n98-magerun/modules`
    
Then clone it:

* `git clone https://github.com/frosit/magerun-rewritetoolset.git lib/n98-magerun/modules/Rewritetoolset`


# What is the URL Rewrite issue?

The URL rewrite issue a Magento core problem that has been around for years and isn't easy to solve due to various caveats. It basically makes your `core_url_rewrite` table grow over time with unnecessary data.
For more in-depth information see the [wiki](https://github.com/frosit/magerun-rewritetoolset/wiki) 

# Commands

We have several segments of commands.

* analysis : Analyses the current state of this problem
* benchmarking : Benchmarks parts related to the problem to get an indication of impact
* cleaning : Cleans out redundant rewrites
* Fixing : Permanent fixes
* Log / url : commands for preserving important urls

## Quick Overview

* rewrites:analysis:totals
* rewrites:analysis:history
* rewrites:analysis:top
* rewrites:benchmark:indexer
* rewrites:benchmark:site-performance
* rewrites:benchmark:resolve-urls
* rewrites:clean:disabled
* rewrites:clean:older-than
* rewrites:clean:yolo
* rewrites:fix:products
* rewrites:log:parse
* rewrites:url:csv
* rewrites:url:visitor
* rewrites:url:whitelist

This project is in early development stages and contains experimental commands, refer to the command documentation for usage.
Fore more in-dept information about commands usage, see the [wiki](https://github.com/frosit/magerun-rewritetoolset/wiki)

# Contributing

There are various solutions for this problem and each situation requires a different solution. Feel free to contribute in any way. Create a PR, follow coding standards and don't let Travis spam my inbox. (i don't mind successful builds)

# Disclaimer

This project is an effort to provide some tools to help understand and solve this widespread issue. Usage of these commands is at own risk. Always backup and test on development setups before executing at production environments.

# Credits

Development of this toolset was sponsored by [Byte](http://www.byte.nl)


