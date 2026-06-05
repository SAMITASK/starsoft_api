import json
from html import escape
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
DATA_PATH = ROOT / "tmp" / "area_manager_map.json"
OUTPUT_PATH = ROOT / "docs" / "jefes-area-por-empresa.svg"

WIDTH = 1800
PADDING_X = 56
PADDING_Y = 44
HEADER_HEIGHT = 120
COLUMN_GAP = 28
CARD_WIDTH = (WIDTH - (PADDING_X * 2) - COLUMN_GAP) // 2


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


def estimate_card_height(company):
    height = 88
    for manager in company.get("managers", []):
        height += 54
        height += len(manager.get("areas", [])) * 30
        height += 18
    return height


with DATA_PATH.open("r", encoding="utf-8") as fh:
    companies = json.load(fh)


card_heights = [estimate_card_height(company) for company in companies]
left_total = 0
right_total = 0
positions = []

for height in card_heights:
    if left_total <= right_total:
        positions.append(("left", left_total))
        left_total += height + 20
    else:
        positions.append(("right", right_total))
        right_total += height + 20

height = max(left_total, right_total) + HEADER_HEIGHT + PADDING_Y + 40

svg_parts = [
    f'<svg xmlns="http://www.w3.org/2000/svg" width="{WIDTH}" height="{height}" viewBox="0 0 {WIDTH} {height}">',
    "<style>",
    ".title{font:700 34px Arial, sans-serif; fill:#ffffff;}",
    ".subtitle{font:16px Arial, sans-serif; fill:#E9EDFF;}",
    ".company{font:700 22px Arial, sans-serif; fill:#4056F4;}",
    ".manager{font:700 18px Arial, sans-serif; fill:#172033;}",
    ".email{font:13px Arial, sans-serif; fill:#5B6475;}",
    ".chip{font:13px Arial, sans-serif; fill:#172033;}",
    "</style>",
    f'<rect width="{WIDTH}" height="{height}" fill="#F5F7FB"/>',
    f'<rect x="{PADDING_X}" y="24" width="{WIDTH - PADDING_X * 2}" height="86" rx="28" fill="#4056F4"/>',
    f'<text x="{PADDING_X + 28}" y="62" class="title">Jefes de Área por Empresa</text>',
    f'<text x="{PADDING_X + 30}" y="92" class="subtitle">Referencia visual para identificar quién preaprueba las OCs según empresa y área.</text>',
]

for company, (column, offset), card_height in zip(companies, positions, card_heights):
    x = PADDING_X if column == "left" else PADDING_X + CARD_WIDTH + COLUMN_GAP
    y = HEADER_HEIGHT + PADDING_Y + offset

    svg_parts.append(f'<rect x="{x}" y="{y}" width="{CARD_WIDTH}" height="{card_height}" rx="24" fill="#FFFFFF"/>')
    svg_parts.append(f'<rect x="{x}" y="{y}" width="{CARD_WIDTH}" height="56" rx="24" fill="#E9EDFF"/>')
    svg_parts.append(f'<rect x="{x}" y="{y + 28}" width="{CARD_WIDTH}" height="28" fill="#E9EDFF"/>')

    company_title = f'{company["company_code"]} · {company["company_name"]}'
    company_lines = wrap_text(company_title, 52)
    title_y = y + 28
    for line in company_lines:
        svg_parts.append(f'<text x="{x + 18}" y="{title_y}" class="company">{escape(line)}</text>')
        title_y += 24

    section_y = y + 82

    for index, manager in enumerate(company.get("managers", [])):
        svg_parts.append(f'<text x="{x + 18}" y="{section_y}" class="manager">{escape(manager["manager_name"])}</text>')
        section_y += 24

        email = manager.get("email", "").strip()
        if email:
            svg_parts.append(f'<text x="{x + 18}" y="{section_y}" class="email">{escape(email)}</text>')
            section_y += 22
        else:
            section_y += 10

        for area in manager.get("areas", []):
            label = f'{area["name"]} ({area["code"]})' if area.get("code") else area["name"]
            svg_parts.append(
                f'<rect x="{x + 18}" y="{section_y - 16}" width="{CARD_WIDTH - 36}" height="22" rx="11" fill="#EEF2F8" stroke="#D6DEEC"/>'
            )
            svg_parts.append(f'<text x="{x + 28}" y="{section_y}" class="chip">{escape(label)}</text>')
            section_y += 30

        if index < len(company.get("managers", [])) - 1:
            svg_parts.append(
                f'<line x1="{x + 18}" y1="{section_y + 2}" x2="{x + CARD_WIDTH - 18}" y2="{section_y + 2}" stroke="#E5EAF3" />'
            )
            section_y += 18

svg_parts.append("</svg>")
OUTPUT_PATH.parent.mkdir(parents=True, exist_ok=True)
OUTPUT_PATH.write_text("\n".join(svg_parts), encoding="utf-8")
print(str(OUTPUT_PATH))
