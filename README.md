# Static Data Importer Bundle

A Symfony CLI Bundle for importing json, csv and/or xml directly into a database via an entity object. 

**Install**: 
```bash 
composer require kerrialn/static-data-importer-bundle
```

**Configure**: add json, xml or csv files to `./data` (name this anything you want) root directory in your project
   1. File naming convention `{order number}_{entity name}.{format}` Eg... `10_Blog.json`, `20_category.csv` or `30_User.xml` (entity name is case-insensitive)
   2. Change the import order, with the order number in the file name. 
   3. Add `_SKIP` (case-sensitive) to the filename to skip the file. 
   
**Run**: 

```bash 
bin/console import data/
``` 

Please note: id fields will be ignored and will generate id as per your entity annotation/attribute definition.
