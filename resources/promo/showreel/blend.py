"""Average the sub-frames render.mjs captures into one motion-blurred frame.

Reads length-prefixed JPEGs on stdin: a header of <frame index, sample count> (two uint32 LE)
followed by that many <uint32 LE length, JPEG bytes> records. Writes <outdir>/fNNNNN.jpg.
Needs only Pillow. Averaging is a pairwise tree of ImageChops.add(scale=2), which is an exact
mean for power-of-two sample counts and keeps rounding to one step per level.
"""
import io
import struct
import sys

from PIL import Image, ImageChops

out = sys.argv[1]
src = sys.stdin.buffer


def read(n):
    buf = b''
    while len(buf) < n:
        chunk = src.read(n - len(buf))
        if not chunk:
            return None
        buf += chunk
    return buf


while True:
    hdr = read(8)
    if hdr is None:
        break
    index, count = struct.unpack('<II', hdr)
    frames = []
    for _ in range(count):
        (length,) = struct.unpack('<I', read(4))
        frames.append(Image.open(io.BytesIO(read(length))).convert('RGB'))
    while len(frames) > 1:
        frames = [ImageChops.add(frames[i], frames[i + 1], scale=2.0) if i + 1 < len(frames) else frames[i]
                  for i in range(0, len(frames), 2)]
    frames[0].save(f'{out}/f{index:05d}.jpg', quality=94, subsampling=0)
