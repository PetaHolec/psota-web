import json, math
t = json.load(open('c50.json'))
sx, sy = t['transform']['scale']; tx, ty = t['transform']['translate']
arcs = []
for a in t['arcs']:
    x = y = 0; pts = []
    for dx, dy in a:
        x += dx; y += dy; pts.append((x * sx + tx, y * sy + ty))
    arcs.append(pts)
def arc(i): return arcs[i] if i >= 0 else arcs[~i][::-1]
def ring(idx):
    pts = []
    for i in idx:
        a = arc(i); pts += a if not pts else a[1:]
    return pts
# Lambert conformal conic
p1, p2, lat0, lon0 = map(math.radians, (36, 62, 50, 15))
n = math.log(math.cos(p1) / math.cos(p2)) / math.log(math.tan(math.pi/4 + p2/2) / math.tan(math.pi/4 + p1/2))
F = math.cos(p1) * math.tan(math.pi/4 + p1/2) ** n / n
rho0 = F / math.tan(math.pi/4 + lat0/2) ** n
def lcc(lon, lat):
    lon, lat = math.radians(lon), math.radians(lat)
    rho = F / math.tan(math.pi/4 + lat/2) ** n
    th = n * (lon - lon0)
    return rho * math.sin(th), rho0 - rho * math.cos(th)
# fit window (lon/lat box sampled along edges)
LON0, LON1, LAT0, LAT1 = -9.5, 38.5, 36.0, 60.0
edge = []
for i in range(0, 101):
    f = i / 100
    for lon, lat in ((LON0 + f*(LON1-LON0), LAT0), (LON0 + f*(LON1-LON0), LAT1), (LON0, LAT0 + f*(LAT1-LAT0)), (LON1, LAT0 + f*(LAT1-LAT0))):
        edge.append(lcc(lon, lat))
xs = [p[0] for p in edge]; ys = [p[1] for p in edge]
minx, maxx, miny, maxy = min(xs), max(xs), min(ys), max(ys)
W = 1000.0; scale = W / (maxx - minx); H = (maxy - miny) * scale
def proj(lon, lat):
    x, y = lcc(lon, lat); return ((x - minx) * scale, (maxy - y) * scale)
print('H', H)
def dp(pts, eps):
    if len(pts) < 3: return pts
    a, b = pts[0], pts[-1]; dmax = 0; idx = 0
    dx, dy = b[0]-a[0], b[1]-a[1]; L = math.hypot(dx, dy) or 1e-9
    for i in range(1, len(pts)-1):
        d = abs(dy*(pts[i][0]-a[0]) - dx*(pts[i][1]-a[1])) / L
        if d > dmax: dmax, idx = d, i
    if dmax > eps: return dp(pts[:idx+1], eps)[:-1] + dp(pts[idx:], eps)
    return [a, b]
def clip(poly, x0, y0, x1, y1):
    def clip_edge(pts, inside, inter):
        out = []
        for i in range(len(pts)):
            cur, prv = pts[i], pts[i-1]
            if inside(cur):
                if not inside(prv): out.append(inter(prv, cur))
                out.append(cur)
            elif inside(prv): out.append(inter(prv, cur))
        return out
    def ix(xv):
        return lambda a, b: (xv, a[1] + (b[1]-a[1]) * (xv - a[0]) / (b[0]-a[0]))
    def iy(yv):
        return lambda a, b: (a[0] + (b[0]-a[0]) * (yv - a[1]) / (b[1]-a[1]), yv)
    for ins, it in ((lambda p: p[0] >= x0, ix(x0)), (lambda p: p[0] <= x1, ix(x1)), (lambda p: p[1] >= y0, iy(y0)), (lambda p: p[1] <= y1, iy(y1))):
        if not poly: return poly
        poly = clip_edge(poly, ins, it)
    return poly
ROUTE = {'Czechia': 'cz', 'Germany': 'de', 'Poland': 'pl', 'Ukraine': 'ua', 'Slovakia': 'sk', 'Hungary': 'hu', 'Austria': 'at', 'Italy': 'it', 'France': 'fr'}
out = []
for g in t['objects']['countries']['geometries']:
    name = g['properties']['name']
    polys = g['arcs'] if g['type'] == 'MultiPolygon' else [g['arcs']] if g['type'] == 'Polygon' else []
    d = []
    for poly in polys:
        for ri, r in enumerate(poly):
            pts = [proj(lon, lat) for lon, lat in ring(r)]
            if not any(-60 <= x <= W+60 and -60 <= y <= H+60 for x, y in pts): continue
            pts = clip(pts, -20, -20, W+20, H+20)
            if len(pts) < 3: continue
            if pts[0] == pts[-1]: pts = pts[:-1]
            h = len(pts) // 2
            pts = dp(pts[:h+1], 0.45)[:-1] + dp(pts[h:] + [pts[0]], 0.45)[:-1]
            if len(pts) < 3: continue
            # drop tiny islands
            area = abs(sum(pts[i][0]*pts[i-1][1] - pts[i-1][0]*pts[i][1] for i in range(len(pts)))) / 2
            if area < 4 and name not in ROUTE: continue
            d.append('M' + 'L'.join(f'{x:.1f} {y:.1f}' for x, y in pts) + 'Z')
    if d: out.append({'name': name, 'code': ROUTE.get(name, ''), 'd': ''.join(d)})
# anchors for flags / labels
anch = {'hub': (16.07, 48.86), 'de': (9.6, 51.3), 'pl': (19.4, 52.1), 'ua': (31.0, 49.2), 'sk': (19.6, 48.75), 'hu': (19.3, 47.15), 'at': (14.2, 47.4), 'it': (12.2, 43.0), 'fr': (2.4, 46.7)}
pos = {k: [round(proj(*v)[0] / W * 100, 2), round(proj(*v)[1] / H * 100, 2)] for k, v in anch.items()}
json.dump({'w': W, 'h': round(H, 1), 'countries': out, 'pos': pos}, open('europe.json', 'w'), ensure_ascii=False, separators=(',', ':'))
print(len(out), 'countries', sum(len(c['d']) for c in out) // 1024, 'KB', pos)
