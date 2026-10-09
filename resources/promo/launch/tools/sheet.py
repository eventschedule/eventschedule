"""Contact sheet: tile images into one JPEG so a set of plates can be judged at a glance.

    python3 tools/sheet.py OUT.jpg IMG [IMG ...] [--cols=4] [--cell=640] [--bg=#0b0f1c] [--label]

Each image is fitted inside a cell (never cropped, never enlarged past its own size unless
--upscale). Transparent plates are laid over a checker so a missing background shows. Needs only
Pillow.
"""
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


def font(size):
    for name in ('/System/Library/Fonts/SFNSMono.ttf', '/System/Library/Fonts/Menlo.ttc', '/System/Library/Fonts/Helvetica.ttc'):
        try:
            return ImageFont.truetype(name, size)
        except OSError:
            continue
    return ImageFont.load_default()


def checker(w, h, a=(26, 32, 52), b=(34, 42, 66), s=16):
    im = Image.new('RGB', (w, h), a)
    d = ImageDraw.Draw(im)
    for y in range(0, h, s):
        for x in range((y // s % 2) * s, w, s * 2):
            d.rectangle([x, y, x + s - 1, y + s - 1], fill=b)
    return im


def main(argv):
    opts = {k: v for k, v in (a[2:].split('=', 1) if '=' in a else (a[2:], '1') for a in argv if a.startswith('--'))}
    files = [a for a in argv if not a.startswith('--')]
    out, files = files[0], files[1:]
    cols = int(opts.get('cols', 4))
    cell = int(opts.get('cell', 640))
    ratio = float(opts.get('ratio', 0.625))
    label = 'label' in opts
    bg = opts.get('bg', '#0b0f1c')
    ch = int(cell * ratio)
    pad, lab = 14, (26 if label else 0)
    rows = (len(files) + cols - 1) // cols
    sheet = Image.new('RGB', (cols * (cell + pad) + pad, rows * (ch + lab + pad) + pad), bg)
    d = ImageDraw.Draw(sheet)
    f = font(15)
    for i, name in enumerate(files):
        try:
            im = Image.open(name)
        except Exception as e:  # a broken plate should show as a hole, not stop the sheet
            print(f'skip {name}: {e}', file=sys.stderr)
            continue
        im.load()
        native = f'{im.width}x{im.height}'
        im = im.convert('RGBA')
        k = min(cell / im.width, ch / im.height)
        if k > 1 and 'upscale' not in opts:
            k = 1
        im = im.resize((max(1, round(im.width * k)), max(1, round(im.height * k))), Image.LANCZOS)
        x = pad + (i % cols) * (cell + pad) + (cell - im.width) // 2
        y = pad + (i // cols) * (ch + lab + pad) + (ch - im.height) // 2
        base = checker(im.width, im.height)
        base.paste(im, (0, 0), im)
        sheet.paste(base, (x, y))
        if label:
            lx = pad + (i % cols) * (cell + pad)
            ly = pad + (i // cols) * (ch + lab + pad) + ch + 4
            d.text((lx, ly), f'{Path(name).stem}  {native}', fill='#9fb0d8', font=f)
    sheet.save(out, quality=88)
    print(out, sheet.size)


if __name__ == '__main__':
    main(sys.argv[1:])
