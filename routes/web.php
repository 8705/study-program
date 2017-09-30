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
use App\Services\Mokuji\Mokuji;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$router->get('/', function (Mokuji $mokuji) use ($router) {
  $content = "<h1>ようこそ</h1>";

  return view("layout", [
    'content' => $content,
    'dirs' => $mokuji->generate(),
  ]);
});

$router->get('{chapter}/{title}', function (Mokuji $mokuji,$chapter,$title) use ($router) {
  $chapter  = urldecode($chapter);
  $title    = urldecode($title);
  $md_file  = MD_PATH."/{$chapter}/{$title}.md";
  if ( !is_file($md_file)) throw new NotFoundHttpException("404 Not Found");

  $markdown = file_get_contents($md_file);

  $parser   = new \cebe\markdown\GithubMarkdown();
  $parser->html5 = true;
  $html     = $parser->parse($markdown);

  return view("layout", [
    'content' => $html,
    'dirs' => $mokuji->generate(),
  ]);
});
