import assert from 'node:assert/strict';
import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const requiredFiles = [
  'README.zh-CN.md',
  'README.md',
  'LICENSE',
  'shared/core.php',
  'shared/static/css/frontend.css',
  'shared/static/js/frontend.js',
  'shared/static/images/icon-16.png',
  'shared/static/images/icon-32.png',
  'shared/static/images/icon-192.png',
  'shared/static/images/icon-512.png',
  'shared/static/images/logo.png',
  'dedecms-v5/source/plus/ddys.php',
  'dedecms-v5/source/plus/ddys_api.php',
  'dedecms-v5/source/plus/ddys_request.php',
  'dedecms-v5/source/include/taglib/ddys.lib.php',
  'dedecms-v5/source/admin/ddys_open.php',
  'dedecms-v5/module/setup.php',
  'dedecms-v5/module/uninstall.php',
  'dedebiz-v6/source/apps/ddys.php',
  'dedebiz-v6/source/apps/ddys_api.php',
  'dedebiz-v6/source/apps/ddys_request.php',
  'dedebiz-v6/source/system/taglib/ddys.lib.php',
  'dedebiz-v6/source/admin/ddys_open.php',
  'dedebiz-v6/module/setup.php',
  'dedebiz-v6/module/uninstall.php',
  'tools/build-packages.mjs',
];

const requiredCoreFunctions = [
  'ddys_open_settings',
  'ddys_open_storage_save_config',
  'ddys_open_api_request',
  'ddys_open_http_request',
  'ddys_open_proxy_response',
  'ddys_open_handle_request_form',
  'ddys_open_render',
  'ddys_open_render_full_page',
  'ddys_open_admin_page',
  'ddys_open_cache_clear',
  'ddys_open_rate_limit',
  'ddys_open_site_text',
  'ddys_open_json_safe',
];

const requiredViews = [
  'latest', 'movies', 'hot', 'search', 'calendar', 'movie', 'sources', 'related',
  'comments', 'collections', 'collection', 'shares', 'share', 'requests',
  'activities', 'user', 'types', 'genres', 'regions', 'request_form',
];

const forbiddenText = [
  'DDYS ' + 'Open API',
  'Open' + 'AI',
  'GP' + 'T',
  'third-party ' + 'CDN',
  '第三方 ' + 'CDN',
  '不依赖 ' + 'Composer',
  '不依赖 ' + 'npm',
];

function read(file) {
  return fs.readFileSync(path.join(root, file), 'utf8');
}

function walk(dir) {
  const out = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['node_modules', 'vendor', '.git', 'dist', 'build'].includes(entry.name)) continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...walk(full));
    else out.push(full);
  }
  return out;
}

function rel(file) {
  return path.relative(root, file).replaceAll('\\', '/');
}

function isText(file) {
  return /\.(php|js|mjs|css|md|txt|json|gitignore)$/i.test(file);
}

function checkPhpShape(file) {
  const text = read(file);
  assert.equal(text.charCodeAt(0) === 0xfeff, false, `${file} has BOM`);
  assert.equal(text.includes('\uFFFD'), false, `${file} has replacement chars`);
  assert.doesNotMatch(text, /\?>\s*$/, `${file} should not end with closing PHP tag`);
  let state = 'code';
  const stack = [];
  const pairs = { ')': '(', ']': '[', '}': '{' };
  for (let i = 0; i < text.length; i++) {
    const ch = text[i];
    const next = text[i + 1] || '';
    if (state === 'line') {
      if (ch === '\n') state = 'code';
      continue;
    }
    if (state === 'block') {
      if (ch === '*' && next === '/') {
        state = 'code';
        i++;
      }
      continue;
    }
    if (state === 'single') {
      if (ch === '\\') i++;
      else if (ch === "'") state = 'code';
      continue;
    }
    if (state === 'double') {
      if (ch === '\\') i++;
      else if (ch === '"') state = 'code';
      continue;
    }
    if (ch === '/' && next === '/') {
      state = 'line';
      i++;
      continue;
    }
    if (ch === '/' && next === '*') {
      state = 'block';
      i++;
      continue;
    }
    if (ch === '#') {
      state = 'line';
      continue;
    }
    if (ch === "'") {
      state = 'single';
      continue;
    }
    if (ch === '"') {
      state = 'double';
      continue;
    }
    if (ch === '(' || ch === '[' || ch === '{') stack.push(ch);
    if (pairs[ch]) assert.equal(stack.pop(), pairs[ch], `${file} has unmatched ${ch}`);
  }
  assert.equal(state, 'code', `${file} ends inside ${state}`);
  assert.equal(stack.length, 0, `${file} has unmatched brackets`);
}

function pngSize(file) {
  const buffer = fs.readFileSync(path.join(root, file));
  assert.equal(buffer.toString('ascii', 1, 4), 'PNG', `${file} is not PNG`);
  return [buffer.readUInt32BE(16), buffer.readUInt32BE(20)];
}

function buildPackages() {
  const out = path.join(os.tmpdir(), 'ddys-dedecms-check-' + crypto.randomBytes(8).toString('hex'));
  const result = spawnSync(process.execPath, ['tools/build-packages.mjs'], {
    cwd: root,
    env: { ...process.env, DDYS_DEDE_DIST: out },
    encoding: 'utf8',
  });
  assert.equal(result.status, 0, result.stderr || result.stdout);
  const manifest = JSON.parse(fs.readFileSync(path.join(out, 'manifest.json'), 'utf8'));
  return { out, manifest };
}

for (const file of requiredFiles) {
  assert.equal(fs.existsSync(path.join(root, file)), true, `missing ${file}`);
}

for (const file of walk(root)) {
  const relative = rel(file);
  assert.doesNotMatch(relative, /(^|\/)(\.env|node_modules|vendor|dist|build)(\/|$)|\.(zip|log|tmp|bak)$/i, `forbidden repository file: ${relative}`);
  if (!isText(relative)) continue;
  const text = fs.readFileSync(file, 'utf8');
  assert.equal(text.includes('\uFFFD'), false, `${relative} has replacement chars`);
  for (const forbidden of forbiddenText) {
    assert.equal(text.includes(forbidden), false, `${relative} contains forbidden text: ${forbidden}`);
  }
}

for (const file of walk(root).filter((item) => item.endsWith('.php'))) {
  checkPhpShape(rel(file));
}

const core = read('shared/core.php');
for (const fn of requiredCoreFunctions) {
  assert.match(core, new RegExp(`function\\s+${fn}\\s*\\(`), `missing ${fn}`);
}
for (const view of requiredViews) {
  assert.match(core, new RegExp(`'${view}'`), `missing view ${view}`);
}
assert.match(core, /proxy_path/);
assert.match(core, /ddys_website/);
assert.match(core, /\^\[0-9\]\{4\}\$/);
assert.match(core, /1900-2099/);
assert.doesNotMatch(core, /[^a-z_](eval|assert)\s*\(/i);

const v5Tag = read('dedecms-v5/source/include/taglib/ddys.lib.php');
const v6Tag = read('dedebiz-v6/source/system/taglib/ddys.lib.php');
assert.match(v5Tag, /function\s+lib_ddys/);
assert.match(v6Tag, /function\s+lib_ddys/);

for (const [file, size] of Object.entries({
  'shared/static/images/icon-16.png': 16,
  'shared/static/images/icon-32.png': 32,
  'shared/static/images/icon-192.png': 192,
  'shared/static/images/icon-512.png': 512,
  'shared/static/images/logo.png': 32,
})) {
  assert.deepEqual(pngSize(file), [size, size], `${file} size mismatch`);
}

const { out, manifest } = buildPackages();
assert.equal(manifest.version, '0.1.0');
assert.equal(manifest.packages.length, 2);
const v5 = fs.readFileSync(manifest.packages.find((item) => item.id === 'dedecms-v5').xml, 'utf8');
const v6 = fs.readFileSync(manifest.packages.find((item) => item.id === 'dedebiz-v6').xml, 'utf8');
for (const token of ['<baseinfo>', '<systemfile>', '<modulefiles>', '<setup>', '<uninstall>', 'moduletype=soft', 'lang=utf-8']) {
  assert.match(v5, new RegExp(token.replace(/[<>]/g, (m) => `\\${m}`)));
  assert.match(v6, new RegExp(token.replace(/[<>]/g, (m) => `\\${m}`)));
}
for (const token of ['../plus/ddys.php', '../include/taglib/ddys.lib.php', '../include/ddys_open/core.php', '../plus/ddys_open_static/css/frontend.css', 'ddys_open.php']) {
  assert.match(v5, new RegExp(token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
}
for (const token of ['../apps/ddys.php', '../system/taglib/ddys.lib.php', '../system/ddys_open/core.php', '../static/ddys_open/css/frontend.css', 'ddys_open.php']) {
  assert.match(v6, new RegExp(token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
}
for (const item of manifest.packages) {
  assert.equal(fs.existsSync(item.zip), true, `${item.zip} missing`);
  assert.equal(item.size > 100000, true, `${item.zip} unexpectedly small`);
  assert.equal(item.sha256.length, 64, 'sha length');
}

const readmeZh = read('README.zh-CN.md');
const readmeEn = read('README.md');
assert.match(readmeZh, /低端影视 API/);
assert.match(readmeZh, /DedeCMS V5/);
assert.match(readmeZh, /DedeBIZ V6/);
assert.match(readmeZh, /伪静态/);
assert.match(readmeEn, /README\.zh-CN\.md/);

console.log(`DedeCMS package checks passed. Build output: ${out}`);
