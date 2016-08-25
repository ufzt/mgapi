<?php
namespace Common\Atom;

class Seed{

  static function rand($length, $type=0){
    $dict = range(0,9); if ($type==1) $dict = range('a', 'z');
    $id = '';
    for ($i=0; $i<$length; $i++) {
      $k  = array_rand($dict);
      $id.= $dict[$k];
    }
    return $id;
  }

  const PREFIX = '$2a$10$';
  static function dkey(){
    $seed = C('token');
    $salt = substr(hash('sha256', time()), 0, 22);
    return  substr( crypt($seed, self::PREFIX.$salt),
                   strlen(self::PREFIX) );
  }

  static function is_dkey($dkey){
    $seed = C('token');
    $food = self::PREFIX.$dkey;
    return $food == crypt($seed, $food);
  }
}
