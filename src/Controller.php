<?php
namespace ArtfulRobot\Shelf;

abstract class Controller {

  protected Core $core;

  public static function handle(Core $core) {
    $c = new static($core);
    $c->run();
  }

  public function __construct(Core $core) {
    $this->core = $core;
  }

  abstract public function run();

  public function renderPage(string $content) {
    $page = file_get_contents(SHELF_DATA_DIR . '/../page-template.html');
    print strtr($page, [
      '{content}' => $content,
    ]);
  }
}
