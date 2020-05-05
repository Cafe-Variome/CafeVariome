<?php
//print("Install Cafe Variome...");
namespace CafeVariomeSetup;

class CVInstaller 
{
   public static function InstallDB()
   {
        // Valid PHP Version?
        $minPHPVersion = '7.2';
        if (phpversion() < $minPHPVersion)
        {
            die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
        }
        unset($minPHPVersion);

        // Path to the front controller (this file)
        define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
        define('ENVIRONMENT', 'development');

        // Location of the Paths config file.
        // This is the line that might need to be changed, depending on your folder structure.
        $pathsPath = '../app/Config/Paths.php';
        // ^^^ Change this if you move your application folder

        /*
        *---------------------------------------------------------------
        * BOOTSTRAP THE APPLICATION
        *---------------------------------------------------------------
        * This process sets up the path constants, loads and registers
        * our autoloader, along with Composer's, loads our constants
        * and fires up an environment-specific bootstrapping.
        */
        // Ensure the current directory is pointing to the front controller's directory
        chdir(__DIR__);

        // Load our paths config file
        require $pathsPath;
        $paths = new \Config\Paths();

        // Location of the framework bootstrap file.
        require rtrim($paths->systemDirectory, '/ ') . '/bootstrap.php';

        echo "Creating database tables ... \n";

        $db = config('Database')->default;

        // Name of the file
        $filename = 'MySql/cafevariome.sql';
        // MySQL host
        $mysql_host = $db['hostname'];
        // MySQL username
        $mysql_username = $db['username'];
        // MySQL password
        $mysql_password = $db['password'];
        // Database name
        $mysql_database = $db['database'];

        // Connect to MySQL server
        $con = @new \mysqli($mysql_host,$mysql_username,$mysql_password,$mysql_database);

        // Check connection
        if ($con->connect_errno) {
            echo "Failed to connect to MySQL: " . $con->connect_errno;
            echo "<br/>Error: " . $con->connect_error;
        }

        // Temporary variable, used to store current query
        $templine = '';
        // Read in entire file
        $lines = file($filename);
        // Loop through each line
        foreach ($lines as $line) {
        // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

        // Add this line to the current segment
            $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                // Perform the query
                $con->query($templine);
                // Reset temp variable to empty
                $templine = '';
            }
        }

        echo "Tables imported successfully \n";

        print("Please enter your installation key:");
        $installation_key = readline("Please enter your installation key:");
        $installation_key = trim($installation_key);

        print("Please enter the URL to authentication server:");
        $auth_server = readline("Please enter the URL to authentication server:");
        $auth_server = trim($auth_server);

        $con->query("Update settings set value = '$installation_key' where setting_key = 'installation_key';");
        $con->query("Update settings set value = '$auth_server' where setting_key = 'auth_server';");

        print("Settings were updated successfully. \n");

        print("Setup completed. Remember that you must remove the Install directory completely. \n");
        $con->close();
    }
}

