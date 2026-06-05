import json
from html import escape
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
DATA_PATH = ROOT / "tmp" / "area_manager_map.json"
OUTPUT_PATH = ROOT / "docs" / "empresas-areas-encargado.svg"

WIDTH = 1600
PADDING_X = 40
PADDING_Y = 32
HEADER_HEIGHT = 90
ROW_HEIGHT = 38

COLUMNS = [
    ("Empresa", 660),
    ("Área", 560),
    ("Encargado", 300),
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
    companies = json.load(fh)

rows = []

for company in companies:
    company_name = company.get("company_name", "").strip()

    for manager in company.get("managers", []):
        manager_name = manager.get("manager_name", "").strip()

        for area in manager.get("areas", []):
            rows.append({
                "company": company_name,
                "area": area.get("name", "").strip(),
                "manager": manager_name,
            })

rows.sort(key=lambda row: (row["company"], row["area"], row["manager"]))

extra_line_rows = 0
for row in rows:
    if len(wrap_text(row["company"], 45)) > 1:
        extra_line_rows += 1
    if len(wrap_text(row["area"], 38)) > 1:
        extra_line_rows += 1

height = HEADER_HEIGHT + PADDING_Y * 2 + (len(rows) * ROW_HEIGHT) + (extra_line_rows * 16) + 40

svg = [
    f'<svg xmlns="http://www.w3.org/2000/svg" width="{WIDTH}" height="{height}" viewBox="0 0 {WIDTH} {height}">',
    "<style>",
    ".title{font:700 30px Arial, sans-serif; fill:#ffffff;}",
    ".subtitle{font:15px Arial, sans-serif; fill:#E9EDFF;}",
    ".thead{font:700 16px Arial, sans-serif; fill:#1D2A44;}",
    ".cell{font:14px Arial, sans-serif; fill:#172033;}",
    ".muted{font:13px Arial, sans-serif; fill:#5B6475;}",
    "</style>",
    f'<rect width="{WIDTH}" height="{height}" fill="#F5F7FB"/>',
    f'<rect x="{PADDING_X}" y="20" width="{WIDTH - PADDING_X * 2}" height="74" rx="24" fill="#4056F4"/>',
    f'<text x="{PADDING_X + 24}" y="52" class="title">Empresas, Áreas y Encargados</text>',
    f'<text x="{PADDING_X + 24}" y="78" class="subtitle">Referencia para identificar quién preaprueba las OCs por cada área.</text>',
]

table_x = PADDING_X
table_y = HEADER_HEIGHT + 28
table_width = sum(width for _, width in COLUMNS)

svg.append(f'<rect x="{table_x}" y="{table_y}" width="{table_width}" height="{height - table_y - 24}" rx="18" fill="#FFFFFF"/>')
svg.append(f'<rect x="{table_x}" y="{table_y}" width="{table_width}" height="46" rx="18" fill="#E9EDFF"/>')
svg.append(f'<rect x="{table_x}" y="{table_y + 22}" width="{table_width}" height="24" fill="#E9EDFF"/>')

current_x = table_x
for title, width in COLUMNS:
    svg.append(f'<text x="{current_x + 18}" y="{table_y + 30}" class="thead">{escape(title)}</text>')
    current_x += width

current_y = table_y + 58

for index, row in enumerate(rows):
    company_lines = wrap_text(row["company"], 45)
    area_lines = wrap_text(row["area"], 38)
    max_lines = max(len(company_lines), len(area_lines), 1)
    row_height = ROW_HEIGHT + ((max_lines - 1) * 16)

    if index % 2 == 0:
        svg.append(f'<rect x="{table_x}" y="{current_y - 22}" width="{table_width}" height="{row_height}" fill="#FAFBFE"/>')

    svg.append(f'<line x1="{table_x}" y1="{current_y - 22}" x2="{table_x + table_width}" y2="{current_y - 22}" stroke="#E7ECF5"/>')

    company_y = current_y
    for line in company_lines:
        svg.append(f'<text x="{table_x + 18}" y="{company_y}" class="cell">{escape(line)}</text>')
        company_y += 16

    area_x = table_x + COLUMNS[0][1]
    area_y = current_y
    for line in area_lines:
        svg.append(f'<text x="{area_x + 18}" y="{area_y}" class="cell">{escape(line)}</text>')
        area_y += 16

    manager_x = area_x + COLUMNS[1][1]
    svg.append(f'<text x="{manager_x + 18}" y="{current_y}" class="cell">{escape(row["manager"])}</text>')

    current_y += row_height

svg.append(f'<line x1="{table_x}" y1="{current_y - 22}" x2="{table_x + table_width}" y2="{current_y - 22}" stroke="#E7ECF5"/>')
svg.append("</svg>")

OUTPUT_PATH.write_text("\n".join(svg), encoding="utf-8")
print(str(OUTPUT_PATH))
