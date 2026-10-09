"""The vignette as a 16-bit mask (ffmpeg's own vignette filter is 8-bit and bands on navy).
python3 tools/vignette.py WIDTH HEIGHT OUT.png [strength]   -> a grey image to multiply the frame by."""
import sys
from PIL import Image

w, h, out = int(sys.argv[1]), int(sys.argv[2]), sys.argv[3]
k = float(sys.argv[4]) if len(sys.argv) > 4 else .3
sw, sh = 256, 144
px = []
for y in range(sh):
    for x in range(sw):
        dx, dy = (x + .5) / sw * 2 - 1, (y + .5) / sh * 2 - 1
        d = (dx * dx * .82 + dy * dy * 1.0) ** .5            # a little wider than tall
        v = 1 - k * max(0.0, min(1.0, (d - .42) / .78)) ** 1.7
        px.append(int(v * 65535))
im = Image.new('I;16', (sw, sh)); im.putdata(px)
im.resize((w, h), Image.BICUBIC).save(out)
