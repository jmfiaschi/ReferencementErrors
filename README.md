#ReferencementErrors

Redirect all errors on specifique error page to keep a good ranking

## Requirements

- Zend Framework 2.2 or higher

## Installation

ReferencementErrors only officially supports installation through Composer. For Composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

add this line in your composer.json :
```sh
...
"require" : {
...
"jmfiaschi/zf2-referencement-errors" : "dev-master",
...
},
...
```


Use the terminal and run this line in your application
```sh
$ php composer.phar update
```

Enable the module by adding `Zf2ReferencementErrors` key to your `application.config.php` file like this :
```sh
$env = getenv('APP_ENV') ?: 'production';
...
if ($env == 'production') {
	$modules[] = 'Zf2ReferencementErrors';
}
```

## Recommandation

Use this module for a production state. For development use the [https://github.com/ghislainf/zf2-whoops](https://github.com/ghislainf/zf2-whoops) module.
