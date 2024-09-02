# Google-Suggestions-Parser-PHP
Parser of Google search suggestions keywords for SEO

GOOGLE SUGGESTIONS PARSER
--------------------------

The script runs on PHP. For the script to work, it must be placed on the server or local Apache.
The folder in which the script is located must have the rights (attributes) set to 777.
The folders results, temp and temp/keywords must also have the rights (attributes) set to 777.
The script runs in the browser. To run, open `http://path_to_script/index.php`
Do not close the browser until the end of the work.

In the file `settings/keywords.txt` - keywords are added in a column, hints for which need to be selected, for example:
`dollar
buy dollar
buy dollar online
...`

In the file `settings/keys_add.txt` - prefixes are added to keywords, for example
`a
b
c
buy
purchase
d
e
...`
Prefixes can be letters and numbers, as well as words and phrases.
It is better to leave the first line in the list empty, so that the search is also possible without a prefix.

In the file `settings/proxy.txt` - proxies:ports are added in a column like this:
`74.53.15.140:3129
213.171.70.243:8080
108.161.130.154:3128
...`

In the file `settings/treads.txt` - the number of parsing threads is specified, for example:
`300`

In the file `settings/language.txt` - the language (country) is specified, for example:
`en`
or
`ru`
Only one language is specified in one line without spaces.

In the file `settings/timeout.txt` - the maximum proxy response time in seconds is specified, after which the wait for a response from it stops.
Small values ​​speed up the work, for example `15`.

During the work, the script creates the file `results/result.txt` - which will contain the parsing result.
When the work is finished, duplicates are removed from the results list and the results are saved to the file:
`results/result_unic_date.txt`
Old keywords that were used for the last pass are saved to the `temp/keywords` folder, for example `temp/keywords/keywords_bc_date.txt`

The collected database can be run again. The script does this automatically, i.e. the collected keywords are run even deeper and deeper to infinity.

Enjoy! ))

// (c) digg 2014
