<?php
namespace ArtfulRobot\Shelf;

use \Parsedown;

class Core {
  public static $singleton;

  /**
   * Like:
   * {
   *    sourceDirs: [
   *      {
   *        "dir": "/path/to/dir",
   *        "name: "Human friendly project name",
   *        "slug": "my_notes",
   *      }, ...
   *    ]
   * }
    */
  public array $config = [];
  /**
   * like:
   * {
   *    <projectSlug>: {
   *      slug: '',
   *      name: '',
   *      files: [
   *        { path: 'relative/path/to/file.md', title: 'h1 or other', htmlUrl: '/root/relative/url.html' },
   *        ...
   *      ]
   *    },
   *    ...
   * }
   *
   */
  public array $index = [];

  /**
   * Copy of $config, indexed by projcet slug.
    */
  public array $projectSlugToConfig = [];

  public static function singleton() {
    if (!isset(static::$singleton)) {
      static::$singleton = new static();
    }
    return static::$singleton;
  }

  public function __construct() {

    // The config file contains a list of paths to search (inc. *) and optionally specifies slugs and names.
    // We search these paths to construct the projectSlugToConfig arary, which
    // contains keys dir (the calculated one if a wildcard was used), the slug
    // and name.
    $this->config = json_decode(file_get_contents(SHELF_CONFIG_FILE), TRUE);
    foreach ($this->config['sourceDirs'] as $sourceDir) {

      if (strpos($sourceDir['dir'], '*') !== FALSE) {
        // Is a glob pattern.
        $dirs = glob($sourceDir['dir']);
        $parts = explode('*', $sourceDir['dir']);
        $re = ';' . preg_quote(array_shift($parts), ';');
        foreach ($parts as $p) {
          $re .= '(.*?)' . preg_quote($p);
        }
        $re .= ';';
        foreach ($dirs as $dir) {
          if (!preg_match($re, $dir, $matches)) {
            throw new \InvalidArgumentException("Config error: pattern $re from $sourceDir[dir]");
          }
          $slug = $matches[1];
          // error_log($slug . ':' . $dir);
          $this->projectSlugToConfig[$slug] = [
            'dir' => rtrim($dir, '/'),
            'slug' => $slug,
            'name' => $slug,
          ];
        }
      }
      else {
        $this->projectSlugToConfig[$sourceDir['slug']] = $sourceDir;
      }
    }

    // Load the index. See structure above.
    // The index should contain data for each file found within each project.
    // It is kept in sync by the IndexController
    if (file_exists(SHELF_DATA_DIR . '/index.serialized')) {
      $index = file_get_contents(SHELF_DATA_DIR . '/index.serialized');
      $index = $index ? unserialize($index) : NULL;
    }
    $this->index = $index ?? [];
  }

  public function saveIndex() {
    file_put_contents(SHELF_DATA_DIR . '/index.serialized', serialize($this->index));
  }

}
