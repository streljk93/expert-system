<?php  
class config {

public static function main() {
  session_start();
  header("Content-Type: text/html; charset=UTF-8;"); // //windows-1251
    $host = 'localhost';
    $user = 'root';
    $pass = '13771993';
    $db   = 'expert-system';
  $connect = mysqli_connect($host, $user, $pass);   // подключение к mysql
  mysqli_query ($connect, "set character_set_client='utf8'");
  mysqli_query ($connect, "set character_set_results='utf8'");
  mysqli_query ($connect, "set collation_connection='utf8_general_ci'");
  mysqli_select_db($connect, $db);                 // подключение к базе данных (БД)

  $GLOBALS['table_names']['datacores'] = 'datacores'; //
  $GLOBALS['table_names']['questions'] = 'questions'; // 
  $GLOBALS['table_names']['answers'] = 'answers'; //
    $GLOBALS['database'] = [
        'connect' => $connect,
    ];

  
}

}
?>