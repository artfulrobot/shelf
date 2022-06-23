<?php
use ArtfulRobot\Shelf\PageController;
use ArtfulRobot\Shelf\IndexController;
use ArtfulRobot\Shelf\Core;

if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

include_once __DIR__ . '/../vendor/autoload.php';

define('SHELF_DATA_DIR', __DIR__ . '/data');
define('SHELF_CONFIG_FILE', __DIR__ . '/../shelf.json');
$core = Core::singleton();

if (preg_match('/\.(html|md)$/', $_SERVER["REQUEST_URI"])) {
  // Requesting a particular rendered page.
  PageController::handle($core, $_SERVER["REQUEST_URI"]);
}
else {
  IndexController::handle($core);
}
