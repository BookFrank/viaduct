<?php  
namespace Bookfrank\Viaduct;

class Router{

	public static $routes = [];

	public static $methods = [];

	public static $handlers = [];

	/**
	 * 利用重载完成路由的注册
	 * @method static get (string $route, Callable $handler)
	 * @method static post (string $route, Callable $handler)
	 * @method static put (string $route, Callable $handler)
	 * @method static delete (string $route, Callable $handler)
	 * @method static options (string $route, Callable $handler)
	 * @method static head (string $route, Callable $handler)
	 * @param  string $method [未定义的静态方法名]
	 * @param  Array $params [未定义方法的参数]
	 * @return
	 */
	public static function __callstatic($method, $params)
	{	
		$route = $params[0]; # 从uri解析的path带/
		$handler = $params[1];

		self::$routes[]   = $route == "/" ? $route : '/'.$route;
		self::$methods[]  = strtoupper($method);
		if (is_string($handler) || is_object($handler)) {
			self::$handlers[] = $handler;
		}else{
			self::error(500, "Need string or object");
		}
	}


	/**
	 * 查看路由列表
	 * @return table
	 */
	public static function listAll()
	{
		echo "<table border=1><tr><th>Route</th><th>Method</th><th>Handler</th></tr>";
		for ($i=0; $i < count(self::$routes); $i++) { 
			echo "<tr>";
			echo "<td>".self::$routes[$i]."</td>";
			echo "<td>".self::$methods[$i]."</td>";
			if (is_object(self::$handlers[$i])) {
				echo "<td>Object</td>";
			}else{
				echo "<td>".self::$handlers[$i]."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}


	/**
	 * 查询请求路由是否注册,返回注册索引
	 * @param  string $uri [server中得到的uri]
	 * @param  string $method [请求资源方法]
	 * @return int $pos [数字索引值]
	 */
	public static function findRoute($uri, $method)
	{
		$pos = null;
		if (in_array($uri, self::$routes)) { # in_array则为普通路由
			$indexArr = array_keys(self::$routes, $uri);
		}else{
			$uriArr = explode("/", $uri); # 可变路由
			foreach (self::$routes as $route) {					
				if (stripos($route, "{") !== false) {
					$routeArr = explode("/", $route); # 转换为数组，比对所有不被括号包住的
					if (count($routeArr) == count($uriArr)) {
						for ($i=1; $i < count($routeArr); $i++) { 
							if (stripos($routeArr[$i], "{") === false && $routeArr[$i] !== $uriArr[$i]) { continue 2;}
						}
						$indexArr = array_keys(self::$routes, $route);break;
					}
				}
			}
		}
		if (isset($indexArr)) {
			foreach ($indexArr as $index) {
				if ($method == self::$methods[$index]) { $pos = $index; break; }
			}
		}
		return $pos;
	}


	/**
	 * 响应路由动作
	 * @param  string $uri [请求uri]
	 * @param  string $pos [对应的路由的键值]
	 * @return [type]
	 */
	public static function handle($uri, $pos)
	{
		$handler = self::$handlers[$pos];
		$handlerType = gettype($handler);
		if (stripos(self::$routes[$pos], "{") !== false) {
			$route = self::$routes[$pos];
			$routeArr = explode("/", $route);
			$uriArr = explode("/", $uri);
			$param = [];
			for ($i=0; $i < count($uriArr); $i++) { 
				if (stripos($routeArr[$i],"{") !== false) { $param[] = $uriArr[$i];	}
			}
			switch ($handlerType) {
				case 'string':
					$handlerArr = explode("@", $handler);
					$class = trim($handlerArr[0]);
					$refMethod = new \ReflectionMethod($class, trim($handlerArr[1]));
					$refMethod->invokeArgs(new $class, $param);
					break;
				case 'object':
					call_user_func_array($handler,$param);
					break;
			}
		}else{
			switch ($handlerType) {
				case 'string':
					$handlerArr = explode("@", $handler);
					$class = trim($handlerArr[0]);
					$refMethod = new \ReflectionMethod(trim($handlerArr[0]), trim($handlerArr[1]));
					$refMethod->invoke(new $class);
					break;
				case 'object':
					call_user_func($handler);
					break;
			}
		}
	}


	/**
	 * 错误处理方法
	 * @param  int $code
	 * @param  string $msg
	 * @return html
	 */
	public static function error($code, $msg)
	{
		header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$msg);
    	echo "status: ".$code."<br>";
    	echo "msg: ".$msg;
    	exit();
	}


	/**
	 * 路由分发
	 * @return [type]
	 */
	public static function dispatch()
	{
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$method = $_SERVER['REQUEST_METHOD'];

		$pos = self::findRoute($uri, $method);
		if (!is_null($pos)) {
			self::handle($uri, $pos);
		}else{
			self::error(404, "Method Not Found");
		}
	}
}