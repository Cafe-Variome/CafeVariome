<?php
//print("Install Cafe Variome...");
namespace CafeVariomeSetup;

use App\Libraries\CafeVariome\Security\Cryptography;

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

		// print("Please enter your installation key:");
		$installation_key = readline("Please enter your installation key:");
		$installation_key = trim($installation_key);

		// print("Please enter the URL to authentication server:");
		$auth_host = readline("Please enter the URL to authentication server (OpenID Connect provider URL: https://example.com/) :");
		$auth_host = trim($auth_host);

		$client_id = readline("Please enter the client id:");
		$client_id = trim($client_id);
		$keycloak_realm = readline("Please enter the keycloak realm:");
		$keycloak_realm = trim($keycloak_realm);
		$client_secret = readline("Please enter the client secret:");
		$client_secret = trim($client_secret);
		$client_secret_hash = Cryptography::GenerateSecretKey();
		$client_secret_password_hash = Cryptography::Encrypt($client_secret, $client_secret_hash);

		$auth_server = $auth_host."realms/$keycloak_realm/";

		// print("Please enter the URL to network server:");
		$network_server = readline("Please enter the URL to network server:");
		$network_server = trim($network_server);

		$admin_firstname = readline("Please enter your first name:");
		$admin_firstname = trim($admin_firstname);

		$admin_lastname = readline("Please enter last name:");
		$admin_lastname = trim($admin_lastname);

		$admin_username = readline("Please enter your email (It will be your username):");
		$admin_username = trim($admin_username);

		$admin_affiliation = readline("Please enter your affiliation:");
		$admin_affiliation = trim($admin_affiliation);

		// print("Please enter the password for Admin user:")
		echo "Kindly take note of the following PASSWORD POLICIES:
		1. Ensure the inclusion of at least one uppercase letter.
		2. Ensure the inclusion of at least one lowercase letter.
		3. Maintain a minimum length of 8 characters.
		4. Incorporate at least one digit for added security. \n";

		$admin_password = readline("Please enter the password for Admin user:");
		//$admin_password = trim(password_hash("$admin_password", PASSWORD_DEFAULT));

		$is_created = self::createUserOnKeycloak($auth_host, $client_id, $client_secret, $keycloak_realm, $admin_username,
			$admin_username, $admin_firstname, $admin_lastname, $admin_password);

		if ($is_created)
		{

			$stmt = $con->prepare("UPDATE settings SET `value` = ? WHERE `key` = 'installation_key'");
			$stmt->bind_param("s", $installation_key);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE settings SET `value` = ? WHERE `key` = 'auth_server'");
			$stmt->bind_param("s", $network_server);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `password` = NULL WHERE `id` = 1");
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `first_name` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $admin_firstname);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `last_name` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $admin_lastname);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `username` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $admin_username);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `email` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $admin_username);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE users SET `company` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $admin_affiliation);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE servers SET `address` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $auth_server);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE credentials SET `username` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $client_id);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE credentials SET `password` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $client_secret_password_hash);
			$stmt->execute();

			$stmt = $con->prepare("UPDATE credentials SET `hash` = ? WHERE `id` = 1");
			$stmt->bind_param("s", $client_secret_hash);
			$stmt->execute();


			print("Settings were updated successfully. \n");
			print("Setup completed. Remember that you must remove the Install directory completely. \n");
		}
		else
		{
			print("Installation was not successful.\n");
		}
		$con->close();
	}

	public static function createUserOnKeycloak($keycloakHost, $client_id, $client_secret, $realm,
												$username, $email, $firstName, $lastName, $password)
	{
		$tokenUrl = "$keycloakHost/realms/$realm/protocol/openid-connect/token";
		$data = [
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'grant_type' => 'client_credentials',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $tokenUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for testing, disable in production

		$response = curl_exec($ch);

		if (!$response) {
			die('Failed to obtain access token: ' . curl_error($ch));
		}

		$responseData = json_decode($response, true);
		$accessToken = $responseData['access_token'];

		$userUrl = "$keycloakHost/admin/realms/$realm/users";
		$userData = [
			'username' => $username,
			'enabled' => true,
			'email' => $email,
			'firstName' => $firstName,
			'lastName' => $lastName,
			"emailVerified" => true,
			'credentials' => [
				[
					'type' => 'password',
					'value' => $password,
					'temporary' => false,
				],
			],
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $userUrl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $accessToken,
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Only for testing, disable in production

		$response = curl_exec($ch);

		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 201)
		{
			die('Failed to create user as  ' . $response);
		}
		else
		{
			curl_close($ch);
			return true;
		}


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
