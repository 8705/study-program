<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
  $content = "<h1>hogheog<h1>";
  return view("layout", ['content'=>$content]);
});

$router->get('{chapter}/{title}', function ($chapter,$title) use ($router) {
  $chapter  = urldecode($chapter);
  $title    = urldecode($title);
  $md_file  = MD_PATH."/{$chapter}/{$title}.md";
  if ( !is_file($md_file)) throw new HttpException("404 Not Found", 1);

  $markdown = file_get_contents($md_file);

  $parser   = new \cebe\markdown\GithubMarkdown();
  $parser->html5 = true;
  $html     = $parser->parse($markdown);

  // 目次
  $dirs = scandir(MD_PATH);
  $dirs = array_filter($dirs, function($dir){
    return !in_array($dir, ['.','..']);
  });
  $dirs = array_flip($dirs);
  $list = [];
  foreach ( $dirs as $dir => $val ) {
    $files = scandir(MD_PATH.'/'.$dir);
    $files = array_filter($files, function($file){
      return !in_array($file, ['.','..']);
    });
    $dirs[$dir] = array_values($files);
  }
  return view("layout", [
    'content' => $html,
    'dirs'    => $dirs
  ]);
});
