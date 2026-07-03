# DDYS DedeCMS / DedeBIZ Module

English | [中文](README.zh-CN.md)

Official DedeCMS / DedeBIZ module for the [DDYS](https://ddys.io/) API. It adds DDYS content rendering, template tags, local JSON proxy, server-side request submission, caching, and admin diagnostics to Dede-powered sites.

- Repository: [ddysiodev/ddys-dedecms-plugin](https://github.com/ddysiodev/ddys-dedecms-plugin)
- GitHub Release: [v0.1.1](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/tag/v0.1.1)
- DedeCMS V5 package: [ddys-dedecms-v5-module-v0.1.1.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.1/ddys-dedecms-v5-module-v0.1.1.zip)
- DedeBIZ V6 package: [ddys-dedebiz-v6-module-v0.1.1.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.1/ddys-dedebiz-v6-module-v0.1.1.zip)

## Package Choice

- Use `ddys-dedecms-v5-module-v0.1.1.zip` for DedeCMS V5.
- Use `ddys-dedebiz-v6-module-v0.1.1.zip` for DedeBIZ V6.

DedeBIZ safe mode disables module management, so disable it according to your operational policy before installing through the admin panel.

## Features

- Admin settings for API base URL, site source URL, API key, cache TTLs, theme, layout, columns, source links, request form, and pretty URL base path.
- Template tag `{dede:ddys .../}` for latest, movie list, hot list, search, calendar, movie detail, sources, related items, comments, collections, collection detail, shares, share detail, requests, activities, user detail, and dictionaries.
- Standalone pages for major list and detail views.
- Local JSON proxy so browsers can call the local site instead of the remote API directly.
- Server-side request form with nonce, honeypot field, IP rate limiting, title length checks, and year validation.
- File cache under `data/ddys_open/cache`, with admin cache clearing.
- DedeCMS charset handling for API text output.
- Install and uninstall through Dede module management.

## Install

1. Download the package for your site version from the Release page.
2. Open admin: `Modules` -> `Module Management` -> `Upload Module`.
3. Choose the zip module package option and upload the zip.
4. Install the uploaded module.
5. Open the new DDYS API settings page.
6. Run the API connection test, then save your display and request-form settings.

## Template Tag

```html
{dede:ddys type='latest' row='12'/}
{dede:ddys type='movies' per_page='24'/}
{dede:ddys type='hot' row='10'/}
{dede:ddys type='search' q='interstellar' per_page='12'/}
{dede:ddys type='calendar' year='2026' month='7'/}
{dede:ddys type='movie' slug='i-robot'/}
{dede:ddys type='sources' slug='i-robot'/}
{dede:ddys type='related' slug='i-robot'/}
{dede:ddys type='comments' slug='i-robot' per_page='20'/}
{dede:ddys type='collections' per_page='10'/}
{dede:ddys type='collection' slug='classic-sci-fi'/}
{dede:ddys type='shares' per_page='10'/}
{dede:ddys type='share' id='1'/}
{dede:ddys type='requests' per_page='10'/}
{dede:ddys type='activities' per_page='10'/}
{dede:ddys type='user' username='demo'/}
{dede:ddys type='types'/}
{dede:ddys type='genres'/}
{dede:ddys type='regions'/}
{dede:ddys type='request_form'/}
```

Common parameters: `row`, `limit`, `page`, `per_page`, `slug`, `id`, `username`, `year`, `month`, `type`, `genre`, `region`, `sort`, `layout`, `theme`, and `columns`.

## Pages

DedeCMS V5:

```text
/plus/ddys.php
/plus/ddys.php?view=movies
/plus/ddys.php?view=hot
/plus/ddys.php?view=search&q=interstellar
/plus/ddys.php?view=calendar&year=2026&month=7
/plus/ddys.php?view=movie&slug=i-robot
/plus/ddys.php?view=sources&slug=i-robot
/plus/ddys.php?view=related&slug=i-robot
/plus/ddys.php?view=comments&slug=i-robot
/plus/ddys.php?view=collections
/plus/ddys.php?view=collection&slug=classic-sci-fi
/plus/ddys.php?view=shares
/plus/ddys.php?view=share&id=1
/plus/ddys.php?view=requests
/plus/ddys.php?view=activities
/plus/ddys.php?view=user&username=demo
/plus/ddys.php?view=types
```

DedeBIZ V6 uses `/apps/` instead of `/plus/`.

## Local JSON Proxy

```text
/plus/ddys_api.php?route=latest&limit=12
/plus/ddys_api.php?route=hot&limit=10
/plus/ddys_api.php?route=search&q=interstellar&type=movie
/plus/ddys_api.php?route=calendar&year=2026&month=7
/plus/ddys_api.php?route=movie&slug=i-robot
/plus/ddys_api.php?route=sources&slug=i-robot
/plus/ddys_api.php?route=comments&slug=i-robot
/plus/ddys_api.php?route=collections&per_page=10
/plus/ddys_api.php?route=collection&slug=classic-sci-fi
/plus/ddys_api.php?route=shares&per_page=10
/plus/ddys_api.php?route=share&id=1
/plus/ddys_api.php?route=user&username=demo
/plus/ddys_api.php?route=types
```

DedeBIZ V6 uses `/apps/ddys_api.php`.

## Pretty URLs

Enable pretty URLs in the admin page, then add rewrite rules for your server.

Apache:

```apache
RewriteEngine On
RewriteRule ^ddys/?$ plus/ddys.php [L,QSA]
RewriteRule ^ddys/api/?$ plus/ddys_api.php [L,QSA]
RewriteRule ^ddys/request-submit/?$ plus/ddys_request.php [L,QSA]
RewriteRule ^ddys/movie/([^/]+)/?$ plus/ddys.php?view=movie&slug=$1 [L,QSA]
RewriteRule ^ddys/movie/([^/]+)/(sources|related|comments)/?$ plus/ddys.php?view=$2&slug=$1 [L,QSA]
RewriteRule ^ddys/collection/([^/]+)/?$ plus/ddys.php?view=collection&slug=$1 [L,QSA]
RewriteRule ^ddys/share/([0-9]+)/?$ plus/ddys.php?view=share&id=$1 [L,QSA]
RewriteRule ^ddys/user/([^/]+)/?$ plus/ddys.php?view=user&username=$1 [L,QSA]
RewriteRule ^ddys/(movies|hot|search|calendar|collections|shares|requests|activities|types|genres|regions)/?$ plus/ddys.php?view=$1 [L,QSA]
```

Nginx:

```nginx
rewrite ^/ddys/?$ /plus/ddys.php last;
rewrite ^/ddys/api/?$ /plus/ddys_api.php last;
rewrite ^/ddys/request-submit/?$ /plus/ddys_request.php last;
rewrite ^/ddys/movie/([^/]+)/?$ /plus/ddys.php?view=movie&slug=$1 last;
rewrite ^/ddys/movie/([^/]+)/(sources|related|comments)/?$ /plus/ddys.php?view=$2&slug=$1 last;
rewrite ^/ddys/collection/([^/]+)/?$ /plus/ddys.php?view=collection&slug=$1 last;
rewrite ^/ddys/share/([0-9]+)/?$ /plus/ddys.php?view=share&id=$1 last;
rewrite ^/ddys/user/([^/]+)/?$ /plus/ddys.php?view=user&username=$1 last;
rewrite ^/ddys/(movies|hot|search|calendar|collections|shares|requests|activities|types|genres|regions)/?$ /plus/ddys.php?view=$1 last;
```

IIS URL Rewrite:

```xml
<rewrite>
  <rules>
    <rule name="DDYS page" stopProcessing="true">
      <match url="^ddys/?$" />
      <action type="Rewrite" url="plus/ddys.php" appendQueryString="true" />
    </rule>
    <rule name="DDYS api" stopProcessing="true">
      <match url="^ddys/api/?$" />
      <action type="Rewrite" url="plus/ddys_api.php" appendQueryString="true" />
    </rule>
    <rule name="DDYS request" stopProcessing="true">
      <match url="^ddys/request-submit/?$" />
      <action type="Rewrite" url="plus/ddys_request.php" appendQueryString="true" />
    </rule>
    <rule name="DDYS movie detail" stopProcessing="true">
      <match url="^ddys/movie/([^/]+)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view=movie&amp;slug={R:1}" appendQueryString="true" />
    </rule>
    <rule name="DDYS movie subview" stopProcessing="true">
      <match url="^ddys/movie/([^/]+)/(sources|related|comments)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view={R:2}&amp;slug={R:1}" appendQueryString="true" />
    </rule>
    <rule name="DDYS collection detail" stopProcessing="true">
      <match url="^ddys/collection/([^/]+)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view=collection&amp;slug={R:1}" appendQueryString="true" />
    </rule>
    <rule name="DDYS share detail" stopProcessing="true">
      <match url="^ddys/share/([0-9]+)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view=share&amp;id={R:1}" appendQueryString="true" />
    </rule>
    <rule name="DDYS user detail" stopProcessing="true">
      <match url="^ddys/user/([^/]+)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view=user&amp;username={R:1}" appendQueryString="true" />
    </rule>
    <rule name="DDYS list pages" stopProcessing="true">
      <match url="^ddys/(movies|hot|search|calendar|collections|shares|requests|activities|types|genres|regions)/?$" />
      <action type="Rewrite" url="plus/ddys.php?view={R:1}" appendQueryString="true" />
    </rule>
  </rules>
</rewrite>
```

DedeBIZ V6 uses `apps/` instead of `plus/`.

## Development

```bash
node tools/check.mjs
node --test tests/*.test.mjs
node tools/build-packages.mjs
```

The checks cover module XML generation, V5/V6 paths, template tag support, proxy, request form, cache, security wording, static assets, and package structure.
