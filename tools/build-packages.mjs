import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const version = '0.1.0';
const outputRoot = path.resolve(process.env.DDYS_DEDE_DIST || path.join(os.tmpdir(), 'ddys-dedecms-plugin-dist'));

const sharedFiles = {
  core: 'shared/core.php',
  css: 'shared/static/css/frontend.css',
  js: 'shared/static/js/frontend.js',
  icon16: 'shared/static/images/icon-16.png',
  icon32: 'shared/static/images/icon-32.png',
  icon192: 'shared/static/images/icon-192.png',
  icon512: 'shared/static/images/icon-512.png',
  logo: 'shared/static/images/logo.png',
};

const targets = [
  {
    id: 'dedecms-v5',
    name: '低端影视 API DedeCMS V5 模块',
    zipName: `ddys-dedecms-v5-module-v${version}.zip`,
    xmlName: `ddys-dedecms-v5-module-v${version}.xml`,
    hash: md5('ddys-dedecms-v5-module'),
    indexUrl: 'ddys_open.php',
    files: [
      ['dedecms-v5/source/admin/ddys_open.php', 'ddys_open.php'],
      ['dedecms-v5/source/plus/ddys.php', '../plus/ddys.php'],
      ['dedecms-v5/source/plus/ddys_api.php', '../plus/ddys_api.php'],
      ['dedecms-v5/source/plus/ddys_request.php', '../plus/ddys_request.php'],
      ['dedecms-v5/source/include/taglib/ddys.lib.php', '../include/taglib/ddys.lib.php'],
      [sharedFiles.core, '../include/ddys_open/core.php'],
      [sharedFiles.css, '../plus/ddys_open_static/css/frontend.css'],
      [sharedFiles.js, '../plus/ddys_open_static/js/frontend.js'],
      [sharedFiles.icon16, '../plus/ddys_open_static/images/icon-16.png'],
      [sharedFiles.icon32, '../plus/ddys_open_static/images/icon-32.png'],
      [sharedFiles.icon192, '../plus/ddys_open_static/images/icon-192.png'],
      [sharedFiles.icon512, '../plus/ddys_open_static/images/icon-512.png'],
      [sharedFiles.logo, '../plus/ddys_open_static/images/logo.png'],
    ],
    setup: 'dedecms-v5/module/setup.php',
    uninstall: 'dedecms-v5/module/uninstall.php',
  },
  {
    id: 'dedebiz-v6',
    name: '低端影视 API DedeBIZ V6 模块',
    zipName: `ddys-dedebiz-v6-module-v${version}.zip`,
    xmlName: `ddys-dedebiz-v6-module-v${version}.xml`,
    hash: md5('ddys-dedebiz-v6-module'),
    indexUrl: 'ddys_open.php',
    files: [
      ['dedebiz-v6/source/admin/ddys_open.php', 'ddys_open.php'],
      ['dedebiz-v6/source/apps/ddys.php', '../apps/ddys.php'],
      ['dedebiz-v6/source/apps/ddys_api.php', '../apps/ddys_api.php'],
      ['dedebiz-v6/source/apps/ddys_request.php', '../apps/ddys_request.php'],
      ['dedebiz-v6/source/system/taglib/ddys.lib.php', '../system/taglib/ddys.lib.php'],
      [sharedFiles.core, '../system/ddys_open/core.php'],
      [sharedFiles.css, '../static/ddys_open/css/frontend.css'],
      [sharedFiles.js, '../static/ddys_open/js/frontend.js'],
      [sharedFiles.icon16, '../static/ddys_open/images/icon-16.png'],
      [sharedFiles.icon32, '../static/ddys_open/images/icon-32.png'],
      [sharedFiles.icon192, '../static/ddys_open/images/icon-192.png'],
      [sharedFiles.icon512, '../static/ddys_open/images/icon-512.png'],
      [sharedFiles.logo, '../static/ddys_open/images/logo.png'],
    ],
    setup: 'dedebiz-v6/module/setup.php',
    uninstall: 'dedebiz-v6/module/uninstall.php',
  },
];

function md5(value) {
  return crypto.createHash('md5').update(value).digest('hex');
}

function read(file, encoding = null) {
  return fs.readFileSync(path.join(root, file), encoding);
}

function b64(file) {
  return read(file).toString('base64');
}

function esc(value) {
  return String(value).replace(/[<>&]/g, (char) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;' }[char]));
}

function menuString() {
  return [
    "<m:top name='低端影视 API' c='6,' display='block' rank='sys_module'>",
    "<m:item name='低端影视 API 设置' link='ddys_open.php' rank='sys_module' target='main' />",
    '</m:top>',
  ].join('\n');
}

function readmeHtml(target) {
  return `<h2>${target.name}</h2>
<p>安装后进入后台“低端影视 API 设置”，配置 API Base URL、API Key、缓存、展示样式和求片开关。</p>
<p>前台页面入口：${target.id === 'dedecms-v5' ? '/plus/ddys.php' : '/apps/ddys.php'}。</p>
<p>模板标签示例：{dede:ddys type='latest' row='12'/}、{dede:ddys type='movie' slug='i-robot'/}。</p>`;
}

function oldFileList(target) {
  return target.files.map((item) => item[1]).join('\n');
}

function moduleXml(target) {
  const fileNodes = target.files.map(([source, dest]) => {
    return `<file type='file' name='${esc(dest)}'>\n${b64(source)}\n</file>`;
  }).join('\n');

  return `<module>
<baseinfo>
name=${target.name}
team=ddysiodev
time=2026-07-03
email=dev@ddys.io
url=https://github.com/ddysiodev/ddys-dedecms-plugin
hash=${target.hash}
indexname=低端影视 API 设置
indexurl=${target.indexUrl}
ismember=0
autosetup=0
autodel=0
lang=utf-8
moduletype=soft
</baseinfo>
<systemfile>
<menustring>
${Buffer.from(menuString()).toString('base64')}
</menustring>
<readme>
${Buffer.from(readmeHtml(target)).toString('base64')}
</readme>
<setupsql40>
</setupsql40>
<delsql>
</delsql>
<setup>
${b64(target.setup)}
</setup>
<uninstall>
${b64(target.uninstall)}
</uninstall>
<oldfilelist>
${oldFileList(target)}
</oldfilelist>
</systemfile>
<modulefiles>
${fileNodes}
</modulefiles>
</module>
`;
}

function compressXml(xmlPath, zipPath) {
  const stage = path.join(outputRoot, '.stage-' + path.basename(zipPath, '.zip'));
  fs.rmSync(stage, { recursive: true, force: true });
  fs.mkdirSync(stage, { recursive: true });
  fs.copyFileSync(xmlPath, path.join(stage, path.basename(xmlPath)));
  fs.rmSync(zipPath, { force: true });
  const command = `Compress-Archive -Path '${stage.replaceAll("'", "''")}\\*' -DestinationPath '${zipPath.replaceAll("'", "''")}' -Force`;
  const result = spawnSync('powershell.exe', ['-NoProfile', '-Command', command], { encoding: 'utf8' });
  if (result.status !== 0) {
    throw new Error(result.stderr || result.stdout || 'Compress-Archive failed');
  }
  fs.rmSync(stage, { recursive: true, force: true });
}

fs.rmSync(outputRoot, { recursive: true, force: true });
fs.mkdirSync(outputRoot, { recursive: true });

const manifest = [];
for (const target of targets) {
  const xmlPath = path.join(outputRoot, target.xmlName);
  const zipPath = path.join(outputRoot, target.zipName);
  fs.writeFileSync(xmlPath, moduleXml(target));
  compressXml(xmlPath, zipPath);
  manifest.push({
    id: target.id,
    hash: target.hash,
    xml: xmlPath,
    zip: zipPath,
    sha256: crypto.createHash('sha256').update(fs.readFileSync(zipPath)).digest('hex'),
    size: fs.statSync(zipPath).size,
  });
}

fs.writeFileSync(path.join(outputRoot, 'manifest.json'), JSON.stringify({ version, packages: manifest }, null, 2));
console.log(JSON.stringify({ outputRoot, version, packages: manifest }, null, 2));
