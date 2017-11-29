## Viaduct 高架桥

Viaduct is a simple and quick PHP router.

> Viaduct is a very simple php router, hope to be able to help the php-framework beginners to understand the principle of router better.

### Features

* Simple
* One-file (only 6 functions)
* Laravel-like routes


### Installation

1. You can directly run `composer require` to install in your project.

```php
composer require bookfrank/viaduct
```

2. Also you can get the viaduct simply require it in your `composer.json` file.

```
"bookfrank/viaduct": "dev-master"
```

You will then need to run `composer install` to download it and have the autoloader updated.

### Usage

First, create the `routes.php` file.

```php
<?php
use \Bookfrank\Viaduct\Router;

Router::get('hello', function(){
	echo "Hello viaduct";
});

Router::get('profile/{uid}', function($uid){
	echo "Present userid is ".$uid;
});

Router::get('blog/{id}', "\Foo\Bar\FooController@bar");

Router::dispatch();
```

```php
<?php
class FooController{
	public function bar($id){
    	echo "id is ".$id;
    }
}
```


### Contact me

Author: Frank 李扬

Email: bookfrank@foxmail.com



