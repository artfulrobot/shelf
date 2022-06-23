<?php
namespace ArtfulRobot\Shelf;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Project {
  public $slug;
  public $name;
  public $dir;
  public $core;

  public function __construct(Core $core, array $sourceDir) {
    $this->slug = $sourceDir['slug'] ?? trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($sourceDir['name'])), '-');
    $this->name = $sourceDir['name'];
    $this->dir = $sourceDir['dir'];
    $this->core = $core;
  }

  /**
   * Recursively go through all the .md files in a project
   * creating HTML versions as needed, and updating the index.
   *
   */
  public function indexFiles(bool $force = FALSE) {

    if (!isset($this->core->index[$this->slug])) {
      $this->core->index[$this->slug] = [
        'slug' => $this->slug,
        'name' => $this->name,
        'files' => [],
      ];
    }
    elseif (!$force && (time() - ($this->core->index[$this->slug]['indexedOn'] ?? 0)) < 60) {
      // do not re-index if done 1 minute ago
      error_log("Skipping indexing of " . $this->slug);
      return;
    }
    $files = &$this->core->index[$this->slug]['files'];
    $filesFound = [];
    $di = new RecursiveDirectoryIterator($this->dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $it = new RecursiveIteratorIterator($di);

    error_log("indexing of " . $this->slug);
    foreach($it as $file) {
      if (pathinfo($file, PATHINFO_EXTENSION) == "md") {

        $projectFile = new ProjectFile($this, $file);
        $filesFound[$projectFile->relPath] = 1;

        $projectFile->updateHtml($force);

        $files[$projectFile->relPath] = [
          'path' => $projectFile->relPath,
          'title' => $projectFile->title,
          'htmlUrl' => $projectFile->getHtmlUrl(),
        ];
      }
    }
    // Remove old files no longer present.
    $old = array_diff_key($files, $filesFound);
    error_log(count($files) . " files found");
    error_log(count($old) . " old files being removed");
    foreach ($old as $oldFile) {
      $projectFile = new ProjectFile($this, $oldFile, TRUE);
      $htmlFile = $projectFile->getHtmlPath();
      if (file_exists($htmlFile)) {
        unlink($htmlFile);
      }
    }
    $this->core->index[$this->slug]['files'] = array_intersect_key($files, $filesFound);

    // Sort.
    uasort($this->core->index[$this->slug]['files'], function ($a, $b) {
      if ($a['path'] === '/index' && $b['path'] !== '/index') return -1;
      if ($a['path'] !== '/index' && $b['path'] === '/index') return 1;
      return $a['path'] <=> $b['path'];
    });

    $this->core->index[$this->slug]['indexedOn'] = time();
  }

}
