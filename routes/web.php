<?php
if(!class_exists('Route')){
  class Route{
    public static function get(string $p,$a){self::add('GET',$p,$a);}
    private static function add($m,$p,$a){global $routes;
      if(is_array($a)){[$c,$f]=$a;$routes[$m][$p]=fn()=>(new $c())->$f();}
      else $routes[$m][$p]=$a;
    }
  }
}
use App\Http\Controllers\SlotController;
Route::get('/',[SlotController::class,'welcome']);
Route::get('/spin',[SlotController::class,'spin']);
