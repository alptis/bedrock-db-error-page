# alptis/bedrock-db-error-page

Composer plugin that customizes the database connection error page of a [Bedrock](https://roots.io/bedrock/) WordPress project.

When WordPress cannot connect to its database, it displays a bare "Error establishing a database connection" message. This plugin replaces it with a customizable HTML page served with a `503 Service Unavailable` status and a `Retry-After` header, so that browsers, crawlers and load balancers understand the outage is temporary.

## How it works

On every `composer install` and `composer update`, the plugin copies two files into `WP_CONTENT_DIR` (`web/app` in a standard Bedrock project):

| File | Behavior |
| --- | --- |
| `db-error.php` | Always overwritten, so that you get the latest version of the drop-in. |
| `custom_db_error.html` | Created from the default template only if it does not exist yet. Your changes are never overwritten. |

`db-error.php` is a WordPress [drop-in](https://developer.wordpress.org/reference/functions/dead_db/) that WordPress loads instead of its own error message when the database is unavailable: when the connection fails (`wpdb::db_connect()`), when the database selection fails or when the connection is lost (`dead_db()`). The provided drop-in renders `custom_db_error.html`.

It does not replace the global `$wpdb` object: the `db.php` drop-in is left untouched, so the plugin stays compatible with any plugin providing its own (Query Monitor, HyperDB, LudicrousDB...).

> [!NOTE]
> When `WP_DEBUG` and `WP_DEBUG_DISPLAY` are both enabled, WordPress shows its own detailed message when the database selection fails, and PHP warnings may be displayed above the error page when the connection fails. This only affects development environments.

## Requirements

- PHP 8.0 or later with the `mysqli` extension
- Composer 2
- A Bedrock project (the plugin loads `config/application.php` to resolve `WP_CONTENT_DIR`)

## Installation

```shell
composer require alptis/bedrock-db-error-page
```

Composer will ask you to allow the plugin. Accept it, which adds the following to your root `composer.json`:

```json
"config": {
  "allow-plugins": {
    "alptis/bedrock-db-error-page": true
  }
}
```

## Customization

Edit `web/app/custom_db_error.html` to match your site. It is a static HTML file: since the database is unavailable when it is displayed, it cannot rely on WordPress functions, themes or options.

You will probably want to commit `web/app/db-error.php` and `web/app/custom_db_error.html` to your project repository, or at least `custom_db_error.html` if `db-error.php` is ignored.

## Healthcheck integration

WordPress connects to its database before loading plugins, so a healthcheck plugin cannot report a database outage on its own: the request stops at the error page.

When [alptis/wordpress-healthcheck](https://packagist.org/packages/alptis/wordpress-healthcheck) is also installed with Composer, the drop-in hands healthcheck requests over to it before rendering the error page. The healthcheck endpoints then keep answering with their usual JSON report (HTTP 200), with the `wordpress_database` check marked as critical. The MySQL error is passed along to be written to the PHP error log, never displayed. Every other URL still gets the error page.

```shell
composer require alptis/wordpress-healthcheck
```

## Development

```shell
composer install
composer lint      # check coding standards (PSR-12)
composer lint:fix  # automatically fix what can be fixed
```

## License

This project is released under the [MIT License](LICENSE).

## Disclaimer

This project is not affiliated with or endorsed by [Roots](https://roots.io/) or the [WordPress Foundation](https://wordpressfoundation.org/). Bedrock and WordPress are mentioned only to describe what this package works with.
