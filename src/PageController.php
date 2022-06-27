<?php
namespace ArtfulRobot\Shelf;

class PageController extends Controller {

  public function run() {
    $requestPath = rawurldecode($_SERVER["REQUEST_URI"]);

    $projectFile = ProjectFile::fromRelPath($this->core, $requestPath);
    $projectFile->updateHtml();
    $path = $projectFile->getHtmlPath();
    if (file_exists($path)) {
      $html = file_get_contents($path);
      $this->renderPage($html, [
        '{title}' => htmlspecialchars($projectFile->title),
      ]);
      exit;
    }
    $this->renderPage('Error: ' . $path . ' does not exist');
  }

}
