# BEA - Manage theme plugins

## Description ##

Dev oriented plugin to manage theme's plugins on activation and deactivation by forcing or suggesting it.

## Usages

In your theme's functions.php file, hook on `bea\manage_theme_plugins\theme_plugins` to manage theme's plugins dependencies.

Here is an example of how theme's plugins array is formatted :
$theme_plugins = array(
     $action1 = array(
         plugin-folder1/plugin1.php,
         plugin-folder2/plugin2.php
     )
);

Actions are :
- force_activation
- suggest_activation
- force_deactivation
- suggest_deactivation

### Exemple

```
<?php
/**
 * Manage all plugin dependencies
 */
add_filter( 'bea\manage_theme_plugins\theme_plugins', 'manage_my_theme_plugins' );
function manage_my_theme_plugins( $plugins ) {
	$plugins = array(
		'force_activation'     => array(
			'advanced-custom-fields-pro/acf.php',
			'wp-thumb/wpthumb.php',
		),
		'suggest_activation'   => array(
			'wp-pagenavi/wp-pagenavi.php',
			'wordpress-seo/wp-seo.php'
		),
		'force_activation'     => array(
			'image-widget/image-widget.php'
		),
		'suggest_deactivation' => array(
			'lazy-load/lazy-load.php'
		)
	);

	return $plugins;
}
```


## Changelog ##

### 1.0.0
* 02 Dec 2016
* Init with boilerplate 2.1.6
