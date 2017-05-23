Welcome to the Dead Code identification toolset, this toolset can be used together
with the dead files plugin for Eclipse and the treemap visualization.

## Configuration

Please add a config.yml file in the root directory with
the following contents:
```yaml
options:
  dsn: mysql:host=servernamehere;dbname=databasenamehere
  username: usernamehere
  password: passwordhere
```
You can also put this information in ~/.deadrc for user based configuration
or in /etc/dead.conf for systemwide configuration.

Run make to build dead.phar. dead.phar can be installed by running
make install. Then the systemwide command dead will be available.

You have to add a table includes to the database databasenamehere. These
names are not required but then you have to configure them or pass them as
a commandline parameter. If you want to monitor multiple applications, use
the application name instead of include.

The create query is:
```sql
CREATE TABLE `includes` (
 `file` varchar(255) NOT NULL,
 `count` bigint(20) NOT NULL,
 `first_hit` timestamp NULL default NULL,
 `last_hit` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
 `added_at` timestamp NULL default NULL,
 `deleted_at` timestamp NULL default NULL,
 `changed_at` timestamp NULL default NULL,
 PRIMARY KEY  (`file`)
)
```
To let PHP add data to the measuring a file has to be added to the server
(note: this is server wide, do not polute your application with this file).

append.php (or your own name)
```php
<?php 
// push all data to the browser
@ob_flush(); 
@flush(); 

/* DYNAMIC LIVE FILE ANALYSIS */ 
$db = mysql_connect('servernamehere','usernamehere','passwordhere'); 
mysql_select_db('databasenamehere',$db); 

$files = implode('\',\'',get_included_files()); 

$query = "UPDATE aurora SET count = count + 1, first_hit = if( first_hit IS NULL, NOW(), first_hit)  WHERE file IN ('$files')"; 
mysql_query($query,$db); 
mysql_close($db);
```

Then the php.ini for the server has to be changed to let php execute this code always after each request.
If you have a sperate php.ini for CLI also change that file if you want CLI use of your application in the
measurements.

php.ini
```ini
[php]

... lots of directives ...

; Automatically add files after PHP document.
; http://php.net/auto-append-file
auto_append_file="/your/path/to/append.php"
```

If you have any questions or need guidance using the toolset feel free to contact me using 
hidde@hostnet.nl.
