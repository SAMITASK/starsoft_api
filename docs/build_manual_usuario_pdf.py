from pathlib import Path

from reportlab.graphics.shapes import Drawing, Line, Rect, String
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import (
    KeepTogether,
    ListFlowable,
    ListItem,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)


ROOT = Path(__file__).resolve().parent
OUTPUT = ROOT / "manual-usuario-starcheck.pdf"

PAGE_WIDTH, PAGE_HEIGHT = A4
PRIMARY = colors.HexColor("#25366D")
SECONDARY = colors.HexColor("#4D63D9")
ACCENT = colors.HexColor("#15A087")
WARN = colors.HexColor("#F4B740")
SOFT_BG = colors.HexColor("#F5F7FC")
TEXT = colors.HexColor("#24324B")
MUTED = colors.HexColor("#5D6B82")
WHITE = colors.white
BORDER = colors.HexColor("#D9E1F2")


def build_styles():
    sample = getSampleStyleSheet()

    return {
        "title": ParagraphStyle(
            "Title",
            parent=sample["Title"],
            fontName="Helvetica-Bold",
            fontSize=24,
            leading=28,
            alignment=TA_LEFT,
            textColor=WHITE,
            spaceAfter=10,
        ),
        "cover_subtitle": ParagraphStyle(
            "CoverSubtitle",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=11,
            leading=16,
            alignment=TA_LEFT,
            textColor=colors.HexColor("#DCE4FF"),
            spaceAfter=14,
        ),
        "section": ParagraphStyle(
            "Section",
            parent=sample["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=16,
            leading=20,
            textColor=PRIMARY,
            spaceBefore=2,
            spaceAfter=10,
        ),
        "subsection": ParagraphStyle(
            "Subsection",
            parent=sample["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=11.5,
            leading=15,
            textColor=SECONDARY,
            spaceBefore=6,
            spaceAfter=5,
        ),
        "body": ParagraphStyle(
            "Body",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=10,
            leading=14,
            textColor=TEXT,
            spaceAfter=6,
        ),
        "small": ParagraphStyle(
            "Small",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=8.7,
            leading=12,
            textColor=MUTED,
            spaceAfter=4,
        ),
        "card_title": ParagraphStyle(
            "CardTitle",
            parent=sample["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=12,
            leading=15,
            textColor=PRIMARY,
            spaceAfter=4,
        ),
        "card_body": ParagraphStyle(
            "CardBody",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=9.2,
            leading=13,
            textColor=TEXT,
        ),
        "hero_stat": ParagraphStyle(
            "HeroStat",
            parent=sample["BodyText"],
            fontName="Helvetica-Bold",
            fontSize=14,
            leading=16,
            textColor=PRIMARY,
            alignment=TA_CENTER,
        ),
        "hero_label": ParagraphStyle(
            "HeroLabel",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=8.5,
            leading=11,
            textColor=MUTED,
            alignment=TA_CENTER,
        ),
        "footer": ParagraphStyle(
            "Footer",
            parent=sample["BodyText"],
            fontName="Helvetica",
            fontSize=8,
            leading=10,
            textColor=colors.HexColor("#6B7280"),
            alignment=TA_CENTER,
        ),
    }


def bullet_list(items, styles, bullet_color=PRIMARY):
    return ListFlowable(
        [
            ListItem(
                Paragraph(
                    f"<font color='{bullet_color}'>{item}</font>",
                    styles["body"],
                )
            )
            for item in items
        ],
        bulletType="bullet",
        leftIndent=14,
        bulletFontName="Helvetica",
        bulletFontSize=8,
    )


def info_box(title, body, styles, background=SOFT_BG, title_color=PRIMARY):
    content = [
        Paragraph(f"<font color='{title_color}'>{title}</font>", styles["card_title"]),
        Paragraph(body, styles["card_body"]),
    ]
    table = Table([[content]], colWidths=[170 * mm])
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), background),
                ("BOX", (0, 0), (-1, -1), 0.8, BORDER),
                ("LEFTPADDING", (0, 0), (-1, -1), 12),
                ("RIGHTPADDING", (0, 0), (-1, -1), 12),
                ("TOPPADDING", (0, 0), (-1, -1), 10),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 10),
            ]
        )
    )
    return table


def two_card_row(left_title, left_body, right_title, right_body, styles):
    left = [
        Paragraph(left_title, styles["card_title"]),
        Paragraph(left_body, styles["card_body"]),
    ]
    right = [
        Paragraph(right_title, styles["card_title"]),
        Paragraph(right_body, styles["card_body"]),
    ]
    table = Table([[left, right]], colWidths=[84 * mm, 84 * mm], hAlign="LEFT")
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), WHITE),
                ("BOX", (0, 0), (0, 0), 0.8, BORDER),
                ("BOX", (1, 0), (1, 0), 0.8, BORDER),
                ("LEFTPADDING", (0, 0), (-1, -1), 12),
                ("RIGHTPADDING", (0, 0), (-1, -1), 12),
                ("TOPPADDING", (0, 0), (-1, -1), 10),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 10),
                ("VALIGN", (0, 0), (-1, -1), "TOP"),
            ]
        )
    )
    return table


def hero_stats(styles):
    cards = [
        [
            Paragraph("3", styles["hero_stat"]),
            Paragraph("roles principales", styles["hero_label"]),
        ],
        [
            Paragraph("4", styles["hero_stat"]),
            Paragraph("estados del flujo", styles["hero_label"]),
        ],
        [
            Paragraph("1", styles["hero_stat"]),
            Paragraph("ruta simple de aprobación", styles["hero_label"]),
        ],
    ]
    table = Table([cards], colWidths=[55 * mm, 55 * mm, 55 * mm])
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, -1), WHITE),
                ("BOX", (0, 0), (-1, -1), 0, WHITE),
                ("LEFTPADDING", (0, 0), (-1, -1), 10),
                ("RIGHTPADDING", (0, 0), (-1, -1), 10),
                ("TOPPADDING", (0, 0), (-1, -1), 10),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 10),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("GRID", (0, 0), (-1, -1), 0.8, BORDER),
            ]
        )
    )
    return table


def role_matrix(styles):
    data = [
        [
            Paragraph("<b>Rol</b>", styles["small"]),
            Paragraph("<b>Ver órdenes</b>", styles["small"]),
            Paragraph("<b>Filtro por área</b>", styles["small"]),
            Paragraph("<b>Dar visto bueno</b>", styles["small"]),
            Paragraph("<b>Aprobación final</b>", styles["small"]),
            Paragraph("<b>Marcar leído</b>", styles["small"]),
        ],
        ["Jefe de Área", "Si", "Si", "Si", "No", "No"],
        ["Gerente", "Si", "No", "No", "Si", "Si"],
        ["Administrador / Sistemas", "Según permiso", "No", "No", "Si", "No"],
    ]

    table = Table(data, colWidths=[33 * mm, 24 * mm, 27 * mm, 28 * mm, 30 * mm, 25 * mm])
    table.setStyle(
        TableStyle(
            [
                ("BACKGROUND", (0, 0), (-1, 0), PRIMARY),
                ("TEXTCOLOR", (0, 0), (-1, 0), WHITE),
                ("GRID", (0, 0), (-1, -1), 0.6, BORDER),
                ("BACKGROUND", (0, 1), (-1, -1), WHITE),
                ("ROWBACKGROUNDS", (0, 1), (-1, -1), [WHITE, colors.HexColor("#F8FAFF")]),
                ("FONTNAME", (0, 1), (-1, -1), "Helvetica"),
                ("FONTSIZE", (0, 1), (-1, -1), 8.8),
                ("LEADING", (0, 1), (-1, -1), 11),
                ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ("ALIGN", (1, 1), (-1, -1), "CENTER"),
                ("LEFTPADDING", (0, 0), (-1, -1), 8),
                ("RIGHTPADDING", (0, 0), (-1, -1), 8),
                ("TOPPADDING", (0, 0), (-1, -1), 8),
                ("BOTTOMPADDING", (0, 0), (-1, -1), 8),
            ]
        )
    )
    return table


def approval_flow():
    drawing = Drawing(520, 145)

    boxes = [
        (12, 65, 95, 40, colors.HexColor("#EDF2FF"), PRIMARY, "Orden", "EMITIDA"),
        (145, 65, 115, 40, colors.HexColor("#EEFAF7"), ACCENT, "Jefe de Área", "Dar visto bueno"),
        (304, 65, 110, 40, colors.HexColor("#FFF7E6"), colors.HexColor("#B7791F"), "Estado portal", "PREAPROBADA"),
        (448, 87, 65, 30, colors.HexColor("#EAF8F4"), ACCENT, "Gerencia", "Aprobar"),
        (448, 40, 65, 30, colors.HexColor("#FFF0F0"), colors.HexColor("#C53030"), "Gerencia", "Rechazar"),
    ]

    for x, y, w, h, bg, stroke, t1, t2 in boxes:
        drawing.add(Rect(x, y, w, h, rx=9, ry=9, fillColor=bg, strokeColor=stroke, strokeWidth=1.2))
        drawing.add(String(x + w / 2, y + 23, t1, fontName="Helvetica-Bold", fontSize=8.5, fillColor=stroke, textAnchor="middle"))
        drawing.add(String(x + w / 2, y + 11, t2, fontName="Helvetica", fontSize=8, fillColor=TEXT, textAnchor="middle"))

    for x1, y1, x2, y2 in [
        (107, 85, 145, 85),
        (260, 85, 304, 85),
        (414, 85, 448, 101),
        (414, 85, 448, 55),
    ]:
        drawing.add(Line(x1, y1, x2, y2, strokeColor=SECONDARY, strokeWidth=1.6))

    for arrow_x, arrow_y in [(145, 85), (304, 85), (448, 101), (448, 55)]:
        drawing.add(Line(arrow_x - 7, arrow_y + 4, arrow_x, arrow_y, strokeColor=SECONDARY, strokeWidth=1.6))
        drawing.add(Line(arrow_x - 7, arrow_y - 4, arrow_x, arrow_y, strokeColor=SECONDARY, strokeWidth=1.6))

    drawing.add(String(258, 125, "Flujo resumido de aprobación", fontName="Helvetica-Bold", fontSize=11, fillColor=PRIMARY, textAnchor="middle"))
    drawing.add(String(258, 17, "Solo gerencia realiza la aprobación final y el rechazo.", fontName="Helvetica", fontSize=8.5, fillColor=MUTED, textAnchor="middle"))

    return drawing


def draw_cover(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(PRIMARY)
    canvas.rect(0, 0, PAGE_WIDTH, PAGE_HEIGHT, stroke=0, fill=1)

    canvas.setFillColor(SECONDARY)
    canvas.rect(0, PAGE_HEIGHT - 70 * mm, PAGE_WIDTH, 70 * mm, stroke=0, fill=1)

    canvas.setFillColor(colors.HexColor("#6D84FF"))
    canvas.circle(PAGE_WIDTH - 28 * mm, PAGE_HEIGHT - 30 * mm, 16 * mm, stroke=0, fill=1)
    canvas.setFillColor(colors.HexColor("#89E3D1"))
    canvas.circle(PAGE_WIDTH - 60 * mm, PAGE_HEIGHT - 52 * mm, 9 * mm, stroke=0, fill=1)

    canvas.setFillColor(colors.HexColor("#C7D2FF"))
    canvas.setFont("Helvetica-Bold", 18)
    canvas.drawString(22 * mm, PAGE_HEIGHT - 32 * mm, "StarCheck")

    canvas.setStrokeColor(colors.HexColor("#DCE4FF"))
    canvas.setLineWidth(2)
    canvas.roundRect(21 * mm, PAGE_HEIGHT - 52 * mm, 16 * mm, 16 * mm, 4 * mm, stroke=1, fill=0)
    canvas.line(25 * mm, PAGE_HEIGHT - 44 * mm, 29 * mm, PAGE_HEIGHT - 40 * mm)
    canvas.line(29 * mm, PAGE_HEIGHT - 40 * mm, 35 * mm, PAGE_HEIGHT - 48 * mm)

    canvas.setFillColor(WHITE)
    canvas.setFont("Helvetica", 8.5)
    canvas.drawRightString(PAGE_WIDTH - 22 * mm, 18 * mm, "Manual visual para usuario final")
    canvas.restoreState()


def draw_inside_pages(canvas, doc):
    canvas.saveState()
    canvas.setFillColor(colors.HexColor("#F8FAFF"))
    canvas.rect(0, 0, PAGE_WIDTH, PAGE_HEIGHT, stroke=0, fill=1)

    canvas.setFillColor(PRIMARY)
    canvas.rect(0, PAGE_HEIGHT - 16 * mm, PAGE_WIDTH, 16 * mm, stroke=0, fill=1)
    canvas.setFillColor(WHITE)
    canvas.setFont("Helvetica-Bold", 9)
    canvas.drawString(20 * mm, PAGE_HEIGHT - 10.5 * mm, "StarCheck · Manual de Usuario")

    canvas.setFillColor(MUTED)
    canvas.setFont("Helvetica", 8)
    canvas.drawRightString(PAGE_WIDTH - 20 * mm, 10 * mm, f"Pagina {doc.page}")
    canvas.restoreState()


def build_story(styles):
    story = []

    story.append(Spacer(1, 74 * mm))
    story.append(Paragraph("Manual de Usuario", styles["title"]))
    story.append(Paragraph("Guia visual para operar StarCheck de forma rapida y ordenada.", styles["cover_subtitle"]))
    story.append(
        info_box(
            "Que encontraras en este documento",
            "Pantallas y acciones clave para revisar ordenes, usar el flujo de aprobacion, comprender los permisos por rol y resolver las dudas mas frecuentes sin entrar en detalles tecnicos.",
            styles,
            background=WHITE,
            title_color=PRIMARY,
        )
    )
    story.append(Spacer(1, 10))
    story.append(hero_stats(styles))
    story.append(Spacer(1, 8))
    story.append(Paragraph("Version visual generada el 14/05/2026", styles["small"]))
    story.append(PageBreak())

    story.append(Paragraph("1. Panorama General", styles["section"]))
    story.append(
        two_card_row(
            "Que es StarCheck",
            "Es una aplicacion para revisar ordenes de compra y ordenes de servicio, realizar preaprobaciones por area, aprobar por gerencia y mantener la configuracion de usuarios.",
            "Modulos principales",
            "Segun el rol del usuario, el sistema puede mostrar Dashboard, Ordenes de Compra, Proveedores, Productos, Reportes y Usuarios.",
            styles,
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        info_box(
            "Ingreso al sistema",
            "Abra la pantalla de acceso, ingrese su correo, ingrese su contrasena y presione <b>Ingresar</b>. Si el usuario esta activo y los datos son correctos, el sistema mostrara el menu segun su perfil.",
            styles,
        )
    )
    story.append(Spacer(1, 12))
    story.append(Paragraph("2. Matriz de Roles", styles["section"]))
    story.append(Paragraph("La siguiente tabla resume lo mas importante de cada perfil para evitar confusiones durante la operacion diaria.", styles["body"]))
    story.append(role_matrix(styles))
    story.append(Spacer(1, 10))
    story.append(
        KeepTogether(
            [
                Paragraph("Reglas rapidas por rol", styles["subsection"]),
                bullet_list(
                    [
                        "Jefe de Area: trabaja por empresa y por areas asignadas.",
                        "Gerente: aprueba o rechaza ordenes preaprobadas y puede marcar ordenes como leidas.",
                        "Administrador / Sistemas: tiene acceso administrativo segun configuracion del sistema.",
                    ],
                    styles,
                ),
            ]
        )
    )
    story.append(PageBreak())

    story.append(Paragraph("3. Flujo de Aprobacion", styles["section"]))
    story.append(Paragraph("Este es el recorrido normal de una orden dentro del portal. La idea es que el usuario pueda identificar en segundos que accion le corresponde hacer.", styles["body"]))
    story.append(approval_flow())
    story.append(Spacer(1, 10))
    story.append(
        two_card_row(
            "Jefe de Area",
            "Cuando la orden esta <b>EMITIDA</b>, puede abrirla y usar <b>Dar Visto Bueno</b>. Esa accion la deja disponible para la aprobacion gerencial.",
            "Gerencia",
            "Cuando la orden ya esta <b>PREAPROBADA</b>, vera los botones <b>Aprobar</b> y <b>Rechazar</b>. Solo en esa etapa existe la aprobacion final.",
            styles,
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        info_box(
            "Lectura de ordenes",
            "Las ordenes pueden marcarse como leidas cuando las revisa gerencia. Los jefes de area pueden abrirlas, pero no cambian el estado de lectura.",
            styles,
            background=colors.HexColor("#EEF7FF"),
            title_color=SECONDARY,
        )
    )
    story.append(PageBreak())

    story.append(Paragraph("4. Uso Diario del Modulo Ordenes de Compra", styles["section"]))
    story.append(
        two_card_row(
            "Filtros disponibles",
            "Puede filtrar por empresa, estado, rango de fechas, area y busqueda por texto. El filtro de area solo aparece para Jefe de Area.",
            "Estados visibles",
            "El portal trabaja con cuatro estados faciles de identificar: EMITIDA, PREAPROBADA, APROBADA y RECHAZADO.",
            styles,
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        KeepTogether(
            [
                Paragraph("Pasos para revisar una orden", styles["subsection"]),
                bullet_list(
                    [
                        "Ingrese a Ordenes de Compra.",
                        "Seleccione empresa, estado y rango de fechas.",
                        "Haga clic sobre la orden para abrir el detalle.",
                        "Revise proveedor, fechas, observacion, productos e importes.",
                        "Ejecute la accion que corresponda segun su rol.",
                    ],
                    styles,
                ),
            ]
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        info_box(
            "Lo que vera en el detalle",
            "Proveedor, fechas, moneda, forma de pago, solicitante, responsable, observacion, tabla de productos, importes y botones de accion segun el estado de la orden.",
            styles,
            background=colors.HexColor("#F4FBF9"),
            title_color=ACCENT,
        )
    )
    story.append(PageBreak())

    story.append(Paragraph("5. Usuarios y Asignaciones", styles["section"]))
    story.append(
        two_card_row(
            "Jefe de Area: permisos por empresa",
            "Cuando el usuario tiene cargo JEFE DE AREA, el sistema permite asignar empresas y seleccionar las areas que puede revisar dentro de cada una.",
            "Gerencia: datos por empresa",
            "En el icono de empresa del usuario gerente se registran dos datos: ID de usuario y correo aprobador. El correo aprobador se usa en la aprobacion final.",
            styles,
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        KeepTogether(
            [
                Paragraph("Pasos para asignar datos por empresa", styles["subsection"]),
                bullet_list(
                    [
                        "Ir al modulo Usuarios.",
                        "Ubicar al usuario en la grilla.",
                        "Hacer clic en el icono de empresa.",
                        "Elegir la empresa.",
                        "Completar los datos requeridos y presionar Vincular.",
                    ],
                    styles,
                ),
            ]
        )
    )
    story.append(Spacer(1, 10))
    story.append(
        info_box(
            "Recordatorio operativo",
            "Si cambia de usuario o vuelve a abrir el formulario, el dialogo debe cargar la informacion del registro correcto. Si no ve datos, cierre y vuelva a abrir el registro para forzar la recarga de la pantalla actualizada.",
            styles,
            background=colors.HexColor("#FFF8E9"),
            title_color=colors.HexColor("#A56A00"),
        )
    )
    story.append(PageBreak())

    story.append(Paragraph("6. Sesion e Inactividad", styles["section"]))
    story.append(
        two_card_row(
            "Continuar sesion",
            "Si aparece el aviso de inactividad y sigue trabajando, use el boton <b>Continuar sesion</b> para renovar la sesion actual.",
            "Desconectar",
            "Si termina sus actividades o no desea continuar, use <b>Desconectar</b>. Si no responde a tiempo, el sistema cerrara la sesion automaticamente.",
            styles,
        )
    )
    story.append(Spacer(1, 10))
    story.append(Paragraph("7. Problemas Frecuentes", styles["section"]))
    story.append(
        KeepTogether(
            [
                Paragraph("Si algo no aparece como espera, revise esto primero:", styles["subsection"]),
                bullet_list(
                    [
                        "No puedo ingresar: valide correo, contrasena y estado activo del usuario.",
                        "No veo una orden: revise empresa, fechas, estado y areas asignadas.",
                        "No aparece Aprobar: confirme que la orden este PREAPROBADA y que el usuario sea gerente.",
                        "No veo mis areas: confirme que el usuario sea JEFE DE AREA y que las areas esten guardadas por empresa.",
                    ],
                    styles,
                ),
            ]
        )
    )
    story.append(Spacer(1, 12))
    story.append(
        info_box(
            "Cierre",
            "Este manual esta pensado para operacion diaria. Si necesita una version tecnica con tablas, endpoints o estructura interna del sistema, puede usar el manual mixto del proyecto.",
            styles,
            background=colors.HexColor("#EEF2FF"),
            title_color=PRIMARY,
        )
    )

    return story


def main():
    styles = build_styles()
    story = build_story(styles)

    doc = SimpleDocTemplate(
        str(OUTPUT),
        pagesize=A4,
        leftMargin=19 * mm,
        rightMargin=19 * mm,
        topMargin=23 * mm,
        bottomMargin=16 * mm,
        title="Manual de Usuario StarCheck",
        author="OpenAI Codex",
    )
    doc.build(story, onFirstPage=draw_cover, onLaterPages=draw_inside_pages)
    print(OUTPUT)


if __name__ == "__main__":
    main()
