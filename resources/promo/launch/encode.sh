#!/bin/sh
# Grade the master's frames and write the deliverables. One pass reads the frames once.
#
#   ./encode.sh FRAMES_DIR SCORE.wav OUT_DIR [fps]
#
# FRAMES_DIR holds f00000.jpg ... from `node render.mjs --preset=master` (2560x1440, full-range
# BT.601 JPEGs). Writes launch-1440p60.mp4 (the YouTube upload) and launch-1080p60.mp4.
# The grade is grade/grade.fg, the same graph tools/grade-still.sh uses on a still.
# This ffmpeg has no drawtext, subtitles or zscale: captions are in the picture already and
# colour conversion goes through `scale`.
set -e
here=$(cd "$(dirname "$0")" && pwd)
frames=$1; score=$2; out=$3; fps=${4:-60}
FF=${FFMPEG:-/opt/homebrew/bin/ffmpeg}
[ -d "$frames" ] && [ -f "$score" ] || { echo "usage: encode.sh FRAMES_DIR SCORE.wav OUT_DIR [fps]"; exit 1; }
mkdir -p "$out"
first=$(ls "$frames" | grep -E '^f[0-9]{5}\.jpg$' | head -1)
# The film is as long as its frames. The vignette is a looped still, and a blend goes on for as
# long as either input does, so without -t the encode runs to the end of the score instead.
n=$(ls "$frames" | grep -cE '^f[0-9]{5}\.jpg$')
dur=$(python3 -c "print($n / $fps)")
wh=$(/opt/homebrew/bin/ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0:s=x "$frames/$first")
W=${wh%x*}; H=${wh#*x}
vg="$here/grade/vignette-${W}x${H}.png"
[ -f "$vg" ] || python3 "$here/tools/vignette.py" "$W" "$H" "$vg"
# the grade, then to limited-range BT.709 (said so in the stream), then a 1080p copy
grade=$(sed -e "s/@W@/$W/g" -e "s/@H@/$H/g" -e '/^#/d' "$here/grade/grade.fg" | tr -d '\n' | sed -e 's/\[0:v\]format=gbrp16le/[0:v]scale=in_range=pc:in_color_matrix=bt601:flags=accurate_rnd+full_chroma_int,format=gbrp16le/' -e 's/\[out\]$/[g]/')
tail="[g]scale=out_color_matrix=bt709:out_range=tv:flags=accurate_rnd+full_chroma_int:sws_dither=ed,format=yuv420p,split=2[m][s];[s]scale=1920:1080:flags=lanczos[h]"
x264="-c:v libx264 -preset slow -profile:v high -g $((fps / 2)) -bf 2 -flags +cgop -color_range tv -colorspace bt709 -color_primaries bt709 -color_trc bt709"
aac="-c:a aac -b:a 384k -ar 48000 -ac 2 -aac_pns 0 -aac_is 0"
"$FF" -y -hide_banner -loglevel error -stats -threads 6 -framerate "$fps" -i "$frames/f%05d.jpg" -loop 1 -framerate "$fps" -i "$vg" -i "$score" \
  -filter_complex "$grade;$tail" \
  -map "[m]" -map 2:a $x264 -level 5.1 -crf 15 -maxrate 80M -bufsize 160M $aac -t "$dur" -movflags +faststart "$out/launch-1440p60.mp4" \
  -map "[h]" -map 2:a $x264 -crf 16 $aac -t "$dur" -movflags +faststart "$out/launch-1080p60.mp4"
ls -la "$out"/launch-1440p60.mp4 "$out"/launch-1080p60.mp4
