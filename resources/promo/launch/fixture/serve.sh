#!/bin/bash
# Serves the launch film's private copy, and proves the thing listening is that copy.
#
#   fixture/serve.sh COPY_DIR            start (the port is the one in the copy's APP_URL)
#   fixture/serve.sh COPY_DIR --stop     stop the listeners that are this copy's, by PID
#   fixture/serve.sh COPY_DIR --status   say whether it is up and whose it is
#
# Other sessions serve copies of the same app on this machine, and one of them once took a port
# between a check and a start. So "ours" is decided by what a listener IS: a php -S whose command
# line names this copy's path. Never by port alone, and never with pkill.
set -euo pipefail

COPY="${1:?usage: serve.sh COPY_DIR [--stop|--status]}"
MODE="${2:-}"
RUN="$(dirname "$COPY")"
PIDFILE="$RUN/launchfilm-serve.pid"
LOG="$RUN/launchfilm-serve.log"
PORT="$(grep -E '^APP_URL=' "$COPY/.env" | sed -E 's/.*:([0-9]+).*/\1/')"

case "$PORT" in 8000|9515|8073|'') echo "refusing port '$PORT'" >&2; exit 1;; esac

listeners() { lsof -t -nP -iTCP:"$PORT" -sTCP:LISTEN 2>/dev/null || true; }
# The listeners whose command line contains this copy's path.
ours() { for p in $(listeners); do if ps -o command= -p "$p" | grep -qF "$COPY/"; then echo "$p"; fi; done; return 0; }
others() { for p in $(listeners); do if ! ps -o command= -p "$p" | grep -qF "$COPY/"; then echo "$p"; fi; done; return 0; }

if [ "$MODE" = "--stop" ]; then
  if [ -f "$PIDFILE" ]; then P="$(cat "$PIDFILE")"; kill $(pgrep -P "$P" 2>/dev/null) "$P" 2>/dev/null || true; rm -f "$PIDFILE"; fi
  W="$(ours)"; if [ -n "$W" ]; then kill $W 2>/dev/null || true; fi
  sleep 0.5
  if [ -n "$(ours)" ]; then echo "some of our workers are still up: $(ours)" >&2; exit 1; fi
  echo "stopped"; exit 0
fi

if [ "$MODE" = "--status" ]; then
  if [ -z "$(listeners)" ]; then echo "down (port $PORT free)"; exit 1; fi
  if [ -n "$(others)" ]; then echo "port $PORT is held by a process that is NOT this copy: $(others)"; exit 2; fi
  echo "up on $PORT; pid file $(cat "$PIDFILE" 2>/dev/null || echo none); listeners $(ours | tr '\n' ' ')(all name this copy's path)"; exit 0
fi

if [ -n "$(others)" ]; then echo "port $PORT is taken by something that is not this copy ($(others)): remake the copy on another port" >&2; exit 1; fi
if [ -n "$(ours)" ]; then echo "already up on $PORT (pid file $(cat "$PIDFILE" 2>/dev/null || echo none))"; exit 0; fi

# Eight workers: with one, the app deadlocks when it fetches its own pictures while drawing.
(cd "$COPY" && PHP_CLI_SERVER_WORKERS=8 nohup php artisan serve --host=127.0.0.1 --port="$PORT" --tries=1 --no-reload > "$LOG" 2>&1 & echo $! > "$PIDFILE")

for i in $(seq 1 80); do
  if curl -fsS -o /dev/null --max-time 5 "http://127.0.0.1:$PORT/up" 2>/dev/null; then break; fi
  sleep 0.5
done

if [ -z "$(ours)" ] || [ -n "$(others)" ]; then echo "after starting, the listener on $PORT is not this copy; see $LOG" >&2; exit 1; fi
CODE="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 "http://127.0.0.1:$PORT/indigo-room" || true)"
echo "up on $PORT, pid $(cat "$PIDFILE"); listeners $(ours | tr '\n' ' '); /indigo-room answers $CODE"
