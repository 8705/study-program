# 見出し1
## 見出し2
### 見出し3
#### 見出し4
##### 見出し5
###### 見出し6

- リスト1
    - ネスト リスト1_1
        - ネスト リスト1_1_1
        - ネスト リスト1_1_2
    - ネスト リスト1_2
- リスト2
- リスト3

1. 番号付きリスト1
    1. 番号付きリスト1_1
    1. 番号付きリスト1_2
1. 番号付きリスト2
1. 番号付きリスト3

> お世話になります。xxxです。
>
> ご連絡いただいた、バグの件ですが、仕様です。

> お世話になります。xxxです。
>
> ご連絡いただいた、バグの件ですが、仕様です。
> > お世話になります。 yyyです。
> >
> > あの新機能バグってるっすね

インストールコマンドは `gem install hoge` です

normal *italic* normal
normal _italic_ normal

normal **bold** normal
normal __bold__ normal

***

___

---

*    *    *

[Google先生](https://www.google.co.jp/)

~~取り消し線~~

```php
<?php

require_once 'Zend/Uri/Http.php';

namespace Location\Web;

interface Factory
{
   static function _factory();
}

abstract class URI extends BaseURI implements Factory
{
   abstract function test();

   public static $st1 = 1;
   const ME = "Yo";
   var $list = NULL;
   private $var;

   /**
    * Returns a URI
    *
    * @return URI
    */
   static public function _factory($stats = array(), $uri = 'http')
   {
       echo __METHOD__;
       $uri = explode(':', $uri, 0b10);
       $schemeSpecific = isset($uri[1]) ? $uri[1] : '';
       $desc = 'Multi
line description';

       // Security check
       if (!ctype_alnum($scheme)) {
           throw new Zend_Uri_Exception('Illegal scheme');
       }

       $this->var = 0 - self::$st;
       $this->list = list(Array("1"=> 2, 2=>self::ME, 3 => \Location\Web\URI::class));

       return [
           'uri'   => $uri,
           'value' => null,
       ];
   }
}

echo URI::ME . URI::$st1;

__halt_compiler () ; datahere
datahere
datahere */
datahere
```

 |header1|header2|header3|
 |:--|--:|:--:|
 |align left|align right|align center|
 |a|b|c|
