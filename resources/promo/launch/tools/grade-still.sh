#!/bin/sh
# Grade one still the way encode.sh grades the film, to judge the look on a frame.
#   tools/grade-still.sh in.png out.png
set -e
here=$(cd "$(dirname "$0")/.." && pwd)
in=$1; out=$2
FF=${FFMPEG:-/opt/homebrew/bin/ffmpeg}
wh=$(/opt/homebrew/bin/ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0:s=x "$in")
W=${wh%x*}; H=${wh#*x}
vg="$here/grade/vignette-${W}x${H}.png"
[ -f "$vg" ] || python3 "$here/tools/vignette.py" "$W" "$H" "$vg"
graph=$(sed -e "s/@W@/$W/g" -e "s/@H@/$H/g" -e '/^#/d' "$here/grade/grade.fg" | tr -d '\n')
"$FF" -y -hide_banner -loglevel error -i "$in" -i "$vg" -filter_complex "$graph" -map "[out]" -frames:v 1 -pix_fmt rgb24 "$out"
