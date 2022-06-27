<?php
namespace ArtfulRobot\Shelf;


class IndexController extends Controller {

  public function run() {

    $this->scan($_GET['force'] ?? 0);

    $search = trim(mb_strtolower($_GET['search'] ?? ''));
    $searchValue = '';
    if ($search) {
      $search = preg_split('/\s+/', $search);
      $searchValue = htmlspecialchars(implode(" ", $search));
    }

    $html = '';
    foreach ($this->core->index as $indexedProject) {
      $html .= "<article><h2>" . htmlspecialchars($indexedProject['name']) . '</h2><ul class="pages">';
      foreach ($indexedProject['files'] as $indexedFile) {
        $include = TRUE;

        if ($search) {
          // Only return the result if the file search matches.
          $project = new Project($this->core, $this->core->projectSlugToConfig[$indexedProject['slug']]);
          $file = new ProjectFile($project, $indexedFile['path'], TRUE);
          if (!$file->matches($search)) $include = FALSE;
        }
        if ($include) {
          $html .= "<li><a href='{$indexedFile['htmlUrl']}'>" . htmlspecialchars($indexedFile['title']) . "</a></li>\n";
        }
      }
      $html .="</ul></article>\n";
    }
    $clearSearch = empty($searchValue) ? '' : '<a href=/ class="clear-search" >✖</a>';

    $html = <<<HTML
      <div class="index-content">
        <div class="shelf-index-header">
          <h1>Shelf</h1>
          <form method=get><div><span id=hint ><kbd>Enter</kbd> for full text search</span><input name=search value="$searchValue" /></div> $clearSearch</form>
        </div>
        <div class="projects">$html</div>
      </div>';
      HTML;

    $this->renderPage($html, [
        '{title}' => htmlspecialchars('Shelf'),
      ]);
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
