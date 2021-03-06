<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Let's Study Programing</title>
  <meta name="format-detection" content="telephone=no">
  <meta name="Keywords" content="">
  <meta name="description" content="">
  <meta name="referrer" content="unsafe-url">
  <meta http-equiv="content-language" content="ja">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link href="/css/reset.css" rel="stylesheet"></link>
  <link href="/css/github-markdown.css" rel="stylesheet"></link>
  <!-- <link href="/css/hightlightstyles/agate.css" rel="stylesheet"></link> -->
  <!-- <link href="/css/hightlightstyles/dracula.css" rel="stylesheet"></link> -->
  <!-- <link href="/css/hightlightstyles/gruvbox-dark.css" rel="stylesheet"></link> -->
  <link href="/css/hightlightstyles/monokai.css" rel="stylesheet"></link>
  <link href="/css/style.css" rel="stylesheet"></link>
  <!-- <script src="/js/syntaxhighlighter.js"></script> -->

</head>
<body>
  <div class="container-fulid">
    <div class="row">
      <div id="side-menu" class="col-sm-3 col-md-2">
        <p class="link-top"><a href="/">TOP</a></p>
        <ul class="chapers">
          <?php foreach ( $dirs as $dir => $mds ):?>
          <li>
            <p class="chaper"><?php echo $dir; ?></p>
            <ul class="titles">
              <?php foreach ( $mds as $md ):?>
                <li><a class="arrow ku-arrow" href="/<?php echo $dir; ?>/<?php echo rtrim($md,'.md'); ?>"><?php echo rtrim($md,'.md'); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-sm-9 col-md-10"><?php echo $content; ?></div>
    </div>
  </div>
  <footer>
    
  </footer>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
  <script>hljs.initHighlightingOnLoad();</script>
</body>
</html>
