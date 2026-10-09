"""Build what the film needs beside each plate, and the manifest the page loads.

For every plate under plates/ (PNG or WebP, any depth; names holding '@' are outputs and skipped):
  <id>@blur.webp  960 px wide and softened: reflections, and slabs standing out of focus
  <id>@glow.png   48 px wide, saturated: scaled up behind a slab it is the light the screen throws
and plates/manifest.js, a classic script (a page opened from disk cannot fetch JSON) that sets
window.PLATES = {id: {src, blur, glow, w, h, tint: 'r,g,b'}}. `tint` is the colour the screen
throws on the floor: its mean, pushed toward its most saturated pixels.

Needs only Pillow.  python3 tools/variants.py [plates_dir]
"""
import colorsys
import json
import os
import sys

from PIL import Image, ImageEnhance, ImageFilter

root = os.path.abspath(sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), '..', 'plates'))
out = {}
for folder, _, files in os.walk(root):
    for name in sorted(files):
        base, ext = os.path.splitext(name)
        if ext.lower() not in ('.png', '.webp', '.jpg') or '@' in base or base.startswith('_'):
            continue
        path = os.path.join(folder, name)
        rel = os.path.relpath(path, root)
        pid = os.path.splitext(rel)[0].replace(os.sep, '/')
        im = Image.open(path)
        w, h = im.size
        rgb = im.convert('RGBA')
        flat = Image.new('RGBA', rgb.size, (7, 11, 24, 255))
        flat.alpha_composite(rgb)
        flat = flat.convert('RGB')
        blur_path = os.path.join(folder, base + '@blur.webp')
        glow_path = os.path.join(folder, base + '@glow.png')
        if not os.path.exists(blur_path) or os.path.getmtime(blur_path) < os.path.getmtime(path):
            bw = min(960, w)
            b = flat.resize((bw, max(1, round(h * bw / w))), Image.LANCZOS).filter(ImageFilter.GaussianBlur(bw / 190))
            b.save(blur_path, quality=86, method=4)
            g = flat.resize((48, max(1, round(h * 48 / w))), Image.LANCZOS).filter(ImageFilter.GaussianBlur(2.2))
            ImageEnhance.Color(g).enhance(1.9).save(glow_path)
        # the colour it throws: weight each pixel of a small copy by its saturation and value
        s = flat.resize((40, max(1, round(h * 40 / w))), Image.LANCZOS)
        acc = [0.0, 0.0, 0.0]; wsum = 0.0
        for r, g_, b_ in s.getdata():
            hh, ss, vv = colorsys.rgb_to_hsv(r / 255, g_ / 255, b_ / 255)
            k = .05 + ss * vv * vv
            acc[0] += r * k; acc[1] += g_ * k; acc[2] += b_ * k; wsum += k
        tint = [acc[i] / wsum for i in range(3)]
        hh, ss, vv = colorsys.rgb_to_hsv(*[c / 255 for c in tint])
        tint = [round(c * 255) for c in colorsys.hsv_to_rgb(hh, min(1, ss * 1.25 + .08), max(.55, min(1, vv * 1.5)))]
        lum = sum(.2126 * r + .7152 * g_ + .0722 * b_ for r, g_, b_ in s.getdata()) / (255 * s.size[0] * s.size[1])
        out[pid] = {'lum': round(lum, 3), 'src': 'plates/' + rel.replace(os.sep, '/'), 'blur': 'plates/' + os.path.relpath(blur_path, root).replace(os.sep, '/'),
                    'glow': 'plates/' + os.path.relpath(glow_path, root).replace(os.sep, '/'), 'w': w, 'h': h, 'tint': ','.join(map(str, tint))}
# the maps shot with the plates (where a seat, a field or a day is, in plate pixels)
data = {}
for name in sorted(os.listdir(root)):
    if name.endswith('.json') and name != 'MANIFEST.json':
        data[name[:-5]] = json.load(open(os.path.join(root, name)))
with open(os.path.join(root, 'manifest.js'), 'w') as f:
    f.write('// Written by tools/variants.py. Do not edit.\nwindow.PLATES = ' + json.dumps(out, indent=1, sort_keys=True) + ';\n')
    f.write('window.PDATA = ' + json.dumps(data, separators=(',', ':')) + ';\n')
print(f'{len(out)} plates -> {os.path.join(root, "manifest.js")}')
