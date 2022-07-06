# Shelf

- Provides a local web page that shows all your .md files across different projects.

- Searchable (instant search on titles, hit enter for full text search).

- Always up-to-date. The HTML is re-rendered any time you update your.md file.

![screencast](screencast.gif)

## Configuration

Edit `shelf.json`. e.g.:

```json
{
  "sourceDirs": [
    { "dir": "/home/rich/notes", "name":"Tech notes", "slug": "tech" },
    { "dir": "/home/rich/myciviextensions/*/docs/"}
}
```

Each entry in sourceDirs specifies the directory that contains .md files. All subdirs of that dir will be searched. The dir may include glob patterns, here the 2nd entry will look in all my extensions' docs/ dirs.

## Running

Run by going to the 'app' dir and running `php -S localhost:8123 router.php`

Or adapt and install the systemd service included.
