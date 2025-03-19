<?php

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\fs;
use function Castor\http_client;
use function Castor\http_download;
use function Castor\io;
use function Castor\load_dot_env;
use function Castor\run;

const PUBLIC_PLUGINS = [
    "cmb2-attached-posts" => [
        "repo_url" => "https://github.com/CMB2/cmb2-attached-posts/",
        "zip_url" => "https://github.com/CMB2/cmb2-attached-posts/archive/refs/tags/%version%.zip"
    ],
    "cmb2-password-field" => [
        "repo_url" => "https://github.com/amnestywebsite/cmb2-password-field/",
        "zip_url" => "https://github.com/amnestywebsite/cmb2-password-field/releases/download/%version%/cmb2-password-field.zip"
    ],
    "cmb2-message-field" => [
        "repo_url" => "https://github.com/amnestywebsite/cmb2-message-field/",
        "zip_url" => "https://github.com/amnestywebsite/cmb2-message-field/releases/download/%version%/cmb2-message-field.zip"
    ],
    "cmb2-field-order" => [
        "repo_url" => "https://github.com/jaymcp/cmb2-field-order/",
        "zip_url" => "https://github.com/jaymcp/cmb2-field-order/archive/refs/tags/%version%.zip"
    ]
];

const PRIVATE_PLUGINS = [
    "wp-plugin-amnesty-branding" => [
        "repo_owner" => "amnestywebsite"
    ]
];

#[AsTask(description: 'Install wordpress with humanity theme and plugins')]
function install(string $path = '.', string $token = ''): void
{
    io()->title("Installation of WordPress for Amnesty International France");

    if(!fs()->exists('.env')) {
        io()->error('.env file not found');
        return;
    }

    if(!fs()->exists($path)) {
        fs()->mkdir($path);
    }

    $context = context()->withEnvironment(load_dot_env())->withWorkingDirectory($path)->withAllowFailure();

    $wp_cli_installed = run('wp cli', context: $context->withQuiet());
    if(!$wp_cli_installed->isSuccessful()) {
        io()->info('wp cli is not installed'.PHP_EOL.'Installation...');

        http_download('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar', './wp-cli.phar');
        $result = run(['php', 'wp-cli.phar', '--info']);
        if(!$result->isSuccessful()) {
            io()->error('Error during the installation of wp cli');
            fs()->remove('wp-cli.phar');
            return;
        }
        fs()->chmod('wp-cli.phar', 0755);
        fs()->copy('./wp-cli.phar', getenv('HOME').'/.local/bin/wp');
        fs()->remove('./wp-cli.phar');
        io()->success('wp cli installation complete');

        // If WP has not been added to $PATH
        $wp_cli_installed = run('wp cli', context: $context->withQuiet());
        if(!$wp_cli_installed->isSuccessful()) {
            io()->error('$HOME/.local/bin has not been added to your $PATH.\n Add the following line to your .bashrc or .zschrc : \'export PATH="$HOME/.local/bin:$PATH"\'');
            return;
        }
    }

    // Test if a WordPress installation exists
    $wp_installation = run('wp core is-installed', context: $context->withQuiet());
    if($wp_installation->isSuccessful()) {
        io()->info('A wordpress installation already exists.');
        return;
    }

    // Make the installation
    io()->info("Starting installation of WordPress environment");
    run('wp core download --locale=fr_FR --skip-content --path='.$path, context: $context);
    run('wp config create --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASSWORD --dbhost=$DB_HOST --dbprefix=$DB_PREFIX', context: $context);

	$db_exists = run("wp db check", context: $context->withQuiet())->isSuccessful();
	if( !$db_exists ) {
		run('wp db create', context: $context);
	}

    run('wp core install --locale=fr_FR --url=$WP_URL --title="$WP_TITLE" --admin_user=$WP_ADMIN_USER --admin_password=$WP_ADMIN_PASSWORD --admin_email=$WP_ADMIN_EMAIL', context: $context);

    io()->info("Core installed.".PHP_EOL."Installing required plugins...");
    run("wp plugin install cloudflare --activate", context: $context);
	run("wp plugin install cmb2 jetpack --activate", context: $context);

    run("wp theme activate humanity-theme", context: $context);

    foreach (PUBLIC_PLUGINS as $plugin => $plugin_data) {
        $plugin_version = get_github_latest_version("{$plugin_data['repo_url']}releases/latest");
        $zip_url = str_replace("%version%", $plugin_version, $plugin_data['zip_url']);
        run("wp plugin install $zip_url --activate", context: $context);
    }

	if($token) {
		foreach (PRIVATE_PLUGINS as $plugin => $plugin_data) {
			$last_plugin_release = http_client()->withOptions([
				'headers' => [
					'Accept' => 'application/vnd.github+json'
				],
				'auth_bearer' => $token,
			])->request('GET', "https://api.github.com/repos/{$plugin_data['repo_owner']}/$plugin/releases/latest")->getContent();
			$last_plugin_release_json = json_decode($last_plugin_release, true);

			$zipball_url = $last_plugin_release_json['zipball_url'];
			http_download($zipball_url, $path."/$plugin.zip", options: ['auth_bearer' => $token]);

			run("wp plugin install $plugin.zip --activate", context: $context);
			fs()->remove($path."/$plugin.zip");
			io()->success("Plugin $plugin updated.");
		}
	}

    run("wp plugin auto-updates enable --all", context: $context);
}

#[AsTask(description: 'update theme and plugins from github repositories')]
function update_github_plugins(string $path = '.', string $token = ''): void {
    io()->title("Update the theme and required plugins from github repositories");

    $context = context()->withEnvironment(load_dot_env())->withWorkingDirectory($path)->withAllowFailure();

    // Check public plugins
    foreach (PUBLIC_PLUGINS as $plugin => $plugin_data) {
        $actual_plugin_version = run("wp plugin get $plugin --field=version", context: $context)->getOutput();
        $remote_version = get_github_latest_version("{$plugin_data['repo_url']}releases/latest");
        if( ! str_contains($actual_plugin_version, substr($remote_version, 1)) ) {
            $zip_url = str_replace("%version%", $remote_version, $plugin_data['zip_url']);
            run("wp plugin install $zip_url --force", context: $context);
        } else {
            io()->info("Plugin $plugin is already up to date.");
        }
    }

    // Check private plugins
    if( $token ) {
        foreach (PRIVATE_PLUGINS as $plugin => $plugin_data) {
            $is_installed = run("wp plugin is-installed $plugin", context: $context)->isSuccessful();

            $last_plugin_release = http_client()->withOptions([
                'headers' => [
                    'Accept' => 'application/vnd.github+json'
                ],
                'auth_bearer' => $token,
            ])->request('GET', "https://api.github.com/repos/{$plugin_data['repo_owner']}/$plugin/releases/latest")->getContent();
            $last_plugin_release_json = json_decode($last_plugin_release, true);

            if( $is_installed ) {
                $actual_plugin_version = run("wp plugin get $plugin --field=version", context: $context)->getOutput();
                if( str_contains($actual_plugin_version, substr($last_plugin_release_json['tag_name'], 1))) {
                    io()->info("Plugin $plugin is already up to date.");
                    continue;
                }
            }

            $zipball_url = $last_plugin_release_json['zipball_url'];
            http_download($zipball_url, $path."/$plugin.zip", options: ['auth_bearer' => $token]);

            run("wp plugin install $plugin.zip --force", context: $context);
            fs()->remove($path."/$plugin.zip");
            io()->success("Plugin $plugin updated.");
        }
    }
}

function get_github_latest_version(string $url): string {
    io()->info('check version for '. $url);
    $location = http_client()->withOptions(['max_redirects' => 0])->request('GET', $url)->getHeaders(false)['location'][0];
    $split = explode('/', $location);
    return $split[array_key_last($split)];
}
