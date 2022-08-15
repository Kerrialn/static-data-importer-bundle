# Static Data Importer 

A package for importing json, csv and/or xml directly into a database via an entity object. 

1. **Install**: `composer require kerrialn/static-data-importer`

2. **Configure**: add json, xml or csv files to `./data` (name this anything you want) root directory in your project
   1. File naming convention `{entity name}_{order number}.{format}` Eg... `Blog_10.json`, `category_20.csv` or `User_30.xml` (entity name is case-insensitive)
   2. Change the load order, with the order number in the file name. 
   
3. **Run**: `bin/console import data/` 

