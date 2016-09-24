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

* analysis : Analyzes the current state of this problem
* benchmarking : Benchmarks parts related to the problem
* cleaning : Solves the problem

## Quick Overview

* rewrites:analysis:totals
* rewrites:analysis:history
* rewrites:benchmark:index
* rewrites:benchmark:url-resolve
* rewrites:clean:disabled
* rewrites:clean:yolo

These commands are initially stable, more will follow.
Fore more in-dept information about commands usage, see the [wiki](https://github.com/frosit/magerun-rewritetoolset/wiki)

# Contributing

There are various solutions for this problem and each situation requires a different solution. Feel free to contibute. Create a PR, follow coding standards and don't let Travis spam my inbox. (i don't mind successfull builds)

# Credits

Development of these tools was sponsored by [Byte](http://www.byte.nl)


