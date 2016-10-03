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

### rewrites:analysis:top

Shows products and categories with the most duplicate rewrites

* stable, documented

__options__

* --limit : limits the amount of top entities to show

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

### rewrites:clean:older-than

Cleans out all rewrites older than x days

* development, documented

__arguments__

* days : x days as number, rewrites older than x days from now are to be removed

__options__

* --dry-run

### rewrites:clean:yolo

Cleans out every duplicate rewrite

* development, documented

__options__

* --dry-run

## Section: Fix

This section contains commands for fixing products with duplicate url_keys

_note_ Early development

### rewrites:fix:products

This command fixes url_keys of products that duplicate by adding a unique suffix to the key.
url_keys can be made unique in 3 ways, using the current duplicate url as the unique url, using the product id or the sku. The first 2 methods are advices and current_url set by default.
This command uses Magento's models load and save function which are relatively slow due to the trigger of single reindex.
This command only runs on the global scope and skips products with url_key configurations in child scopes.

* development | tested, documented

__options__

* --new-suffix : Suffix to append after url-key. accepts: current_url, product_id, sku, default: current_url
* --seperator : Seperator character between url_key and suffix, default: -
* --limit : Limits the amount of products to be fetches als duplicated (testing purposes)
* --dry-run : does not execute product save


## Section: Log

This section is actually redundant and only contains one command. Due to it's nature i'd like to keep it seperated for now.

### rewrites:log:parse

Parses access logs into a json database. Currently only supports the hypernode platform by this specific command:

* unstable, tested hypernode only

`magerun   rewrites:log:parse --file="/var/log/nginx/access.log*"`

the file option can be modified, above example uses glob.

__options (current)__

* --file : path/pattern to access logs (autodetect is buggy)
* --to-db : specify a different json db id for overwriting or testing
* --clean : filters out urls with the validateUrl function, enabled by default
* --webserver : override webserver identification (apache/nginx)
* --platofrom : override platform identification (only hypernode)

#### Features currently

* supports parsing nginx access logs, by single file or globbing
* support plain-text or gzipped access logs
* filters out bad requests by several patterns (e.g. to js/css/img, urls containing: ?=% etc. more patterns and custom patterns will be added, refer to AbstractRewritesCommand.php validateUrl()
* memory management : identifies running out of memory, triggers uniqueing results and garbage collect cycles
* identifies platform and platform available commands (wc, cat)
* identifies apache, nginx, hypernode (apache parsing unfinished)


## Section: url

This section contains commands for adding sources to whitelists. For more information about the whitelisting methodology, refer to the wiki.
In short, we add all url's that are considered active and indexed by search engines to a whitelist. We compare this whitelist to the duplicate rewrites database to secure all rewrites that hold any value.
There are 3 methods of adding urls to the whitelist, one is specified is log:parse.


### rewrites:url:csv

Adds urls to a whitelist json db from CSV source. Adding a google analytics CSV is adviced.

* stable, documented

` magerun rewrites:url:csv --csv google.csv --column Bestemingspagina`

__options__

* --csv : path to csv file
* --column : column that holds the urls

The [ParseCSV](https://github.com/parsecsv/parsecsv-for-php) class is used for autodetection.


### rewrites:url:visitor

Adds urls with a max-age from the log_url_info table to the whitelist. This requires _system_log_enable_log_ to be set to yes in system configuration.

`magerun rewrites:url:visitor`

__options__

* --max-age : The maximum age in days for urls in the visitor log to be added to the whitelist, default: 60

### rewrites:url:whitelist

Combines, filters and compares all urls from sources to the rewrites table for whitelisting important rewrites.
Refer to the wiki for more information about the whitelist methodology which is a safegaurd for valuable rewrites.

* development, tested

`magerun rewrites:url:whitelist`

__options__

* --max-age : maximum age in days for rewrites in core_url_rewrite to be added to the master whitelist for safekeeping, default: 60
* --debug : dumps json in each step for debugging (merging, create segments, query segments)



