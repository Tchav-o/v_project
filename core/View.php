<?php
namespace Core;
final class View{
  public static function make(string $v,array $d=[]):string{
    $file=dirname(__DIR__)."/resources/views/$v.php";
    extract($d,EXTR_SKIP);
    ob_start();
    include$file;
    return ob_get_clean();
  }
  public static function layout(string $l,array $d):string{
    return self::make("layouts/$l",$d);
  }
}
