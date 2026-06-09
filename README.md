# Amnesty International France Website

Ceci est le dépôt de la refonte du site web d'Amnesty International France basé sur [humanity theme](https://github.com/amnestywebsite/humanity-theme) [(README)](./README_humanity.md). Le dépôt contient tous les éléments nécessaires au fonctionnement du site, notamment les plugins spécifiques.

This is the repository for the redesign of the Amnesty International France website based on [humanity theme](https://github.com/amnestywebsite/humanity-theme) [(README)](./README_humanity.md). The repository contains all the elements required to run the site, including specific plugins.

## Documentation

- [Architecture overview](./docs/ARCHITECTURE.md)
- [Environments and deployment](./docs/ENVIRONMENTS.md)
- [Mon Espace](./docs/MON-ESPACE.md)
- [Salesforce integration](./docs/SALESFORCE.md)
- [Cloudflare Turnstile integration](./docs/TURNSTILE.md)
- [Main theme](./wp-content/themes/humanity-theme/README.md)
- [Frontend toolchain](./private/README.md)
- [Playwright E2E tests](./private/tests/e2e/README.md)
- [AIF Donor Space plugin](./wp-content/plugins/aif-donor-space/README.md)
- [AIF RSS Importer plugin](./wp-content/plugins/aif-rss-importer/README.md)
- [Interactive Map plugin](./wp-content/plugins/interactive-map/README.md)
- [Prismic Migration plugin](./wp-content/plugins/prismic-migration/README.md)

## Requirements

- Ask for the `.env` file
- PHP 8.2 or higher, as required by `composer.json`
- MySQL version 8.0 or higher or MariaDB version 10.6 or higher
- Composer
- Node.js with Corepack/Yarn 4 for the frontend toolchain
- Docker when using the local `wp-env` WordPress stack
- [Castor](https://github.com/jolicode/castor)

Note : The database can be dockerized as follows :
```
Example with mariadb
Command : docker run --detach --name amnesty -p 3306:3306 --env MARIADB_USER=admin --env MARIADB_PASSWORD=password123 --env MARIADB_DATABASE=amnesty --env MARIADB_ROOT_PASSWORD=root mariadb:latest
DB_HOST value : your IP (like the one of your wifi)
```

Note : Do not use the MySQL `root` account in `.env`. On Ubuntu `root` uses socket authentication, so a password connection fails with `Access denied for user 'root'@'localhost' (1698)`. Create a dedicated user instead, for example :
```
CREATE DATABASE amnesty;
CREATE USER 'amnesty'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON amnesty.* TO 'amnesty'@'localhost';
FLUSH PRIVILEGES;
```

## Installation

First, you need to change `.env` file to provide some information to the script for the creation of the WordPress environment.
You can create different env file like `.env.local`, `.env.dev`, ...

Note : Values containing spaces must be wrapped in double quotes, otherwise the install aborts with `A value containing spaces must be surrounded by quotes`. Example : `WP_TITLE="Amnesty Local"`.

The installation script takes two optional arguments which are `--path` and `--token`. They correspond respectively to the location where the environment will be created (default: current folder) and the github token to have access to private repositories.

The script needs to have the path `$HOME/.local/bin` into your `$PATH` because `wp-cli` will be installed there.
If it is not, add the following line in your `.bashrc` or `.zschrc` : `export PATH="$HOME/.local/bin:$PATH"`

To start the installation script : `castor install`.
Example with all arguments : `castor install --path .` (or even `castor install --path www --token my-github-token`).

It will install the environment with the [humanity theme](https://github.com/amnestywebsite/humanity-theme) and its required plugins.

## Clever Cloud hosting

On Clever Cloud, hooks in the `clevercloud` directory are executed at build time and the script `infogerance/aif-clever-cloud.php` replaces `wp-config.php`.

These environment variables must be defined :

| Name                  | Purpose |
|-----------------------|---------|
| `WP_HOME`             | Base URL of the website |
| `WP_SITEURL`          | Base URL of the WordPress install |
| `MYSQL_ADDON_HOST`    | Database host, usually provided by Clever Cloud |
| `MYSQL_ADDON_PORT`    | Database port, usually provided by Clever Cloud |
| `MYSQL_ADDON_DB`      | Database name, usually provided by Clever Cloud |
| `MYSQL_ADDON_USER`    | Database user, usually provided by Clever Cloud |
| `MYSQL_ADDON_PASSWORD` | Database password, usually provided by Clever Cloud |
| `WP_AUTH_KEY`         | Random string |
| `WP_AUTH_SALT`        | Random string |
| `WP_CACHE_KEY_SALT`   | Random string |
| `WP_LOGGED_IN_KEY`    | Random string |
| `WP_LOGGED_IN_SALT`   | Random string |
| `WP_NONCE_KEY`        | Random string |
| `WP_NONCE_SALT`       | Random string |
| `WP_SECURE_AUTH_KEY`  | Random string |
| `WP_SECURE_AUTH_SALT` | Random string |
| `WP_ENVIRONMENT_TYPE` | Optional WordPress environment type, for example `development`, `staging`, or `production` |

## Update plugins from Github repositories

To update plugins from GitHub repositories, you can use the following script :

`castor update-github-plugins`

To update plugins from private repositories (especially the amnesty branding plugin). You need to pass your Github access token to the script with the option `--token` like this :

`castor update-github-plugins --token my-access-token`

Like the install script, you can specify the path of the WordPress installation with `--path`.

## Build blocks and assets

```bash
cd private
# You will maybe need to enable corepack
yarn install --immutable
yarn build
```

You may need to execute `corepack enable` before (use `sudo corepack enable` if you get an `EACCES` permission error).

## CI/CD

Current deployment workflows are:

- pushes to `main` run `.github/workflows/deploy-release.yml`, use the `RELEASE` environment, deploy over SSH to the staging host, then run `wp update-db-schema`;
- pushes to `prod` run `.github/workflows/deploy-prod.yaml`, use the `PROD` environment, deploy over SSH to the production host, then run `wp --path="$DOCUMENT_ROOT_PROD" update-db-schema`;
- pushes to `fairness-dev` run `.github/workflows/deploy-fairness.yml`, use the `FAIRNESS` environment, and delegate the Clever Cloud deployment to `coopTilleuls/action-clevercloud-deploy`.

## Custom Plugins

A custom plugin must be developed directly in a folder under `<path>/wp-content/plugins` (example: wp-content/plugins/hello-world).
This will allow the plugin to be loaded directly into WordPress. Note that it is not activated by default, so you need to activate it in wp-admin.

For your plugin to be versioned on git, you need to add the following line to the plugins section of the `.gitignore` file : `!wp-content/plugins/your-plugin/`

## Start WordPress

To start the local Docker-based WordPress stack, run :

```bash
cd private
yarn env start --update
```

The default `wp-env` URL is `http://localhost:8888`.

If you use the manual Castor/WP-CLI installation flow instead, you can still run : `wp server` (or with a clean one : `wp cli cache clear && wp cli cache prune && wp cache flush && wp server`)

Note : You can add `--port={xyz}` to `wp server` if the port `{xyz}` is not `8080`.

## Configuration

## Environment Variables

This project needs some environment variables to communicate with Salesforce.
You can create a `.env` file and define these variables :

```
AIF_SALESFORCE_URL=
AIF_SALESFORCE_CLIENT_ID=
AIF_SALESFORCE_SECRET=
AIF_SALESFORCE_ORIGINE__C=
AIF_SALESFORCE_CODE_ORIGINE__C__WEB=
AIF_SALESFORCE_RECORD_TYPE_ID=
AIF_SALESFORCE_CODES_AUWEBAPP=
AIF_SALESFORCE_CODES_AUWEB=
AIF_SALESFORCE_CODES_MILITANT=
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
```

Then, you can add this code in your `wp-config.php` to be able to retrieve them using `getenv` in the application.
```php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
```

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

### Extensions

Go in `Extensions > Extensions installées` :
- Tick the checkbox `Extension` (to select all)
- Select `Action groupées > Activer`
- `Appliquer`

## Import some content from the (pre)prod

Note : Here is for the homepage

<<< /wp-admin in preprod (cf Google Doc for auth)
- Select `Pages > Toutes les pages`
- `Accueil`
- Burger (next to `Enregistrer` button) => `Editeur de code`
- Copy the code
- Burger > `Editeur visuel`

<<< /wp-admin in local
- Select `Page > Ajouter une page`
- `Titre : Accueil` (for instance)
- Burger => `Editeur de code`
- Paste the code
- Burger => `Editeur visuel`
- `Publier`

<<< /wp-admin in local
- Select `Réglages > Lecture`
- `La page d’accueil affiche :` > `Une page statique`
- `Page d’accueil : Accueil`
- `Enregistrer`

If you do not have preprod access yet, you can generate dummy content locally with WP-CLI so the theme has something to display :
```
wp post generate --count=20 --post_type=post --post_status=publish
wp post generate --count=5 --post_type=page --post_status=publish
wp term generate category --count=8
```

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

Current high-level structure of the "Espace Don" plugin:

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

Scripts are added to the plugin via the `aif_donor_space_enqueue_assets` function, located in the `aif-donor-space.php` file.

This function uses the WordPress function [wp_enqueue_script()](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).

### Routing and Page Management

The plugin creates the required donor-space pages from `aif-donor-space.php`.

The `aif_donor_space_create_pages` function ensures the page tree exists. It is called on `init` through `aif_ensure_critical_pages_exist`, protected by a five-minute transient, and on `after_switch_theme`.

The donor-space page templates are mostly selected by the theme, especially from `wp-content/themes/humanity-theme/includes/my-space/template.php`. See the dedicated plugin README for the detailed boundary between the plugin and the theme.

### How to Create/Modify/Delete a Page

Each page is associated with a template. To modify a page, you need to modify the template. To add a page, follow these steps:

1. Create the template in the `templates/` folder when the template belongs to the plugin, or in the theme when it follows the existing theme-owned donor-space pattern.
2. Register the page in the `aif_donor_space_create_pages` function.
3. Associate the template with the page in the relevant theme or plugin template-loading code.
4. Load the page once, or switch the theme, so the page creation hook can create any missing page.

For more details, see [AIF Donor Space plugin](./wp-content/plugins/aif-donor-space/README.md).

## Import Content

See [Prismic migration plugin](./wp-content/plugins/prismic-migration/README.md)
