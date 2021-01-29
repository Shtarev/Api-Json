<?php
/*
|--------------------------------------------------------------------------
| api-json
|--------------------------------------------------------------------------
|
| Здесь простой скрипт для допиливания под потребности
| Скрипт положить к какую-нибудь папку, например yousite.loc/api
| Чтобы получить все записи из таблицы $table с полем id=12
| Перейти по этому адресу с параметрами например yousite.loc/api/?id=12
| Получишь все данные из таблицы $table с полем id=12 в json формате
| парамеры-названия полей можно передавать как GET, так POST 
|
*/

/**
* инициализация переменных для сообщения с базой
*/
$db_host = 'localhost'; // хост
$db_user = 'root';      // логин пользователя базы
$db_pass = '';          // пароль пользователя базы
$db_db = 'test';        // название базы
$table = 'testing';     // таблица в базе

if(isset($_GET['json_killer'])) {
	sleep(1);
	unlink(Request::_root_dir().'api.json');
}
else {
	$mysqli = mysqli_connect($db_host, $db_user, $db_pass, $db_db);
	$request = Request::request();

	$count = count($request);
	$key = array_keys($request)[0];
	$value = $request[$key];

	switch($count)
	{
		// без параметров - вытаскиваем все
		case 0:
		$res = $mysqli->query("SELECT * FROM $table")->fetch_all(MYSQLI_ASSOC);
		in_api($res);
		break;
		
		// с 1 параметром - вытаскиваем все по этому параметру
		case 1:
		$key = array_keys($request)[0];
		$value = $request[$key];
		$res = $mysqli->query("SELECT * FROM $table WHERE $key = '$value'")->fetch_all(MYSQLI_ASSOC);
		in_api($res);
		break;
		
		// несколько параметров - вытаскиваем в зависимости от параметров
		default:
		echo 'здесь несколько параметров - пропиши актуальные варианты выбора данных';
		break;
	}
}
/**
* в $res ассоциативный массив из базы, который преобразуем в json строку и сохраняем в файл api.json
* делаем перенаправление на api.json (следи за путями. здесь он в корне)
*/
function in_api($res) {
	file_put_contents('api.json', json_encode($res));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, Request::url_noparam().'?json_killer'); // отсылаем на этот же файл гет json_killer который ловим в самом начале
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); 
	curl_exec($ch);
	curl_close($ch);
	header('Location: '.Request::root_dir().'api.json');
	
}
/**
* класс серверных и браузерных ссылок
*/
class Request
{
    public function __construct() {
		//
	}
	// текущий браузерный путь без GET-параметров ( http://site.ru/admin/index?id=5&title=item  = http://site.ru/admin/index)
    public static function url_noparam() {
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
		return 'http://'.$_SERVER['HTTP_HOST'].$uri_parts[0];
    }
	// браузерный путь к директории где файл ( http://site.ru/dir/ )
    public static function root_dir() {
        $pieces = explode('/', $_SERVER['PHP_SELF']);    
		$file = array_pop($pieces);
		$url = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];  
        return "http://".mb_strstr($url, $file, true);
    }
	// серверный путь к директории где файл ( C:/OSPanel/domains/site.ru/dir/ )
    public static function _root_dir() {
        $pieces = explode('/', $_SERVER['PHP_SELF']);    
		$file = array_pop($pieces);
		$url = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];  
        return mb_strstr($url, $file, true);
    }
    // текущий запрос ( GET, POST, PUT, DELETE )
    public static function request() {
        $var = $_SERVER['REQUEST_METHOD'];
        switch ($var)
        {
            case 'GET':
            return $_GET;
            break;

            case 'POST':
            return $_POST;
            break;

            case 'PUT':
            return $_PUT;
            break;
            
            case 'DELETE':
            return $_DELETE;
            break;

            default:
            return 'Нет информации о методе запроса';
            break;
        }
    }
}
