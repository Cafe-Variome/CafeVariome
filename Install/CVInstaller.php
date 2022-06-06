<?php
//print("Install Cafe Variome...");
namespace CafeVariomeSetup;

class CVInstaller
{
	public static function InstallDB()
	{
	   // Valid PHP Version?
	   $minPHPVersion = '8.0';
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

		$lines = file('../.env');

		$db = [];


		foreach ($lines as $line)
		{
			if (str_starts_with($line, 'database.default.hostname'))
			{
				$db['hostname'] = trim(explode('=', $line)[1]);
			}
			else if(str_starts_with($line, 'database.default.database'))
			{
				$db['database'] = trim(explode('=', $line)[1]);
			}
			else if(str_starts_with($line, 'database.default.username'))
			{
				$db['username'] = trim(explode('=', $line)[1]);
			}
			else if(str_starts_with($line, 'database.default.password'))
			{
				$db['password'] = trim(explode('=', $line)[1]);
			}
			else if(str_starts_with($line, 'database.default.DBDriver'))
			{
				$db['DBDriver'] = trim(explode('=', $line)[1]);
			}

		}

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

	public static function Deploy(string $base_url, string $installation_key, string $php_bin_path, string $apache_config_file, string $deployment_directory, array $database_info)
	{
		// Valid PHP Version?
		$minPHPVersion = '8.0';
		if (phpversion() < $minPHPVersion) {
			die("Your PHP version must be {$minPHPVersion} or higher to run CodeIgniter. Current version: " . phpversion());
		}

		unset($minPHPVersion);

		// Ensure the current directory is pointing to the front controller's directory
		chdir(__DIR__);
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
			$con->query("Update settings set value = '$installation_key' where setting_key = 'installation_key';");

			echo 'Database imported successfully.';
		}

		$con->close();

		$envFile = $appPath . '.env';
		$envFileHandle = fopen($envFile, 'a+');
		if ($envFileHandle !== false)
		{
			$envData = "CI_ENVIRONMENT = development" . PHP_EOL;
			$envData .= "app.baseURL = '$base_url'" . PHP_EOL;
			$envData .= "app.baseURL = '$base_url'" . PHP_EOL;
			$envData .= "database.default.hostname = $dbHost" . PHP_EOL;
			$envData .= "database.default.database = $dbName" . PHP_EOL;
			$envData .= "database.default.username = $dbUsername" . PHP_EOL;
			$envData .= "database.default.password =  $dbPassword" . PHP_EOL;
			$envData .= "database.default.DBDriver = MySQLi" . PHP_EOL;
			$envData .= "#----------------------------------" . PHP_EOL;
			$envData .= "PHP_BIN_PATH = $php_bin_path" . PHP_EOL;
			$envData .= "EAV_BATCH_SIZE = 30000" . PHP_EOL;
			$envData .= "NEO4J_BATCH_SIZE = 30000" . PHP_EOL;
			$envData .= "SPREADSHEET_BATCH_SIZE = 1000" . PHP_EOL;
			$envData .= "ELASTICSERACH_AGGREGATE_SIZE = 100000" . PHP_EOL;
			$envData .= "ELASTICSERACH_EXTRACT_AGGREGATE_SIZE = 10000" . PHP_EOL;

			if (fwrite($envFileHandle, $envData) !== false)
			{
				echo '.env file was created successfully.';
			}
			else
			{
				echo 'Error in writing to .env file';
			}
			fclose($envFileHandle);
		}
		else
		{
			echo 'Error in creating a handle for .env file';
		}

		$apacheConfFileHandle = fopen($apache_config_file, 'a+');

		if ($apacheConfFileHandle !== false)
		{
			// Options None AllowOverride All  Require all granted </Directory>
			$apacheConfData = PHP_EOL . '<Directory "/local/www/htdocs/' . $deployment_directory . '">' . PHP_EOL;
			$apacheConfData .= "Options None" . PHP_EOL;
			$apacheConfData .= "AllowOverride All" . PHP_EOL;
			$apacheConfData .= "Require all granted" . PHP_EOL;
			$apacheConfData .= "</Directory>";

			if (fwrite($apacheConfFileHandle, $apacheConfData) !== false)
			{
				echo 'Apache config file was modified successfully.';
			}
			else
			{
				echo 'Error in writing to Apache config file.';
			}
			fclose($apacheConfFileHandle);
		}
		else
		{
			echo 'Error in creating a handle for Apache config file';
		}
	}
}

if (isset($argc)) {
	if($argc == 11){
		$base_url = $argv[1];
		$installation_key = $argv[2];
		$php_bin_path = $argv[3];
		$apache_config_file = $argv[4];
		$deployment_directory = $argv[5];
		$dbHost = $argv[6];
		$dbRootUser = $argv[7];
		$dbRootPassword = $argv[8];
		$dbName = $argv[9];
		$dbUsername = $argv[10];

		$dbInfo = [
			'host' => $dbHost,
			'root_user' => $dbRootUser,
			'root_password' => $dbRootPassword,
			'db_name' => $dbName,
			'username' => $dbUsername
		];

		CVInstaller::Deploy($base_url , $installation_key, $php_bin_path, $apache_config_file, $deployment_directory, $dbInfo);
	}
	else{
		echo 'Incorrect number of arguments passed.';
	}
}
else {
	echo "argc and argv disabled\n";
}


