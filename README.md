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

## Amnesty International France - Donor Space Plugin


**Amnesty International France Donor Space** is distributed as a plugin.

### Prerequisites

Before activating the plugin, ensure that you have added the following environment variables.

#### SalesForce Configuration

This plugin requires access to SalesForce. To enable SalesForce integration, please add the following environment variables:

| Name                     | Description                                           |
|--------------------------|-------------------------------------------------------|
| `AIF_SALESFORCE_URL`     | The URL of the SalesForce instance used by the plugin |
| `AIF_SALESFORCE_CLIENT_ID` | The `client_id` from SalesForce                      |
| `AIF_SALESFORCE_SECRET`  | The `client_secret` from SalesForce                   |

To obtain these details, please contact Amnesty International France.

#### MailGun Configuration

This plugin requires access to MailGun for email sending. To enable MailGun, please add the following environment variables:

| Name                  | Description                                    |
|-----------------------|------------------------------------------------|
| `AIF_MAILGUN_TOKEN`   | The MailGun Token                              |
| `AIF_MAILGUN_URL`     | The MailGun URL                                |
| `AIF_MAILGUN_DOMAIN`  | The domain name used for sending emails        |

For more information, visit [MailGun](https://app.mailgun.com/).

### Plugin Structure

Here is the structure of the "Espace Don" plugin as of 20/02/25:

```
├── assets
│   ├── css // contains the "style.css" file with all the custom CSS for the donation space
│   └── js // contains the JS for the donation space
├── includes
│   ├── domain // contains all the "business" functions of the donation space
│   │   ├── 2FA // contains everything related to two-factor authentication
│   │   ├── bank // contains all functions related to users' bank information
│   │   ├── contact  // contains all functions related to communication methods with Amnesty
│   │   └── tax-receipt // contains everything related to managing tax receipts
│   └── sales-force // contains all utility functions for interacting with Salesforce
└── templates // contains all templates associated with a page
    └── partials // contains "partials" (reusable UI components on the pages)
```

### FAQ

### How to configure the donor space as sub route "/espace-don"

To achieve this just revert the commit `9efc8e981e0eb55891f3dbd917f04b7ec3550d83`

### How to manage CSS inside the plugin ?

#### Some Principles

Each class is prefixed with `aif` to minimize the risk of collision.

Initially, assuming the CSS would remain limited, we adopted a [Tailwind](https://tailwindcss.com/) approach by creating utility CSS classes (e.g., `.aif-mt1w` to add margin-top).

Some UI elements needed to be built (as they did not exist in the theme), and this approach showed its limitations. Therefore, part of the CSS uses the ["Block Element Modifier" (BEM)](https://getbem.com/) naming convention.

#### How is CSS managed in the Plugin ?

Styles are added to the plugin via the `aif_donor_space_enqueue_assets` function, located in the `aif-donor-space.php` file.

This function uses the WordPress function [wp_enqueue_style()](https://developer.wordpress.org/reference/functions/wp_enqueue_style/).

### JS Management

#### Some Principles

We have tried to minimize the use of JavaScript as much as possible. The JS files are located in the `assets/js` folder.

#### How is JS Included in the Plugin?

Styles are added to the plugin via the `aif_donor_space_enqueue_assets` function, located in the `aif-donor-space.php` file.

This function uses the WordPress function [wp_enqueue_script()](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).

### Routing and Page Management

The plugin's routing is managed by two functions located in the `aif-donor-space.php` file.

The first function, `aif_donor_space_create_pages`, creates the directory structure, and the second function, `aif_donor_space_load_template`, links a page to a template.

These two functions are triggered by a WordPress hook when the plugin is activated.

### How to Create/Modify/Delete a Page

Each page is associated with a template. To modify a page, you need to modify the template. To add a page, follow these steps:

1. Create the template in the `/template` folder.
2. Register the page in the `aif_donor_space_create_pages` function.
3. Associate the template with the page in the `aif_donor_space_load_template` function.
4. Deactivate and reactivate the plugin in the admin interface.
