<?php

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\fs;
use function Castor\http_client;
use function Castor\http_download;
use function Castor\io;
use function Castor\load_dot_env;
use function Castor\run;

#[AsTask(description: 'Install wordpress with humanity theme and plugins')]
function install(string $path = '.'): void
{
    io()->title("Installation of WordPress for Amnesty International France");

    if(!fs()->exists('.env')) {
        io()->error('.env file not found');
        exit(1);
    }

    if(!fs()->exists($path)) {
        fs()->mkdir($path);
    }

    $context = context()->withEnvironment(load_dot_env())->withWorkingDirectory($path)->withAllowFailure();

    $wp_cli_installed = run('wp cli', context: $context->withQuiet());

    if($wp_cli_installed->isSuccessful()) {
        io()->info('wp cli is already installed installed');
    }

    if(!$wp_cli_installed->isSuccessful()) {
        io()->info('wp cli is not installed'.PHP_EOL.'Installation...');

        http_download('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar', './wp-cli.phar');
        $result = run(['php', 'wp-cli.phar', '--info']);
        if(!$result->isSuccessful()) {
            io()->error('Error during the installation of wp cli');
            fs()->remove('wp-cli.phar');
            exit(1);
        }
        fs()->chmod('wp-cli.phar', 0755);
        fs()->copy('./wp-cli.phar', '/home/bas/.local/bin/wp');
        fs()->remove('./wp-cli.phar');
        io()->success('wp cli installation complete');
    }

    $wp_installation = run('wp core is-installed', context: $context->withQuiet());
    if($wp_installation->isSuccessful()) {
        io()->info('A wordpress installation already exists');
    } else {
        io()->info("Starting installation of WordPress environment");
        run('wp core download --locale=$LANG --skip-content --path=.', context: $context);
        run('wp config create --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASSWORD --dbhost=$DB_HOST --dbprefix=$DB_PREFIX', context: $context);
        //run('wp db create', context: $context);
        run('wp core install --locale=$LANG --url=$WP_URL --title="$WP_TITLE" --admin_user=$WP_ADMIN_USER --admin_password=$WP_ADMIN_PASSWORD --admin_email=$WP_ADMIN_EMAIL', context: $context);
        run("wp plugin install cloudflare --activate", context: $context);
    }

    $theme_version = get_github_latest_version('https://github.com/amnestywebsite/humanity-theme/releases/latest');
    run("wp theme install https://github.com/amnestywebsite/humanity-theme/releases/download/$theme_version/humanity-theme.zip --activate", context: $context);
    $cmb2_attached_posts_version = get_github_latest_version('https://github.com/CMB2/cmb2-attached-posts/releases/latest');
    $cmb2_password_field_version = get_github_latest_version('https://github.com/amnestywebsite/cmb2-password-field/releases/latest');
    $cmb2_message_field_version = get_github_latest_version('https://github.com/amnestywebsite/cmb2-message-field/releases/latest');
    $cmb2_field_order_version = get_github_latest_version('https://github.com/jaymcp/cmb2-field-order/releases/latest');
    run("wp plugin install cmb2 \
        https://github.com/CMB2/cmb2-attached-posts/archive/refs/tags/$cmb2_attached_posts_version.zip \
        https://github.com/amnestywebsite/cmb2-password-field/releases/download/$cmb2_password_field_version/cmb2-password-field.zip \
        https://github.com/amnestywebsite/cmb2-message-field/releases/download/$cmb2_message_field_version/cmb2-message-field.zip \
        https://github.com/jaymcp/cmb2-field-order/archive/refs/tags/$cmb2_field_order_version.zip \
        jetpack \
        --activate", context: $context);
    run("wp plugin auto-updates enable --all", context: $context);
}

function get_github_latest_version(string $url): string {
    io()->info('check version for '. $url);
    $location = http_client()->withOptions(['max_redirects' => 0])->request('GET', $url)->getHeaders(false)['location'][0];
    $split = explode('/', $location);
    return $split[array_key_last($split)];
}
