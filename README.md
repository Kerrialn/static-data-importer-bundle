# Static Data Importer Bundle

A Symfony Bundle for importing json, csv and/or xml directly into a database via an entity object. 

**Install**: 
```bash 
composer require kerrialn/static-data-importer-bundle
```

**Configure**: add json, xml or csv files to `./data` (name this anything you want) root directory in your project
   1. File naming convention `{entity name}_{order number}.{format}` Eg... `Blog_10.json`, `category_20.csv` or `User_30.xml` (entity name is case-insensitive)
   2. Change the load order, with the order number in the file name. 
   
**Run**: 

```bash 
bin/console import data/
``` 

Please note: id fields will be ignored and will generate id as per your entity annotation/attribute definition.