<?php
namespace ArtfulRobot\Shelf;

class ProjectFile {
  /** Full path */
  public string $mdFilePath;
  /** path relative to the project and without extension */
  public string $relPath;
  public Project $project;
  public string $htmlFilePath;
  public string $title;

  /**
    */
  public static function fromRelPath(Core $core, string $relativePath) {
    if (preg_match('@^/([^/]+)(/.*?)(\.(?:md|html))$@', $relativePath, $matches)) {
      $project = new Project($core, $core->projectSlugToConfig[$matches[1]]);
      $file = new ProjectFile($project, $matches[2], TRUE);
      return $file;
    }
    throw new \InvalidArgumentException("Cannot parse $relativePath");
  }
  /**
   * If $relativePath then $mdFilePath is like/this otherwise it is /home/user/project/like/this.md
   *
   */
  public function __construct(Project $project, string $mdFilePath, bool $relativePath = FALSE) {
    $this->project = $project;
    if ($relativePath) {
      $this->mdFilePath = $project->dir . $mdFilePath . '.md';
      $this->relPath = $mdFilePath;
    }
    else {
      if (substr($mdFilePath, 0, strlen($project->dir)) !== $project->dir) {
        // uh oh.
        throw new \Exception("md path $mdFilePath does not match " . $project->dir);
      }
      $this->mdFilePath = $mdFilePath;
      $mdPathWithoutSuffix = substr($this->mdFilePath, 0, strlen($this->mdFilePath) - 3);
      $this->relPath = substr($mdPathWithoutSuffix, strlen($this->project->dir));
    }

    // Can we load the title from the index?
    $this->title  = $this->project->core->index[$this->project->slug]['files'][$this->relPath]['title'] ?? substr($this->relPath, 1);
  }

  public function getHtmlPath() :string {
    $htmlPath = SHELF_DATA_DIR . '/' . $this->project->slug .
      $this->relPath
      . '.html';
    return $htmlPath;
  }

  public function getHtmlUrl() :string {
    $htmlPath = '/' . $this->project->slug . $this->relPath . '.html';
    return $htmlPath;
  }

  public function updateHtml(bool $force = FALSE) {

    $htmlPath = $this->getHtmlPath();

    $rebuild = TRUE;

    if (!$force && file_exists($htmlPath)) {
      // compare the mtimes of the two files.
      $mtimeSource = filemtime($this->mdFilePath);
      if (filemtime($htmlPath) >= $mtimeSource) {
        // HTML is fine.
        $rebuild = FALSE;
      }
    }

    if ($rebuild) {
      $this->createHtml($this);
    }

  }

  public function createHtml() {

    // Header.
    $html = '<header><div class="path"><span class="project-dir">' . htmlspecialchars($this->project->dir) . '</span><span class="project-file">' 
      .htmlspecialchars($this->relPath) . '.md</span></div></header>';

    $parsedown = new \Parsedown();
    $html .= '<div class="main-content">' . $parsedown->text(file_get_contents($this->mdFilePath)) . '</div>';

    $htmlPath = $this->getHtmlPath();
    $dir = dirname($htmlPath);
    if (!file_exists($dir)) {
      mkdir($dir, 0777, TRUE);
    }
    file_put_contents($htmlPath, $html);
    // Crudely find the first h1.
    if (preg_match('@<h1>([^<]+)</h1>@s', $html, $matches)) {
      $this->title = $matches[1];
    }
    else {
      $this->title = substr($this->relPath, 1);
    }
  }
  public function matches(array $needles) {
    $src = mb_strtolower(file_get_contents($this->mdFilePath));
    foreach ($needles as $needle) {
      if (strpos($src, $needle) === FALSE) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
