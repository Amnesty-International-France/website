# Amnesty International France Website

Ceci est le dépôt de la refonte du site web d'Amnesty International France basé sur [humanity theme](https://github.com/amnestywebsite/humanity-theme). Le dépôt contient tous les éléments nécessaires au fonctionnement du site, notamment les plugins spécifiques.

This is the repository for the redesign of the Amnesty International France website based on [humanity theme](https://github.com/amnestywebsite/humanity-theme). The repository contains all the elements required to run the site, including specific plugins.

## Requirements

- PHP 8.0 or higher
- MySQL version 8.0 or higher or MariaDB version 10.5 or higher
- [Castor](https://github.com/jolicode/castor)

## Installation

The first step is to create your database in mysql : `create database <name>;`.

Then, you need to change `.env` file to provide some information to the script for the creation of the WordPress environment.  
You can create different env file like `.env.local`, `.env.dev`, ...

The script takes one optional argument which is `--path`. It corresponds to the location where the environment will be created (default: current folder).

The script need to have the path `$HOME/.local/bin` into your `$PATH` because `wp-cli` will be installed there.  
If it is not, add the following line in your `.bashrc` or `.zschrc` : `export PATH="$HOME/.local/bin:$PATH"` 

To start the installation script : `castor install`

It will install the environment with the [humanity theme](https://github.com/amnestywebsite/humanity-theme) and its required plugins.

## Update plugins from Github repositories (and install private plugins)

To update plugins from Github repositories (and update humanity theme), you can use the following script :

`castor update-github-plugins`

You can update (or install) plugins from private repositories (especially the amnesty branding plugin). You need to pass a Github access token to the script with the option `--token` like this :

`castor update-github-plugins --token my-access-token`

Like the install script, you can specify the path of the wordpress installation with `--path`.

## CI/CD

puhsing on branch `main` deploys on http://app-dadec8ba-25dc-44d7-b10d-6dd400a829fd.cleverapps.io 

pushing on branch `fairness-dev` deploys on http://app-0feb7822-eaf8-4f15-ba3d-d5d66aca81f2.cleverapps.io

## Custom Plugins

A custom plugin must be developed directly in a folder under `<path>/wp-content/plugins` (example: wp-content/plugins/hello-world).  
This will allow the plugin to be loaded directly into wordpress. Note that it is not activated by default, so you need to activate it in wp-admin.

For your plugin to be versioned on git, you need to add the following line to the plugins section of the `.gitignore` file : `!wp-content/plugins/your-plugin/`

## Start wordpress

To start wordpress, run : `wp server`
