## Overview

This is an **experimental** way of running WordPress using the PHP Process Manager.
It will offer superior performance to the traditional PHP-FPM methods of running PHP code.

There are currently many issues and bugs and it's not possible to even login to wp-admin.
The home page and individual posts should render which is enough to see the performance benefits.

This repo is based on Bedrock which is a modern WordPress stack that helps you get started with the best development tools and project structure.

Much of the philosophy behind Bedrock is inspired by the [Twelve-Factor App](http://12factor.net/) methodology including the [WordPress specific version](https://roots.io/twelve-factor-wordpress/).

## Requirements

- PHP >= 7.1
- Composer - [Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

## Test

1. Clone Repo :
   ```sh
   $ composer install
   ```
2. Update environment variables in the `.env` file. Wrap values that may contain non-alphanumeric characters with quotes, or they may be incorrectly parsed.

- Database variables
  - `DB_NAME` - Database name
  - `DB_USER` - Database user
  - `DB_PASSWORD` - Database password
  - `DB_HOST` - Database host
  - Optionally, you can define `DATABASE_URL` for using a DSN instead of using the variables above (e.g. `mysql://user:password@127.0.0.1:3306/db_name`)
- `WP_ENV` - Set to environment (`development`, `staging`, `production`)
- `WP_HOME` - Full URL to WordPress home (https://example.com)
- `WP_SITEURL` - Full URL to WordPress including subdirectory (https://example.com/wp)
- `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT`, `NONCE_SALT`
  - Generate with [wp-cli-dotenv-command](https://github.com/aaemnnosttv/wp-cli-dotenv-command)
  - Generate with [our WordPress salts generator](https://roots.io/salts.html)

3. Add theme(s) in `web/app/themes/` as you would for a normal WordPress site
4. Set the document root on your webserver to Bedrock's `web` folder: `/path/to/site/web/`
5. Install with wp-cli `wp core install`
6. Run PHP-PM `vendor/bin/ppm`

## Documentation

See src/ for details on how it works
Currently uses composer-patches to make a small tweak to wp-includes/template.php
A mu-plugin is created to fix wp_die and wp_redirect so they do not terminate PHP.


## Issues

wp-admin does not work. It should be possible to get it to work but because of the WP_ADMIN and other constants
it will be necessary to setup separate PHP-PM master processes one for the frontend and
one for the backend. This has not been done yet.

There are many more issues that only testing will discover.

Bedrock documentation is available at [https://roots.io/docs/bedrock/master/installation/](https://roots.io/docs/bedrock/master/installation/).
