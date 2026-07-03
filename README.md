# DDYS DedeCMS / DedeBIZ Module

[中文](README.zh-CN.md) | English

Official DedeCMS / DedeBIZ module for the [DDYS](https://ddys.io/) API. It adds DDYS content rendering, template tags, local JSON proxy, server-side request submission, caching, and admin diagnostics.

- Repository: [ddysiodev/ddys-dedecms-plugin](https://github.com/ddysiodev/ddys-dedecms-plugin)
- GitHub Release: [v0.1.0](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/tag/v0.1.0)
- DedeCMS V5 package: [ddys-dedecms-v5-module-v0.1.0.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.0/ddys-dedecms-v5-module-v0.1.0.zip)
- DedeBIZ V6 package: [ddys-dedebiz-v6-module-v0.1.0.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.0/ddys-dedebiz-v6-module-v0.1.0.zip)

## Packages

- Use `ddys-dedecms-v5-module-v0.1.0.zip` for DedeCMS V5.
- Use `ddys-dedebiz-v6-module-v0.1.0.zip` for DedeBIZ V6.

DedeBIZ safe mode disables module management, so disable it according to your operational policy before installing through the admin panel.

## Features

- Admin settings for API base URL, site URL, API key, cache TTLs, theme, layout, columns, source links, request form, and pretty URL base path.
- Template tag `{dede:ddys .../}` for latest, movies, hot, search, calendar, movie detail, sources, related, comments, collections, shares, requests, activities, users, and dictionaries.
- Standalone pages for all major views.
- Local JSON proxy so browsers can call the local site instead of the remote API directly.
- Server-side request form with nonce, honeypot field, IP rate limiting, title length checks, and year validation.
- File cache under `data/ddys_open/cache`.
- DedeCMS charset handling for API text output.
- Install and uninstall through Dede module management.

## Install

1. Download the package for your site version.
2. Open admin: `Modules` -> `Module Management` -> `Upload Module`.
3. Choose the zip module package option and upload the zip.
4. Install the uploaded module.
5. Open the new DDYS API settings page.
6. Run the API connection test.

## Template Tag

```html
{dede:ddys type='latest' row='12'/}
{dede:ddys type='movies' per_page='24'/}
{dede:ddys type='hot' row='10'/}
{dede:ddys type='search' q='interstellar'/}
{dede:ddys type='calendar' year='2026' month='7'/}
{dede:ddys type='movie' slug='i-robot'/}
{dede:ddys type='sources' slug='i-robot'/}
{dede:ddys type='collections'/}
{dede:ddys type='request_form'/}
```

## Pages

DedeCMS V5:

```text
/plus/ddys.php
/plus/ddys.php?view=movies
/plus/ddys.php?view=hot
/plus/ddys.php?view=search&q=interstellar
/plus/ddys.php?view=movie&slug=i-robot
/plus/ddys_api.php?route=latest&limit=12
/plus/ddys_request.php
```

DedeBIZ V6 uses `/apps/` instead of `/plus/`.

## Development

```bash
node tools/check.mjs
node --test tests/*.test.mjs
node tools/build-packages.mjs
```

The checks cover module XML generation, V5/V6 paths, template tag support, proxy, request form, cache, security wording, static assets, and package structure.
