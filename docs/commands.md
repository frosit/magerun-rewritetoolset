Rewrite Toolset Command Overview
================================

This page gives insights in the available commands and their respective options.

# Options & Arguments

We have options and arguments defined by section/scope and higher levels like magento's scope configuration.

__Top-level options__

* --log-statistics : saves the statistics as a json file in var/rewrite_toolset/stats - for use in further data statistics.
* --share-statistics : sends the statistics to a server or database for further analysis, this endpoint can be overridden using magerun configuration

_above options accept no values_

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

### rewrites:analysis:history

The history command generates a table (preferably HTML report) showing a timeline of at what moment, how many duplicate rewrites we're created.

__Options__

* --limit : limits the amount of rewrites parsed for history per store view. Use this in case of timeouts.

__note__

Easily results in timeouts and memory shortages if the rewrite table is large. When in trouble, increase these limits or use the `rewrites:clean:disabled` command to clear out some garbage.



