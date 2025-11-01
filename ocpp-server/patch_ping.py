import re, sys

path = "server.py"
src = open(path, "r", encoding="utf-8").read()

# If already patched, do nothing
if "ping_interval=" in src:
    print("already patched")
    sys.exit(0)

m = re.search(r'serve\s*\(', src)
if not m:
    print("ERROR: serve( not found")
    sys.exit(1)

# Find matching closing parenthesis for this call
i = m.end()
depth = 1
j = i
while j < len(src):
    c = src[j]
    if c == '(':
        depth += 1
    elif c == ')':
        depth -= 1
        if depth == 0:
            break
    j += 1

if j >= len(src) or depth != 0:
    print("ERROR: could not match parentheses of serve() call")
    sys.exit(1)

args = src[i:j].strip()
injection = "ping_interval=30, ping_timeout=20"
new_args = (args + (", " if args else "") + injection)

patched = src[:i] + new_args + src[j:]
open(path, "w", encoding="utf-8").write(patched)
print("patched")
