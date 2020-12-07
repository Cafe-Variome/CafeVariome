## Cafe Variome
---

This is the repository for Cafe Variome in CodeIgniter 4. It requires php 7.2 or higher. There is a separate repository for documentation [here](https://github.com/CafeVariomeUoL/CafeVariomeDocs).

This application needs to work with the [Cafe Variome Net](https://github.com/CafeVariomeUoL/CafeVariomeNet).

## Installation
---  
### Cloning the repositories:

$ `sudo git clone https://github.com/CafeVariomeUoL/CafeVariomeCI4.git`  
$ `sudo git clone https://github.com/CafeVariomeUoL/CafeVariomeNet.git`

### Changing Owner ship and renaming directories:

$ `sudo mv CafeVariomeCI4/ cvci4/`  
$ `sudo mv CafeVariomeNet/ cvnet/`  
$ `sudo chown $USER:$USER cv* -R`

### Creating data bases for cafe variome and cvnet:

$ `mysql -u root -p`  
$ `CREATE DATABASE cafevariome;`  
$ `CREATE DATABASE cafevariomenet;`

The cafevariomenet database must be populated with the following command:

$ `mysql -u root -p cafevariomenet < cafevariomenet-schema.sql`

### Setting the permission for the writable folder of codeigniter:

Set the permission within the root directory of cvci4 and cvnet:
Checking the corresponding user within the Linux distribution with the following command:  
$ `ps aux | egrep '(apache|httpd)'`  
On ubuntu the apache user is www-data  
$ `sudo setfacl -m u:www-data:rwx -R writable/ writable/logs writable/session/ writable/cache/`

### Editing configurations in App.php and Database.php

In each directories of cvci4 and cvnet, set the base url as follows in App.php

$ `vim app/Config/App.php`  
public $baseURL=’http://localhost/cvci4/’;

In each directories of cvci4 and cvnet, set the credentials in Database.php as follows:
 
$ `vim app/Config/Database.php`

public $default = [  
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
        ];

### Installing cvnet first by the aim of composer to fetch the necessary packages:

In the root directory of cvnet where the composer.json resides run the below command:

$ `composer install`

### Getting the installation key from cvnet and registering cafe variome instance’s URL:

The following command needs to be executed in the root directory of cvnet where index.php resides

$ `php index.php CLI addInstallation`

Please enter the base url of the installation: http://localhost/cvci4/  
Creating new installation  
Installation created.  
Installation key is : 78rtyyyuyuunkkl (For instance)

### Composer install on cafe variome and executing the shell script to complete installation

In the root directory of cvci4 where composer.json resides run the following command:

$ `composer install`

After fetching the packages run the script to complete the installation:

$ `composer CVInstall`

Setting directory permission…  
Directory permissions set.  
CafeVariomeSetup\CVInstaller::InstallDB  
Creating database tables …  
Tables imported successfully  

Now, it is time to enter the installation key and the URL of authentication server:

Please enter your installation key: 78rtyyyuyuunkkl  
Please enter the URL to authentication server:http://localhost/cvnet/

Now the setup is complete and ready to login into cafe variome instance on the following link:
http://localhost/cvci4/
