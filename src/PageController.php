<?php
namespace ArtfulRobot\Shelf;

class PageController extends Controller {

  public function run() {
    $requestPath = $_SERVER["REQUEST_URI"];

    $requestExtn = pathinfo($requestPath, PATHINFO_EXTENSION);
    if ($requestExtn === 'html') {
      // Check that the file is up to date. (todo)
      $path = SHELF_DATA_DIR . rawurldecode($requestPath);
      // todo check for naughty stuff.
      if (file_exists($path)) {
        $html = file_get_contents($path);
        $this->renderPage($html);
        exit;
      }
      $this->renderPage('Error: ' . $path . ' does not exist');
    }
  }

}
