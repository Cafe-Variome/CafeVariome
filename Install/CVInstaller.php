<?php
//print("Install Cafe Variome...");
namespace CafeVariomeSetup;

class CVInstaller
{
   	public static function InstallDB()
   {
	   // Valid PHP Version?
	   $minPHPVersion = '7.4';
	   if (phpversion() < $minPHPVersion) {
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
	   $con = @new \mysqli($mysql_host, $mysql_username, $mysql_password, $mysql_database);

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

	public static function Deploy(string $base_url, string $installation_key, string $php_bin_path, array $database_info): int
	{
		// Valid PHP Version?
		$minPHPVersion = '7.4';
		if (phpversion() < $minPHPVersion) {
			die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
		}

		unset($minPHPVersion);

		$appPath = '../';

		// Create the database and user
		$filename = 'MySql/cafevariome.sql';

		$dbHost = $database_info['host'];
		$dbRootUser = $database_info['root_user'];
		$dbRootPassword = $database_info['root_password'];
		$dbName = $database_info['db_name'];

		$dbUsername = $database_info['username'];
		$dbPassword = md5(uniqid());

		$con = @new \mysqli($dbHost, $dbRootUser, $dbRootPassword);
		// Check connection
		if ($con->connect_errno) {
			echo "Failed to connect to MySQL as a privileged user: " . $con->connect_errno;
			echo "<br/>Error: " . $con->connect_error;
		}

		$con->query("Create database $dbName;");
		$con->query("Create user $dbUsername identified by '$dbPassword';");
		$con->query("Grant all privileges on $dbName.* to '$dbUsername';");

		$con->close();

		$con = @new \mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

		// Check connection
		if ($con->connect_errno) {
			echo "Failed to connect to MySQL: " . $con->connect_errno;
			echo "<br/>Error: " . $con->connect_error;
		}
		else{
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

			$con->query("Update settings set value = '$installation_key' where setting_key = 'installation_key';");
		}

		$con->close();

		$envFile = $appPath . '.env';
		$envFileHandle = fopen($envFile, 'a+');
		$envData = "ENVIRONMENT = 'development'" . PHP_EOL;
		$envData .= "app.baseURL = '$base_url'" . PHP_EOL;
		$envData .= "app.baseURL = '$base_url'" . PHP_EOL;
		$envData .= "database.default.hostname = $dbHost" . PHP_EOL;
		$envData .= "database.default.database = $dbName" . PHP_EOL;
		$envData .= "database.default.username = $dbUsername" . PHP_EOL;
		$envData .= "database.default.password =  $dbPassword" . PHP_EOL;
		$envData .= "database.default.DBDriver = MySQLi" . PHP_EOL;

		fwrite($envFileHandle, $envData);
		fclose($envFileHandle);

		return 0;
	}

}

if (isset($argc)) {
	if($argc == 9){
		$base_url = $argv[1];
		$installation_key = $argv[2];
		$php_bin_path = $argv[3];

		$dbHost = $argv[4];
		$dbRootUser = $argv[5];
		$dbRootPassword = $argv[6];
		$dbName = $argv[7];
		$dbUsername = $argv[8];

		$dbInfo = [
			'host' => $dbHost,
			'root_user' => $dbRootUser,
			'root_password' => $dbRootPassword,
			'db_name' => $dbName,
			'username' => $dbUsername
		];

		CVInstaller::Deploy($base_url , $installation_key, $php_bin_path, $dbInfo);
	}
	else{
		echo 'Incorrect number of arguments passed.';
	}
}
else {
	echo "argc and argv disabled\n";
}


