import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

test('shared core covers settings, API client, proxy, request form, rendering, and admin', () => {
  const core = read('shared/core.php');
  for (const fn of [
    'ddys_open_settings',
    'ddys_open_api_request',
    'ddys_open_proxy_response',
    'ddys_open_handle_request_form',
    'ddys_open_render_full_page',
    'ddys_open_admin_page',
    'ddys_open_site_text',
    'ddys_open_json_safe',
  ]) {
    assert.match(core, new RegExp(`function\\s+${fn}\\s*\\(`));
  }
  for (const token of ['movies', 'latest', 'hot', 'search', 'calendar', 'sources', 'collections', 'shares', 'activities', 'types', 'genres', 'regions', 'request_form']) {
    assert.match(core, new RegExp(`'${token}'`));
  }
});

test('DedeCMS V5 and DedeBIZ V6 entry files use the right system paths', () => {
  assert.match(read('dedecms-v5/source/plus/ddys.php'), /include\/common\.inc\.php/);
  assert.match(read('dedecms-v5/source/include/taglib/ddys.lib.php'), /function\s+lib_ddys/);
  assert.match(read('dedecms-v5/source/include/taglib/ddys.lib.php'), /ddys_open\/core\.php/);
  assert.match(read('dedebiz-v6/source/apps/ddys.php'), /system\/common\.inc\.php/);
  assert.match(read('dedebiz-v6/source/system/taglib/ddys.lib.php'), /function\s+lib_ddys/);
  assert.match(read('dedebiz-v6/source/system/taglib/ddys.lib.php'), /ddys_open\/core\.php/);
  assert.match(read('dedecms-v5/source/admin/ddys_open.php'), /CheckPurview\('sys_module'\)/);
  assert.match(read('dedebiz-v6/source/admin/ddys_open.php'), /CheckPurview\('sys_module'\)/);
});

test('module builder emits two Dede module packages with expected paths', () => {
  const out = path.join(os.tmpdir(), 'ddys-dedecms-test-' + Date.now());
  const result = spawnSync(process.execPath, ['tools/build-packages.mjs'], {
    cwd: root,
    env: { ...process.env, DDYS_DEDE_DIST: out },
    encoding: 'utf8',
  });
  assert.equal(result.status, 0, result.stderr || result.stdout);
  const manifest = JSON.parse(fs.readFileSync(path.join(out, 'manifest.json'), 'utf8'));
  assert.equal(manifest.packages.length, 2);

  const v5 = fs.readFileSync(manifest.packages.find((item) => item.id === 'dedecms-v5').xml, 'utf8');
  const v6 = fs.readFileSync(manifest.packages.find((item) => item.id === 'dedebiz-v6').xml, 'utf8');
  assert.match(v5, /\.\.\/plus\/ddys\.php/);
  assert.match(v5, /\.\.\/include\/ddys_open\/core\.php/);
  assert.match(v6, /\.\.\/apps\/ddys\.php/);
  assert.match(v6, /\.\.\/system\/ddys_open\/core\.php/);
  for (const item of manifest.packages) {
    assert.equal(fs.existsSync(item.zip), true);
    assert.equal(item.sha256.length, 64);
  }
});

test('README is detailed, linked, and avoids misleading wording', () => {
  const zh = read('README.zh-CN.md');
  const en = read('README.md');
  assert.match(zh, /低端影视 API/);
  assert.match(zh, /README\.md/);
  assert.match(zh, /ddys-dedecms-v5-module-v0\.1\.0\.zip/);
  assert.match(zh, /ddys-dedebiz-v6-module-v0\.1\.0\.zip/);
  assert.match(zh, /伪静态/);
  assert.match(en, /README\.zh-CN\.md/);
  const forbidden = new RegExp(['DDYS ' + 'Open API', 'Open' + 'AI', 'GP' + 'T', 'third-party ' + 'CDN', '第三方 ' + 'CDN', '不依赖 ' + 'Composer', '不依赖 ' + 'npm'].join('|'));
  assert.doesNotMatch(zh + en, forbidden);
});
