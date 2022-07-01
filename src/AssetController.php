<?php
namespace ArtfulRobot\Shelf;

class AssetController extends Controller {

  public function run() {
    $requestPath = rawurldecode($_SERVER["REQUEST_URI"]);

    if (preg_match('@^/([^/]+)(/.*)$@', $requestPath, $matches)) {
      $project = new Project($this->core, $this->core->projectSlugToConfig[$matches[1]]);
      $pathRelativeToProject = $matches[2];
      $path = realpath($project->dir . $pathRelativeToProject);
      $projpath = realpath($project->dir);
      if (substr($path, 0, strlen($projpath)) !== $projpath) {
        throw new \Exception("Cannot access assets outside of project");
        // $this->renderPage('Error: ' . $path . ' does not exist');
      }
      if (file_exists($path)) {
        header('Content-Type: ' . mime_content_type($path));
        readfile($path);
        exit;
      }
    }

  }

}
