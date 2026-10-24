"""Prepare local-only configuration and code-native avatar artwork."""
from pathlib import Path

root = Path(__file__).resolve().parents[1]
if not (root / '.env').exists():
    config = (root / '.env.example').read_text(encoding='utf-8')
    config = config.replace('DB_CONNECTION=mysql', 'DB_CONNECTION=sqlite')
    config = config.replace('DB_DATABASE=di_bitara', 'DB_DATABASE=' + (root/'database/database.sqlite').as_posix())
    (root / '.env').write_text(config, encoding='utf-8')
(root / 'database/database.sqlite').touch(exist_ok=True)
for i, color in enumerate(['#173c70', '#285e80', '#7552a0', '#b75f72'], 1):
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160">
<rect width="160" height="160" rx="80" fill="#e9f3ff"/>
<path d="M29 157v-25q0-27 28-29h46q28 2 28 29v25" fill="{color}"/>
<path d="m64 105 16 29 16-29" fill="white"/><path d="m75 120 5 14 5-14-5-6" fill="#ffcf48"/>
<ellipse cx="80" cy="73" rx="35" ry="39" fill="#f4c49d"/>
<path d="M45 66q-3-33 35-33t35 33l-10-16q-30 15-50 0z" fill="#44312e"/>
<path d="M37 46 45 20q35-19 70 0l8 26Z" fill="{color}"/>
<path d="M36 44q44-13 88 0l-8 11q-36-11-72 0z" fill="#ffcf48"/>
<path d="M49 49q31-7 62 0l-7 9q-24-6-48 0z" fill="#132c50"/>
<circle cx="65" cy="76" r="4" fill="#25324b"/><circle cx="95" cy="76" r="4" fill="#25324b"/>
<path d="M70 92q10 10 20 0" fill="none" stroke="#9d4e43" stroke-width="3" stroke-linecap="round"/>
<path d="m69 29 11 5 11-5-11 12z" fill="#ffcf48"/>
<path d="M40 135h14m52 0h14M40 142h14m52 0h14" stroke="#ffcf48" stroke-width="4"/>
</svg>'''
    (root / f'public/images/pilot-{i}.svg').write_text(svg, encoding='utf-8')
print('Local configuration and avatar artwork ready.')
