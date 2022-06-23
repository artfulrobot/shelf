<?php
namespace ArtfulRobot\Shelf;


class IndexController extends Controller {

  public function run() {

    $this->scan($_GET['force'] ?? 0);

    $html = '';
    foreach ($this->core->index as $indexedProject) {
      $html .= "<article><h2>" . htmlspecialchars($indexedProject['name']) . '</h2><ul>';
      foreach ($indexedProject['files'] as $indexedFile) {
        $html .= "<li><a href='{$indexedFile['htmlUrl']}'>" . htmlspecialchars($indexedFile['title']) . "</a></li>\n";
      }
      $html .="</ul></article>\n";
    }
    $html = '<div class="index-content"><h1>Shelf</h1><div class="projects">' . $html  . '</div></div>';

    $this->renderPage($html);
  }

  public function scan($force) {

    // Build index of files
    $valid = [];
    foreach ($this->core->config['sourceDirs'] as $sourceDir) {
      $project = new Project($this->core, $sourceDir);
      $valid[$project->slug] = 1;
      $project->indexFiles($force);
    }

    // Remove anything from the index that is not in sourceDirs.
    $this->core->index = array_intersect_key($this->core->index, $valid);

    $this->core->saveIndex();
  }


}
