import json
from html import escape
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
DATA_PATH = ROOT / "tmp" / "company_area_assignment_table.json"
OUTPUT_PATH = ROOT / "docs" / "empresas-areas-encargado-completo.svg"

WIDTH = 1680
PADDING_X = 36
HEADER_HEIGHT = 88
TABLE_TOP = 126
ROW_BASE_HEIGHT = 34

COLUMNS = [
    ("Empresa", 640, 42),
    ("Área", 560, 34),
    ("Encargado", 360, 24),
]


def wrap_text(text: str, max_chars: int):
    words = text.split()
    if not words:
        return [""]

    lines = []
    current = words[0]

    for word in words[1:]:
        attempt = f"{current} {word}"
        if len(attempt) <= max_chars:
            current = attempt
        else:
            lines.append(current)
            current = word

    lines.append(current)
    return lines


with DATA_PATH.open("r", encoding="utf-8") as fh:
    rows = json.load(fh)

rows.sort(key=lambda item: (item["company_code"], item["area_name"], item["manager_name"]))

line_overflow = 0
for row in rows:
    line_overflow += max(
        len(wrap_text(row["company_name"], COLUMNS[0][2])),
        len(wrap_text(row["area_name"], COLUMNS[1][2])),
        len(wrap_text(row["manager_name"] or "-", COLUMNS[2][2])),
    ) - 1

HEIGHT = TABLE_TOP + 54 + len(rows) * ROW_BASE_HEIGHT + max(line_overflow, 0) * 14 + 36

svg = [
    f'<svg xmlns="http://www.w3.org/2000/svg" width="{WIDTH}" height="{HEIGHT}" viewBox="0 0 {WIDTH} {HEIGHT}">',
    "<style>",
    ".title{font:700 28px Arial, sans-serif; fill:#ffffff;}",
    ".subtitle{font:15px Arial, sans-serif; fill:#E9EDFF;}",
    ".thead{font:700 15px Arial, sans-serif; fill:#1B2741;}",
    ".cell{font:14px Arial, sans-serif; fill:#172033;}",
    ".muted{font:14px Arial, sans-serif; fill:#687385;}",
    "</style>",
    f'<rect width="{WIDTH}" height="{HEIGHT}" fill="#F5F7FB"/>',
    f'<rect x="{PADDING_X}" y="20" width="{WIDTH - (PADDING_X * 2)}" height="72" rx="24" fill="#4056F4"/>',
    f'<text x="{PADDING_X + 22}" y="50" class="title">Empresas, Áreas y Encargado Responsable</text>',
    f'<text x="{PADDING_X + 22}" y="76" class="subtitle">Listado completo de áreas obtenido desde las conexiones SQL. Si no hay jefe asignado, la columna queda vacía.</text>',
]

table_x = PADDING_X
table_y = TABLE_TOP
table_width = sum(width for _, width, _ in COLUMNS)

svg.extend([
    f'<rect x="{table_x}" y="{table_y}" width="{table_width}" height="{HEIGHT - table_y - 24}" rx="18" fill="#FFFFFF"/>',
    f'<rect x="{table_x}" y="{table_y}" width="{table_width}" height="44" rx="18" fill="#E9EDFF"/>',
    f'<rect x="{table_x}" y="{table_y + 20}" width="{table_width}" height="24" fill="#E9EDFF"/>',
])

current_x = table_x
for title, width, _ in COLUMNS:
    svg.append(f'<text x="{current_x + 16}" y="{table_y + 28}" class="thead">{escape(title)}</text>')
    current_x += width

current_y = table_y + 62

for index, row in enumerate(rows):
    company_lines = wrap_text(row["company_name"], COLUMNS[0][2])
    area_label = f'{row["area_name"]} ({row["area_code"]})' if row["area_code"] else row["area_name"]
    area_lines = wrap_text(area_label, COLUMNS[1][2])
    manager_lines = wrap_text("", COLUMNS[2][2])
    max_lines = max(len(company_lines), len(area_lines), len(manager_lines))
    row_height = ROW_BASE_HEIGHT + (max_lines - 1) * 14

    if index % 2 == 0:
        svg.append(f'<rect x="{table_x}" y="{current_y - 20}" width="{table_width}" height="{row_height}" fill="#FAFBFE"/>')

    svg.append(f'<line x1="{table_x}" y1="{current_y - 20}" x2="{table_x + table_width}" y2="{current_y - 20}" stroke="#E6EBF4"/>')

    company_y = current_y
    for line in company_lines:
        svg.append(f'<text x="{table_x + 16}" y="{company_y}" class="cell">{escape(line)}</text>')
        company_y += 14

    area_x = table_x + COLUMNS[0][1]
    area_y = current_y
    for line in area_lines:
        svg.append(f'<text x="{area_x + 16}" y="{area_y}" class="cell">{escape(line)}</text>')
        area_y += 14

    manager_x = area_x + COLUMNS[1][1]
    manager_y = current_y
    manager_class = "cell"
    for line in manager_lines:
        svg.append(f'<text x="{manager_x + 16}" y="{manager_y}" class="{manager_class}">{escape(line)}</text>')
        manager_y += 14

    current_y += row_height

svg.append(f'<line x1="{table_x}" y1="{current_y - 20}" x2="{table_x + table_width}" y2="{current_y - 20}" stroke="#E6EBF4"/>')
svg.append("</svg>")

OUTPUT_PATH.write_text("\n".join(svg), encoding="utf-8")
print(str(OUTPUT_PATH))
