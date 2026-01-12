<?php

/**
 * Application Entry Point
 * 
 * This file demonstrates how to organize your routes
 * by loading separate route files for web and API
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SimpleRouter\Application\Router;

// Create router instance
$router = new Router();

// ============================================================================
// Load Route Files
// ============================================================================

// Load web routes (HTML responses, session-based auth)
require_once __DIR__ . '/routes/web.php';

// Load API routes (JSON responses, token-based auth)
require_once __DIR__ . '/routes/api.php';

// You can also organize by domain/module
// require_once __DIR__ . '/routes/admin.php';
// require_once __DIR__ . '/routes/shop.php';
// require_once __DIR__ . '/routes/blog.php';

// ============================================================================
// Run the Application
// ============================================================================

$router->run();
