# Static Data Importer 

1. install: `composer require kerrial/static-data-importer`
2. configure: add json, xml or csv files to `./data` root directory in your project
   1. File naming convention `{entity name}_{order number}.{format}` Eg... `Blog_10.json`, `category_20.csv` or `User_30.xml` (entity name is case-insensitive)
   2. Change the load order, with the order number in the file name. 
3. run: `vendor/bin/sdi import data` 

