# BEA - Manage theme plugins

Dev oriented plugin to manage theme's plugins (activation and deactivation) by forcing or suggesting it.

# Installation

## WordPress

* Download and install using the built-in WordPress plugin installer.
* Site Activate in the "Plugins" area of the admin.
* Optionally drop the entire `bea-manage-theme-plugins` directory into `mu-plugins`.
* Add into your theme's functions.php file how to manage your list of plugins, see [Usages](https://github.com/BeAPI/bea-manage-theme-plugins#usages).

## Composer

* Add repository source : `{ "type": "vcs", "url": "https://github.com/BeAPI/bea-manage-theme-plugins" }`.
* Include `"bea/bea-manage-theme-plugins": "dev-master"` in your composer file.
* Add into your theme's functions.php file how to manage your list of plugins, see [Usages](https://github.com/BeAPI/bea-manage-theme-plugins#usages).

# Usages

In your theme's functions.php file, hook on `bea\manage_theme_plugins\theme_plugins` to manage theme's plugins dependencies.

Here is an example of how theme's plugins array is formatted :
```
$theme_plugins = array(
     $action1 = array(
         plugin-folder1/plugin1.php,
         plugin-folder2/plugin2.php
     )
);
```

Available actions are :
- force_activation
- suggest_activation
- force_deactivation
- suggest_deactivation

## Example

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
		'force_deactivation'     => array(
			'image-widget/image-widget.php'
		),
		'suggest_deactivation' => array(
			'lazy-load/lazy-load.php'
		)
	);

	return $plugins;
}
```

## WP-Cli

### Single site management

Will exec only on given site, the theme's plugins management.

`wp plugins manage_single --url={site_url}`

### All sites management

Will exec on all sites, the theme's plugins management.

`wp plugins manage_all`

# Changelog ##

## 1.1.0 - 12 Dec 2016
* Add wp cli to single or multiple management.
* Update WP warnings messages.

## 1.0.1 - 02 Dec 2016
* Implement main methods to register/deregister theme's plugins depending in if it's forced or suggested.
* Update readme with usage & example.
* Add plugin's .pot.
* Add French translation (po/mo).
* Add composer.json !

## 1.0.0 - 02 Dec 2016
* Init with boilerplate 2.1.6
