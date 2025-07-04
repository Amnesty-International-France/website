# Amnesty International France Website

Ceci est le dépôt de la refonte du site web d'Amnesty International France basé sur [humanity theme](https://github.com/amnestywebsite/humanity-theme) [(README)](./README_humanity.md). Le dépôt contient tous les éléments nécessaires au fonctionnement du site, notamment les plugins spécifiques.

This is the repository for the redesign of the Amnesty International France website based on [humanity theme](https://github.com/amnestywebsite/humanity-theme) [(README)](./README_humanity.md). The repository contains all the elements required to run the site, including specific plugins.

## Requirements

- PHP 8.0 or higher
- MySQL version 8.0 or higher or MariaDB version 10.5 or higher
- [Castor](https://github.com/jolicode/castor)

## Installation

First, you need to change `.env` file to provide some information to the script for the creation of the WordPress environment.  
You can create different env file like `.env.local`, `.env.dev`, ...

The installation script takes two optional arguments which are `--path` and `--token`. They correspond respectively to the location where the environment will be created (default: current folder) and the github token to have access to private repositories.

The script needs to have the path `$HOME/.local/bin` into your `$PATH` because `wp-cli` will be installed there.  
If it is not, add the following line in your `.bashrc` or `.zschrc` : `export PATH="$HOME/.local/bin:$PATH"` 

To start the installation script : `castor install`.  
Example with all arguments : `castor install --path www --token my-github-token`.

It will install the environment with the [humanity theme](https://github.com/amnestywebsite/humanity-theme) and its required plugins.

## Clever Cloud hosting

on clever cloud hooks in the `clevercloud` directory will be executed at build time and script `infogerance/aif-clever-cloud.php` will replace `wp-config.php`

These environment variables must be defined :

^ name             ^ purpose ^
| WP_HOME          | base URL of the WP admin |
| WP_SITEURL       | base URL of the website |
| MYSQL_ADDON_HOST | host name of the database (should be automatically set) |
| MYSQL_ADDON_PORT | port number of the database (should be automatically set) |
| DB_NAME             | database name (should be automatically set) |
| DB_USER             | database user name (should be automatically set) |
| DB_PASSWORD         | database password (should be automatically set) |
| WP_AUTH_KEY         | random string |
| WP_AUTH_SALT        | random string |
| WP_CACHE_KEY_SALT   | random string |
| WP_LOGGED_IN_KEY    | random string |
| WP_LOGGED_IN_SALT   | random string |
| WP_NONCE_KEY        | random string |
| WP_NONCE_SALT       | random string |
| WP_SECURE_AUTH_KEY  | random string |
| WP_SECURE_AUTH_SALT | random string |

## Update plugins from Github repositories

To update plugins from GitHub repositories, you can use the following script :

`castor update-github-plugins`

To update plugins from private repositories (especially the amnesty branding plugin). You need to pass your Github access token to the script with the option `--token` like this :

`castor update-github-plugins --token my-access-token`

Like the install script, you can specify the path of the WordPress installation with `--path`.

## Build blocks and assets

You need to build 

## CI/CD

puhsing on branch `main` deploys on http://app-dadec8ba-25dc-44d7-b10d-6dd400a829fd.cleverapps.io 

pushing on branch `fairness-dev` deploys on http://app-0feb7822-eaf8-4f15-ba3d-d5d66aca81f2.cleverapps.io

## Custom Plugins

A custom plugin must be developed directly in a folder under `<path>/wp-content/plugins` (example: wp-content/plugins/hello-world).  
This will allow the plugin to be loaded directly into WordPress. Note that it is not activated by default, so you need to activate it in wp-admin.

For your plugin to be versioned on git, you need to add the following line to the plugins section of the `.gitignore` file : `!wp-content/plugins/your-plugin/`

## Start WordPress

To start WordPress, run : `wp server`

## Configuration

### Theme Options

Go in `Options du thème` tab, you can set the logo of the website.

In `Apparence > Menus` tab, you can define the menus that are show in the website (Header and Footer).

### Permalinks

`Réglages > Permaliens` :
- Set the permalink structure to `/%category%/%postname%/`
- Set the category prefix to `.`

### Homepage

You can create a Page in the **Pages** tab.

`Réglages > Lecture` :
- Select **static page** for showing the homepage and select your page as homepage.

### Yoast SEO

Go in `Yoast SEO > Settings` tab and `Advanced > Breadcrumbs`:
- Set `Separator between breadcrumbs` to empty
- Set `Anchor text for the Homepage` to **Accueil**
- Set `Articles post` to **Types de contenu**

