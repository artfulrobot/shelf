<?php
namespace ArtfulRobot\Shelf;

use \Parsedown;

class Core {
  public static $singleton;

  public array $config = [];
  /**
   * like:
   * {
   *    <projectSlug>: {
   *      slug: '',
   *      name: '',
   *      files: [
   *        { mdPath: 'relative/path/to/file.md', title: 'h1 or other' },
   *        ...
   *      ]
   *    },
   *    ...
   * }
   *
   */
  public array $index = [];

  public static function singleton() {
    if (!isset(static::$singleton)) {
      static::$singleton = new static();
    }
    return static::$singleton;
  }

  public function __construct() {

    $this->config = json_decode(file_get_contents(SHELF_CONFIG_FILE), TRUE);

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
