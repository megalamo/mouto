<?php
// ==============================================================================
// 1. APPLICATION BOOTSTRAP & AUTOLOADING
// ==============================================================================

// Load the Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // safeLoad won't crash if .env is missing in production (e.g., relying on server env vars)

// Load structured configurations
$config = [
	'app' => require __DIR__ . '/../config/application.php',
	'database' => require __DIR__ . '/../config/database.php',
];

// ==============================================================================
// 2. DATABASE CONNECTION (Multi-Driver PDO)
// ==============================================================================

try {
	$dbConfig = $config['database']['connections'][$config['database']['default']];
	$driver = $dbConfig['driver'];

	// Dynamically build the Data Source Name (DSN) string based on the selected driver
	if ($driver === 'sqlite') {

		$dsn = "sqlite:" . $dbConfig['database'];
		$db = new PDO($dsn);
		// Enable foreign key constraints for SQLite
		$db->exec('PRAGMA foreign_keys = ON;');
	} elseif ($driver === 'mysql' || $driver === 'mariadb') {

		$dsn = sprintf(
			"mysql:host=%s;port=%s;dbname=%s;charset=%s",
			$dbConfig['host'],
			$dbConfig['port'],
			$dbConfig['database'],
			$dbConfig['charset']
		);
		$db = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
	} elseif ($driver === 'pgsql') {

		$dsn = sprintf(
			"pgsql:host=%s;port=%s;dbname=%s;sslmode=%s",
			$dbConfig['host'],
			$dbConfig['port'],
			$dbConfig['database'],
			$dbConfig['sslmode']
		);
		$db = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
	} else {
		throw new Exception("Unsupported database driver selected.");
	}

	// Enforce strict error handling and associative array fetching globally
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
	error_log("Database Connection Failed: " . $e->getMessage());
	http_response_code(500);
	die("<h1>Critical Error</h1><p>Database connection failed. Please try again later.</p>");
}

// ==============================================================================
// 3. ROUTING & REQUEST PARSING
// ==============================================================================

// Load the routing configuration
$routes = require __DIR__ . '/../config/routes.php';

// Parse the incoming request parameters
$page = $_GET['page'] ?? $routes['default_page'];
$action = $_GET['s'] ?? '';

// Determine the Controller Name (Fallback to ErrorController if not found)
$controllerName = $routes['controllers'][$page] ?? 'ErrorController';

// Determine the specific method to call inside the Controller
if ($action !== '') {
	$method = $action; // e.g. ?page=post&s=view -> PostController->view()
} else {
	// Look up the default method for this page, fallback to 'index'
	$method = $routes['default_methods'][$page] ?? 'index';
}

// Determine the specific method to call inside the Controller
if ($action !== '') {
	$method = $action; // e.g. ?page=post&s=view -> PostController->view()
} else {
	// If no 's' action is provided, map specific page strings to their default methods
	$method = match ($page) {
		'search' => 'search',
		'account_profile' => 'profile',
		'account_options' => 'options',
		'reg' => 'register',
		'login' => 'login',
		'reset_password' => 'reset_password',
		'post', 'comment' => 'list', // default to listing if viewing posts/comments without an action
		default => 'index'
	};
}


// ==============================================================================
// 4. CONTROLLER DISPATCH
// ==============================================================================

// Construct the fully qualified class name
$controllerClassName = '\\App\\Controllers\\' . $controllerName;

if (class_exists($controllerClassName)) {

	// Instantiate the controller, injecting the PDO database and config
	$controller = new $controllerClassName($db, $config);

	// Check if the requested method exists
	if (method_exists($controller, $method)) {
		$controller->$method();
	} elseif (method_exists($controller, 'index')) {
		$controller->index();
	} else {
		http_response_code(404);
		echo "<h1>404 Not Found</h1><p>The requested action could not be found.</p>";
	}
} else {
	// Controller class file does not exist
	http_response_code(404);

	$notFoundView = __DIR__ . '../app/Views/layouts/404.php';
	if (file_exists($notFoundView)) {
		require_once $notFoundView;
	} else {
		echo "<h1>404 Not Found</h1><p>The requested page could not be found.</p>";
	}
}
