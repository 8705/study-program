<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MokujiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      // Messengerサービスのバインド
      $this->app->bind(
          'App\Services\Mokuji\Mokuji',
          'App\Services\Mokuji\MarkdownMokuji'
      );
    }
}
