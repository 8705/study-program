<?php

namespace App\Services\Mokuji;

class MarkdownMokuji implements Mokuji {

    public function generate() {
        // 目次の配列を作成
        $dirs = scandir(MD_PATH);
        $dirs = array_filter($dirs, function($dir){
          return !in_array($dir, ['.','..','.DS_Store']);
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
        return $dirs;
    }
}
