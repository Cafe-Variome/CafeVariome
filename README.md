## Cafe Variome
---

This is the repository for Cafe Variome in CodeIgniter 4.  
  
There is a separate repository for documentation [here](https://github.com/CafeVariomeUoL/CafeVariomeDocs).

This application needs to work with the [Cafe Variome Net](https://github.com/CafeVariomeUoL/CafeVariomeNet).

## Installation
---  
### Cloning the repositories:

$ `git clone https://github.com/CafeVariomeUoL/CafeVariomeCI4.git`  

### Changing Owner ship and renaming directories:

$ `mv CafeVariomeCI4/ your_desired_directory/`  
$ `sudo chown $USER:$USER your_desired_directory -R`

### Creating the database:

$ `mysql -u [username] -p`  
$ `CREATE DATABASE cafevariome;`  

### Setting the permission for the writable folder of CodeIgniter:

Set the permission within the root directory of Cafe Variome:
Checking the corresponding user within the Linux distribution with the following command:  

$ `ps aux | egrep '(apache|httpd)'`   

On Ubuntu the Apache user is _www-data_.  

$ `setfacl -m u:www-data:rwx -R writable/ writable/logs writable/session/ writable/cache/`

### Editing configurations in App.php and Database.php

The base URL needs to be set in the system using the following commands:

$ `vim app/Config/App.php`  
public $baseURL=’<URL_TO_ACCESS_CAFEVARIOME>’;

Similarly, the database credentials need to be set using the following commands:  

$ `vim app/Config/Database.php`

> public $default = [  
>               'DSN'      => '',  
>               'hostname' => 'localhost',  
>               'username' => 'root',  
>               'password' => 'Your Password',  
>               'database' => 'cafevariome',  
>               'DBDriver' => 'MySQLi',  
>               'DBPrefix' => '',  
>               'pConnect' => false,  
>               'DBDebug'  => (ENVIRONMENT !== 'production'),  
>               'cacheOn'  => false,  
>               'cacheDir' => '',  
>               'charset'  => 'utf8',  
>               'DBCollat' => 'utf8_general_ci',  
>               'swapPre'  => '',  
>               'encrypt'  => false,  
>               'compress' => false,  
>               'strictOn' => false,  
>               'failover' => [],  
>               'port'     => 3306,  
>        ];

### Installing dependencies through Composer:  

In the root directory of Cafe Variome where the composer.json resides, run the below command:

$ `composer install`

### Importing Cafe Variome Database and setting permissions through Composer

In the root directory of Cafe Variome where composer.json resides run the following command:

$ `composer CVInstall`

At this step you will be prompted to enter your installation key which has been given to you prior to installing the software. 
Also, you will need to enter the URL to the Cafe Variome Net service you wish to use. Please note that you need to point Cafe Variome to that Cafe Variome Net instance which has issues your installation key.
