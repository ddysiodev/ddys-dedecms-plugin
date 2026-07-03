# 低端影视 DedeCMS / DedeBIZ 模块

中文 | [English](README.md)

[低端影视](https://ddys.io/) API 的官方 DedeCMS / DedeBIZ 模块，用于在织梦站点中展示低端影视内容、提供模板标签、本地 JSON 代理、服务端求片、缓存和后台诊断。

- GitHub 仓库：[ddysiodev/ddys-dedecms-plugin](https://github.com/ddysiodev/ddys-dedecms-plugin)
- GitHub Release：[v0.1.0](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/tag/v0.1.0)
- DedeCMS V5 安装包：[ddys-dedecms-v5-module-v0.1.0.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.0/ddys-dedecms-v5-module-v0.1.0.zip)
- DedeBIZ V6 安装包：[ddys-dedebiz-v6-module-v0.1.0.zip](https://github.com/ddysiodev/ddys-dedecms-plugin/releases/download/v0.1.0/ddys-dedebiz-v6-module-v0.1.0.zip)

## 版本选择

- DedeCMS V5：使用 `ddys-dedecms-v5-module-v0.1.0.zip`。
- DedeBIZ V6：使用 `ddys-dedebiz-v6-module-v0.1.0.zip`。

DedeBIZ 开启安全模式时，后台模块管理会禁止安装模块，需要先按站点运维策略关闭安全模式或改用手工释放文件。

## 功能

- 后台配置：API Base URL、站点来源 URL、API Key、缓存 TTL、展示主题、布局、列数、链接打开方式、求片开关、伪静态基础路径。
- 模板标签：最新、影片库、热门、搜索、日历、影片详情、资源、相关、评论、片单、分享、求片、动态、用户、类型、分类、地区、求片表单。
- 独立页面：最新、影片库、热门、搜索、日历、影片详情、资源、相关、评论、片单、分享、求片、动态、用户和字典页面。
- 本地 JSON 代理：前端请求本站，由站点服务端访问低端影视 API。
- 服务端求片：API Key 只保存在服务器，支持 nonce、蜜罐字段、同 IP 限流和年份校验。
- 缓存：文件缓存存放在 `data/ddys_open/cache`，后台可清理。
- 编码兼容：模块包声明 `utf-8`，DedeCMS 模块安装器会按站点编码转换文件；运行时对 API 返回文本做站点编码适配。
- 静态资源：CSS、JS 和图标来自主站图标集。
- 安装/卸载：通过 Dede 后台模块管理安装和卸载。

## 安装

1. 下载与你站点匹配的 Release 安装包。
2. 进入后台：`模块` -> `模块管理` -> `上传新模块`。
3. 文件格式选择“经过 zip 压缩的模块包”，上传 zip。
4. 进入模块详情并安装，已有文件按你的站点策略选择覆盖或保留备份。
5. 安装完成后进入后台左侧“低端影视 API”配置页。
6. 点击“测试低端影视 API”，确认服务器可以访问接口。

## 后台配置

常用配置：

```text
API Base URL: https://ddys.io/api/v1
站点来源 URL: https://ddys.io
API Key: 可选；求片、评论、关注等服务端鉴权功能需要
默认缓存 TTL: 300
最新/热门缓存 TTL: 300
列表缓存 TTL: 600
详情缓存 TTL: 1800
社区缓存 TTL: 120
```

## 模板标签

在首页、频道页、内容页模板中使用：

```html
{dede:ddys type='latest' row='12'/}
{dede:ddys type='movies' per_page='24'/}
{dede:ddys type='hot' row='10'/}
{dede:ddys type='search' q='星际' per_page='12'/}
{dede:ddys type='calendar' year='2026' month='7'/}
{dede:ddys type='movie' slug='i-robot'/}
{dede:ddys type='sources' slug='i-robot'/}
{dede:ddys type='related' slug='i-robot'/}
{dede:ddys type='comments' slug='i-robot'/}
{dede:ddys type='collections' per_page='10'/}
{dede:ddys type='shares' per_page='10'/}
{dede:ddys type='requests' per_page='10'/}
{dede:ddys type='activities' per_page='10'/}
{dede:ddys type='types'/}
{dede:ddys type='genres'/}
{dede:ddys type='regions'/}
{dede:ddys type='request_form'/}
```

常用参数：

```text
row / limit: 数量
page / per_page: 分页
slug: 影片或片单 slug
id: 分享 ID
username: 用户名
year / month: 日历
type / genre / region / sort: 筛选
layout: grid / list / compact
theme: auto / light / dark
columns: 1-6
```

## 独立页面

DedeCMS V5：

```text
/plus/ddys.php
/plus/ddys.php?view=movies
/plus/ddys.php?view=hot
/plus/ddys.php?view=search&q=星际
/plus/ddys.php?view=calendar&year=2026&month=7
/plus/ddys.php?view=movie&slug=i-robot
/plus/ddys.php?view=sources&slug=i-robot
/plus/ddys.php?view=related&slug=i-robot
/plus/ddys.php?view=comments&slug=i-robot
/plus/ddys.php?view=collections
/plus/ddys.php?view=shares
/plus/ddys.php?view=requests
/plus/ddys.php?view=activities
/plus/ddys.php?view=types
```

DedeBIZ V6：

```text
/apps/ddys.php
/apps/ddys.php?view=movies
/apps/ddys.php?view=hot
/apps/ddys.php?view=search&q=星际
/apps/ddys.php?view=movie&slug=i-robot
```

## 本地 JSON 代理

DedeCMS V5：

```text
/plus/ddys_api.php?route=latest&limit=12
/plus/ddys_api.php?route=hot&limit=10
/plus/ddys_api.php?route=search&q=星际&type=movie
/plus/ddys_api.php?route=calendar&year=2026&month=7
/plus/ddys_api.php?route=movie&slug=i-robot
/plus/ddys_api.php?route=sources&slug=i-robot
/plus/ddys_api.php?route=comments&slug=i-robot
/plus/ddys_api.php?route=collections&per_page=10
/plus/ddys_api.php?route=shares&per_page=10
/plus/ddys_api.php?route=types
```

DedeBIZ V6 把 `/plus/` 换成 `/apps/`。

## 求片

1. 后台启用“求片表单”。
2. 填写 API Key。
3. 在模板放入 `{dede:ddys type='request_form'/}`，或访问 `view=requests` 页面。

服务端会校验 nonce、蜜罐字段、同 IP 提交间隔、片名长度、年份格式和年份范围。

## 伪静态

开启后台“启用伪静态链接”后，可参考下面规则。

Apache：

```apache
RewriteEngine On
RewriteRule ^ddys/?$ plus/ddys.php [L,QSA]
RewriteRule ^ddys/api/?$ plus/ddys_api.php [L,QSA]
RewriteRule ^ddys/request-submit/?$ plus/ddys_request.php [L,QSA]
RewriteRule ^ddys/movie/([^/]+)/?$ plus/ddys.php?view=movie&slug=$1 [L,QSA]
RewriteRule ^ddys/movie/([^/]+)/(sources|related|comments)/?$ plus/ddys.php?view=$2&slug=$1 [L,QSA]
RewriteRule ^ddys/(movies|hot|search|calendar|collections|shares|requests|activities|types|genres|regions)/?$ plus/ddys.php?view=$1 [L,QSA]
```

DedeBIZ V6 把规则中的 `plus/` 改为 `apps/`。

Nginx：

```nginx
rewrite ^/ddys/?$ /plus/ddys.php last;
rewrite ^/ddys/api/?$ /plus/ddys_api.php last;
rewrite ^/ddys/request-submit/?$ /plus/ddys_request.php last;
rewrite ^/ddys/movie/([^/]+)/?$ /plus/ddys.php?view=movie&slug=$1 last;
rewrite ^/ddys/movie/([^/]+)/(sources|related|comments)/?$ /plus/ddys.php?view=$2&slug=$1 last;
rewrite ^/ddys/(movies|hot|search|calendar|collections|shares|requests|activities|types|genres|regions)/?$ /plus/ddys.php?view=$1 last;
```

## 开发检查

```bash
node tools/check.mjs
node --test tests/*.test.mjs
node tools/build-packages.mjs
```

检查覆盖模块 XML 生成、V5/V6 路径、模板标签、代理、求片、缓存、安全文案、静态资源和 Release 包结构。
