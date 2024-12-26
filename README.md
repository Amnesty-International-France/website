# Amnesty International France Website

Ceci est le dépôt de la refonte du site web d'Amnesty International France basé sur [humanity theme](https://github.com/amnestywebsite/humanity-theme). Le dépôt contient tous les éléments nécessaires au fonctionnement du site, notamment les plugins spécifiques.

This is the repository for the redesign of the Amnesty International France website based on [humanity theme](https://github.com/amnestywebsite/humanity-theme). The repository contains all the elements required to run the site, including specific plugins.

## Requirements

- PHP 8.0 or higher
- MySQL version 8.0 or higher or MariaDB version 10.5 or higher
- [Castor](https://github.com/jolicode/castor)

## Installation

The first step is to change `.env` file to provide some information to the script for the creation of the WordPress environment.  
You can create different env file like `.env.local`, `.env.dev`, ...

The script takes one argument which is the path. It corresponds to the location where the environment will be created (default: current folder).

To start the script : `castor install <path>`

It will install the environment with the [humanity theme](https://github.com/amnestywebsite/humanity-theme) and its required plugins.

## CI/CD

puhsing on branch `main` deploys on http://app-dadec8ba-25dc-44d7-b10d-6dd400a829fd.cleverapps.io 

pushing on branch `fairness-dev` deploys on http://app-0feb7822-eaf8-4f15-ba3d-d5d66aca81f2.cleverapps.io

## Custom Plugins

A custom plugin must be developed directly in a folder under `<path>/wp-content/plugins` (example: wp-content/plugins/hello-world).  
This will allow the plugin to be loaded directly into wordpress. Note that it is not activated by default, so you need to activate it in wp-admin.

For your plugin to be versioned on git, you need to add the following line to the plugins section of the `.gitignore` file : `!wp-content/plugins/your-plugin/`

## Start wordpress

To start wordpress, run : `wp server`

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
