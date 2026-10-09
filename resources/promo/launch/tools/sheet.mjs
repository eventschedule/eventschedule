// A contact sheet of stills, so many frames can be looked at in one picture.
//   node tools/sheet.mjs out/stills/t06.30.png out/stills/t06.90.png ... --out=out/sheet.jpg [--cols=4] [--w=640]
import { spawnSync } from 'node:child_process';
const args = process.argv.slice(2), opt = Object.fromEntries(args.filter(a => a.startsWith('--')).map(a => a.slice(2).split('=')));
const files = args.filter(a => !a.startsWith('--')), cols = +(opt.cols || 4), w = +(opt.w || 640), rows = Math.ceil(files.length / cols);
const inputs = files.flatMap(f => ['-i', f]);
const graph = files.map((_, i) => `[${i}:v]scale=${w}:-2[v${i}]`).join(';') + ';' + files.map((_, i) => `[v${i}]`).join('') + `xstack=inputs=${files.length}:layout=${files.map((_, i) => `${(i % cols) * w}_${Math.floor(i / cols) * Math.round(w * 9 / 16)}`).join('|')}:fill=black[o]`;
const r = spawnSync('/opt/homebrew/bin/ffmpeg', ['-y', '-hide_banner', '-loglevel', 'error', ...inputs, '-filter_complex', files.length > 1 ? graph : `[0:v]scale=${w}:-2[o]`, '-map', '[o]', '-frames:v', '1', '-q:v', '3', opt.out || 'out/sheet.jpg'], { stdio: 'inherit' });
process.exit(r.status || 0);
