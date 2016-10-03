Rewrite Toolset Command Overview
================================

This page gives insights in the available commands and their respective options.

# Options & Arguments

We have options and arguments defined by section/scope and higher levels like magento's scope configuration.

__Top-level options__

* --log-statistics : saves the statistics as a json file in var/rewrite_toolset/stats - for use in further data statistics.
* --share-statistics : sends the statistics to a server or database for further analysis, this endpoint can be overridden using magerun configuration

_above options accept no values_
_note_ Options are becoming obsolete and do not have a function everywhere despite listing in command info

__Rewrite-level options__

* --store "somevalue" : pre-selects store(s), skipping the prompt, usefull when chaining commands
    * accepts: string, int, array
        * an array can be given comma seperated and accepts strings and integers
    * all stores: add "all" for selecting all stores


# Commands

The following commands are here by section.

## Section: Analysis

The analysis commands are all about finding out and determining the scope.

__Options__

The following options are available for the analysis commands.

* --save : saves statistics as a HTML file using twig templates in var/rewrite_tools/report

### rewrites:analysis:totals

The totals command calculates the amount of duplicate rewrites per store and the percentage compared to unique rewrites.
This gives insights in the scope of the problem. This command has a relatively low footprint compared to others.

* stable, documented

### rewrites:analysis:history

The history command generates a table (preferably HTML report) showing a timeline of at what moment, how many duplicate rewrites we're created.

* stable, documented

__Options__

* --limit : limits the amount of rewrites parsed for history per store view. Use this in case of timeouts.

__note__

Easily results in timeouts and memory shortages if the rewrite table is large. When in trouble, increase these limits or use the `rewrites:clean:disabled` command to clear out some garbage.

## Section: Benchmark

This section is all about testing / discovering impact.

### rewrites:benchmark:indexer

Runs the catalog_url full reindex a couple of time to measure indexation times and increase in rewrites.

* stable, documented

__options__

* --limit : the amount of reindex action to run, defaults to 10
* --microtime : outputs the runtime in microtime instead of time

### rewrites:benchmark:site-performance

Tests site performance by crawling URls. Command is becoming obsolete

* obsolete, requires refactor

### rewrites:benchmark:resolve-urls

Tests the time to resolve urls by generating a sitemap.

* stable, needs documentation


## Section: Clean

This section contains commands for cleaning out duplicates.

_note_ There's much development in this section

### rewrites:clean:disabled

Cleans out rewrites from disabled stores and products.

* needs testing, undocumented

__options__

* --limit @dep 
* --dry-run : does not execute delete querys

### rewrites:clea:yolo

Cleans out every duplicate rewrite

* tested, documented

__options__

* --dry-run
