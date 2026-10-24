import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

def create_package_diagram():
    scale = 3  # 3x supersampling for ultra high definition
    canvas_w = 780 * scale
    canvas_h = 760 * scale

    img = Image.new("RGBA", (canvas_w, canvas_h), (255, 255, 255, 255))
    draw = ImageDraw.Draw(img)

    # Fonts
    font_path_regular = "C:/Windows/Fonts/segoeui.ttf"
    font_path_bold = "C:/Windows/Fonts/segoeuib.ttf"
    if not os.path.exists(font_path_regular):
        font_path_regular = "C:/Windows/Fonts/arial.ttf"
        font_path_bold = "C:/Windows/Fonts/arialbd.ttf"

    font_pkg = ImageFont.truetype(font_path_bold, 10 * scale)
    font_note = ImageFont.truetype(font_path_regular, 10.5 * scale)

    # Frame and Layer dividers
    margin_x = 20 * scale
    margin_y = 20 * scale
    frame_w = canvas_w - 2 * margin_x
    frame_h = canvas_h - 2 * margin_y

    y_l1 = margin_y
    y_l2 = margin_y + 175 * scale
    y_l3 = margin_y + 340 * scale
    y_l4 = margin_y + 530 * scale
    y_bot = margin_y + frame_h

    # 1. DRAW OUTER FRAME & HORIZONTAL DIVIDERS
    draw.rectangle([margin_x, margin_y, margin_x + frame_w, margin_y + frame_h], outline=(30, 30, 30), width=int(1.6 * scale))
    draw.line([margin_x, y_l2, margin_x + frame_w, y_l2], fill=(30, 30, 30), width=int(1.6 * scale))
    draw.line([margin_x, y_l3, margin_x + frame_w, y_l3], fill=(30, 30, 30), width=int(1.6 * scale))
    draw.line([margin_x, y_l4, margin_x + frame_w, y_l4], fill=(30, 30, 30), width=int(1.6 * scale))

    # 2. LAYER NOTES (Right side)
    notes = [
        {"y": y_l1 + 72 * scale, "lines": ["Capa Específica", "de la Aplicación"]},
        {"y": y_l2 + 65 * scale, "lines": ["Capa General de", "la Aplicación"]},
        {"y": y_l3 + 78 * scale, "lines": ["Capa", "Intermedia"]},
        {"y": y_l4 + 75 * scale, "lines": ["Capa de Software", "del Sistema"]},
    ]

    note_x = margin_x + frame_w - 132 * scale
    note_w = 116 * scale
    note_h = 42 * scale
    fold = 11 * scale

    for nt in notes:
        nx = note_x
        ny = nt["y"]
        pts = [
            (nx, ny),
            (nx + note_w - fold, ny),
            (nx + note_w, ny + fold),
            (nx + note_w, ny + note_h),
            (nx, ny + note_h),
        ]
        draw.polygon(pts, fill=(255, 255, 255), outline=(50, 50, 50), width=int(1.2 * scale))
        draw.polygon([(nx + note_w - fold, ny), (nx + note_w - fold, ny + fold), (nx + note_w, ny + fold)],
                     fill=(245, 245, 245), outline=(50, 50, 50), width=int(1.2 * scale))
        line1 = nt["lines"][0]
        line2 = nt["lines"][1]
        draw.text((nx + 8 * scale, ny + 6 * scale), line1, fill=(20, 20, 20), font=font_note)
        draw.text((nx + 8 * scale, ny + 22 * scale), line2, fill=(20, 20, 20), font=font_note)

    # 3. PACKAGES DEFINITION
    pkgs = {
        # Layer 1 (Specific)
        "p1_citas": {
            "x": 42 * scale,
            "y": y_l1 + 68 * scale,
            "w": 115 * scale,
            "h": 52 * scale,
            "lines": ["P1 GESTION DE", "CITAS MEDICAS"],
        },
        "p2_atencion": {
            "x": 192 * scale,
            "y": y_l1 + 68 * scale,
            "w": 118 * scale,
            "h": 52 * scale,
            "lines": ["P2 CONTROL DE", "ATENCION MEDICA"],
        },
        "p3_horarios": {
            "x": 344 * scale,
            "y": y_l1 + 68 * scale,
            "w": 115 * scale,
            "h": 52 * scale,
            "lines": ["P3 PLANIFICACION", "DE HORARIOS"],
        },
        "p4_reportes": {
            "x": 494 * scale,
            "y": y_l1 + 68 * scale,
            "w": 115 * scale,
            "h": 52 * scale,
            "lines": ["P4 GESTION DE", "REPORTES"],
        },

        # Layer 2 (General)
        "p5_seguridad": {
            "x": 248 * scale,
            "y": y_l2 + 55 * scale,
            "w": 150 * scale,
            "h": 56 * scale,
            "lines": ["P5 GESTION DE", "SEGURIDAD Y", "CATALOGOS"],
        },

        # Layer 3 (Intermediate)
        "browser": {
            "x": 86 * scale,
            "y": y_l3 + 70 * scale,
            "w": 105 * scale,
            "h": 52 * scale,
            "lines": ["NAVEGADOR", "DE INTERNET"],
        },
        "vue_inertia": {
            "x": 160 * scale,
            "y": y_l3 + 20 * scale,
            "w": 105 * scale,
            "h": 52 * scale,
            "lines": ["VUE.JS &", "INERTIA.JS"],
        },
        "laravel_core": {
            "x": 355 * scale,
            "y": y_l3 + 20 * scale,
            "w": 125 * scale,
            "h": 60 * scale,
            "lines": ["LARAVEL", "CORE Y MVC"],
        },
        "eloquent": {
            "x": 482 * scale,
            "y": y_l3 + 95 * scale,
            "w": 95 * scale,
            "h": 46 * scale,
            "lines": ["ELOQUENT", "ORM"],
        },

        # Layer 4 (System Software)
        "proto": {
            "x": 86 * scale,
            "y": y_l4 + 75 * scale,
            "w": 105 * scale,
            "h": 54 * scale,
            "lines": ["PROTOCOLO", "HTTPS", "TCP-IP"],
        },
        "vps_server": {
            "x": 305 * scale,
            "y": y_l4 + 80 * scale,
            "w": 115 * scale,
            "h": 48 * scale,
            "lines": ["SERVIDOR", "VPS LINUX"],
        },
        "pgsql_db": {
            "x": 482 * scale,
            "y": y_l4 + 80 * scale,
            "w": 95 * scale,
            "h": 48 * scale,
            "lines": ["POSTGRESQL", "SGBD"],
        },
    }

    # 4. SHADOW LAYER
    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    sdraw = ImageDraw.Draw(shadow_img)

    for pid, p in pkgs.items():
        x, y, w, h = p["x"], p["y"], p["w"], p["h"]
        tab_w = min(46 * scale, w * 0.44)
        tab_h = 13 * scale
        soff = 4 * scale
        sdraw.rectangle([x + soff, y + soff, x + w + soff, y + h + soff], fill=(0, 0, 0, 55))
        sdraw.rectangle([x + soff, y - tab_h + soff, x + tab_w + soff, y + soff], fill=(0, 0, 0, 55))

    shadow_blurred = shadow_img.filter(ImageFilter.GaussianBlur(radius=2.2 * scale))
    img.paste(shadow_blurred, (0, 0), shadow_blurred)

    # 5. DRAW ARROWS
    def draw_dashed_line(p1, p2, dash_len=6*scale, space_len=5*scale):
        x1, y1 = p1
        x2, y2 = p2
        dist = math.hypot(x2 - x1, y2 - y1)
        if dist == 0:
            return
        dx = (x2 - x1) / dist
        dy = (y2 - y1) / dist
        curr = 0
        draw_flag = True
        while curr < dist:
            step = dash_len if draw_flag else space_len
            next_curr = min(curr + step, dist)
            if draw_flag:
                draw.line([(x1 + dx * curr, y1 + dy * curr), (x1 + dx * next_curr, y1 + dy * next_curr)],
                          fill=(30, 30, 30), width=int(1.3 * scale))
            curr = next_curr
            draw_flag = not draw_flag

    def draw_arrowhead(p1, p2, size=9*scale, angle_deg=26):
        x1, y1 = p1
        x2, y2 = p2
        angle = math.atan2(y2 - y1, x2 - x1)
        rad = math.radians(angle_deg)
        left_angle = angle + math.pi - rad
        right_angle = angle + math.pi + rad
        ax1 = x2 + size * math.cos(left_angle)
        ay1 = y2 + size * math.sin(left_angle)
        ax2 = x2 + size * math.cos(right_angle)
        ay2 = y2 + size * math.sin(right_angle)
        draw.line([(x2, y2), (ax1, ay1)], fill=(30, 30, 30), width=int(1.3 * scale))
        draw.line([(x2, y2), (ax2, ay2)], fill=(30, 30, 30), width=int(1.3 * scale))

    def draw_segmented_arrow(points):
        for i in range(len(points) - 1):
            draw_dashed_line(points[i], points[i+1])
        draw_arrowhead(points[-2], points[-1])

    # CONNECTORS
    # P1 -> P2
    draw_dashed_line((pkgs["p1_citas"]["x"] + pkgs["p1_citas"]["w"], pkgs["p1_citas"]["y"] + 26 * scale),
                     (pkgs["p2_atencion"]["x"], pkgs["p2_atencion"]["y"] + 26 * scale))
    draw_arrowhead((pkgs["p1_citas"]["x"] + pkgs["p1_citas"]["w"], pkgs["p1_citas"]["y"] + 26 * scale),
                   (pkgs["p2_atencion"]["x"], pkgs["p2_atencion"]["y"] + 26 * scale))

    # P2 -> P3
    draw_dashed_line((pkgs["p2_atencion"]["x"] + pkgs["p2_atencion"]["w"], pkgs["p2_atencion"]["y"] + 26 * scale),
                     (pkgs["p3_horarios"]["x"], pkgs["p3_horarios"]["y"] + 26 * scale))
    draw_arrowhead((pkgs["p2_atencion"]["x"] + pkgs["p2_atencion"]["w"], pkgs["p2_atencion"]["y"] + 26 * scale),
                   (pkgs["p3_horarios"]["x"], pkgs["p3_horarios"]["y"] + 26 * scale))

    # P4 -> P3 (pointing left)
    draw_dashed_line((pkgs["p4_reportes"]["x"], pkgs["p4_reportes"]["y"] + 26 * scale),
                     (pkgs["p3_horarios"]["x"] + pkgs["p3_horarios"]["w"], pkgs["p3_horarios"]["y"] + 26 * scale))
    draw_arrowhead((pkgs["p4_reportes"]["x"], pkgs["p4_reportes"]["y"] + 26 * scale),
                   (pkgs["p3_horarios"]["x"] + pkgs["p3_horarios"]["w"], pkgs["p3_horarios"]["y"] + 26 * scale))

    # Top Arch 1: P1 arching over to P4 (P1 up, right, down to P4 tab)
    arch1_pts = [
        (pkgs["p1_citas"]["x"] + 65 * scale, pkgs["p1_citas"]["y"] - 13 * scale),
        (pkgs["p1_citas"]["x"] + 65 * scale, y_l1 + 18 * scale),
        (pkgs["p4_reportes"]["x"] + 30 * scale, y_l1 + 18 * scale),
        (pkgs["p4_reportes"]["x"] + 30 * scale, pkgs["p4_reportes"]["y"] - 13 * scale),
    ]
    draw_segmented_arrow(arch1_pts)

    # Top Arch 2: P4 arching over to P2 (P4 up, left, down to P2 tab)
    arch2_pts = [
        (pkgs["p4_reportes"]["x"] + 70 * scale, pkgs["p4_reportes"]["y"] - 13 * scale),
        (pkgs["p4_reportes"]["x"] + 70 * scale, y_l1 + 32 * scale),
        (pkgs["p2_atencion"]["x"] + 55 * scale, y_l1 + 32 * scale),
        (pkgs["p2_atencion"]["x"] + 55 * scale, pkgs["p2_atencion"]["y"] - 13 * scale),
    ]
    draw_segmented_arrow(arch2_pts)

    # Layer 1 downward arrows to Layer 2 (P5 Seguridad)
    p1_b = (pkgs["p1_citas"]["x"] + pkgs["p1_citas"]["w"] * 0.85, pkgs["p1_citas"]["y"] + pkgs["p1_citas"]["h"])
    p5_l1 = (pkgs["p5_seguridad"]["x"] + 20 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)
    draw_dashed_line(p1_b, p5_l1)
    draw_arrowhead(p1_b, p5_l1)

    p2_b = (pkgs["p2_atencion"]["x"] + pkgs["p2_atencion"]["w"] * 0.6, pkgs["p2_atencion"]["y"] + pkgs["p2_atencion"]["h"])
    p5_l2 = (pkgs["p5_seguridad"]["x"] + 52 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)
    draw_dashed_line(p2_b, p5_l2)
    draw_arrowhead(p2_b, p5_l2)

    p3_b = (pkgs["p3_horarios"]["x"] + pkgs["p3_horarios"]["w"] * 0.4, pkgs["p3_horarios"]["y"] + pkgs["p3_horarios"]["h"])
    p5_l3 = (pkgs["p5_seguridad"]["x"] + 100 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)
    draw_dashed_line(p3_b, p5_l3)
    draw_arrowhead(p3_b, p5_l3)

    p4_b = (pkgs["p4_reportes"]["x"] + pkgs["p4_reportes"]["w"] * 0.2, pkgs["p4_reportes"]["y"] + pkgs["p4_reportes"]["h"])
    p5_l4 = (pkgs["p5_seguridad"]["x"] + 130 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)
    draw_dashed_line(p4_b, p5_l4)
    draw_arrowhead(p4_b, p5_l4)

    # From Layer 2 (P5) to Layer 3
    p5_bl = (pkgs["p5_seguridad"]["x"] + 35 * scale, pkgs["p5_seguridad"]["y"] + pkgs["p5_seguridad"]["h"])
    vue_t = (pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"] * 0.7, pkgs["vue_inertia"]["y"] - 13 * scale)
    draw_dashed_line(p5_bl, vue_t)
    draw_arrowhead(p5_bl, vue_t)

    p5_br = (pkgs["p5_seguridad"]["x"] + 115 * scale, pkgs["p5_seguridad"]["y"] + pkgs["p5_seguridad"]["h"])
    core_t = (pkgs["laravel_core"]["x"] + 35 * scale, pkgs["laravel_core"]["y"] - 13 * scale)
    draw_dashed_line(p5_br, core_t)
    draw_arrowhead(p5_br, core_t)

    # Within Layer 3: Vue & Inertia -> Laravel Core
    vue_r = (pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"], pkgs["vue_inertia"]["y"] + 25 * scale)
    core_l = (pkgs["laravel_core"]["x"], pkgs["laravel_core"]["y"] + 25 * scale)
    draw_dashed_line(vue_r, core_l)
    draw_arrowhead(vue_r, core_l)

    # Laravel Core -> Eloquent ORM
    core_br = (pkgs["laravel_core"]["x"] + pkgs["laravel_core"]["w"] * 0.9, pkgs["laravel_core"]["y"] + pkgs["laravel_core"]["h"])
    elo_t = (pkgs["eloquent"]["x"] + 20 * scale, pkgs["eloquent"]["y"] - 13 * scale)
    draw_dashed_line(core_br, elo_t)
    draw_arrowhead(core_br, elo_t)

    # Layer 3 to Layer 4
    brow_b = (pkgs["browser"]["x"] + pkgs["browser"]["w"] * 0.5, pkgs["browser"]["y"] + pkgs["browser"]["h"])
    proto_t = (pkgs["proto"]["x"] + pkgs["proto"]["w"] * 0.5, pkgs["proto"]["y"] - 13 * scale)
    draw_dashed_line(brow_b, proto_t)
    draw_arrowhead(brow_b, proto_t)

    vue_b = (pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"] * 0.6, pkgs["vue_inertia"]["y"] + pkgs["vue_inertia"]["h"])
    vps_tl = (pkgs["vps_server"]["x"] + 25 * scale, pkgs["vps_server"]["y"] - 13 * scale)
    draw_dashed_line(vue_b, vps_tl)
    draw_arrowhead(vue_b, vps_tl)

    core_b = (pkgs["laravel_core"]["x"] + pkgs["laravel_core"]["w"] * 0.45, pkgs["laravel_core"]["y"] + pkgs["laravel_core"]["h"])
    vps_tr = (pkgs["vps_server"]["x"] + 65 * scale, pkgs["vps_server"]["y"] - 13 * scale)
    draw_dashed_line(core_b, vps_tr)
    draw_arrowhead(core_b, vps_tr)

    elo_b = (pkgs["eloquent"]["x"] + pkgs["eloquent"]["w"] * 0.5, pkgs["eloquent"]["y"] + pkgs["eloquent"]["h"])
    pg_t = (pkgs["pgsql_db"]["x"] + pkgs["pgsql_db"]["w"] * 0.5, pkgs["pgsql_db"]["y"] - 13 * scale)
    draw_dashed_line(elo_b, pg_t)
    draw_arrowhead(elo_b, pg_t)

    # Within Layer 4: VPS Server -> Protocolo HTTPS TCP-IP
    vps_l = (pkgs["vps_server"]["x"], pkgs["vps_server"]["y"] + 24 * scale)
    proto_r = (pkgs["proto"]["x"] + pkgs["proto"]["w"], pkgs["proto"]["y"] + 24 * scale)
    draw_dashed_line(vps_l, proto_r)
    draw_arrowhead(vps_l, proto_r)

    # 6. DRAW ALL PACKAGES
    for pid, p in pkgs.items():
        x, y, w, h = p["x"], p["y"], p["w"], p["h"]
        tab_w = min(46 * scale, w * 0.44)
        tab_h = 13 * scale

        draw.rectangle([x, y - tab_h, x + tab_w, y], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.5 * scale))
        draw.rectangle([x, y, x + w, y + h], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.5 * scale))

        lines = p["lines"]
        total_th = len(lines) * 13.5 * scale
        start_y = y + (h - total_th) / 2 + 1 * scale
        for i, ln in enumerate(lines):
            bbox = draw.textbbox((0, 0), ln, font=font_pkg)
            bw = bbox[2] - bbox[0]
            draw.text((x + (w - bw) / 2, start_y + i * 13.5 * scale), ln, fill=(15, 15, 15), font=font_pkg)

    target_w = canvas_w // scale
    target_h = canvas_h // scale
    final_img = img.resize((target_w, target_h), Image.Resampling.LANCZOS)

    png_path_docs = "docs/diagramas/diagrama_paquetes_capas.png"
    png_path_public = "public/images/diagrama_paquetes_capas.png"
    artifact_path = r"C:\Users\Cerezo Alex\.gemini\antigravity\brain\79c3592a-8363-4fa4-a132-af215ad350b3\diagrama_paquetes_capas.png"

    final_img.save(png_path_docs, "PNG")
    final_img.save(png_path_public, "PNG")
    final_img.save(artifact_path, "PNG")
    print("PNG generated successfully.")

    # Also generate pure SVG
    generate_svg(pkgs, notes, margin_x, margin_y, frame_w, frame_h, y_l2, y_l3, y_l4, scale, canvas_w, canvas_h)

def generate_svg(pkgs, notes, margin_x, margin_y, frame_w, frame_h, y_l2, y_l3, y_l4, scale, canvas_w, canvas_h):
    sw = canvas_w / scale
    sh = canvas_h / scale

    svg = [
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {sw:.1f} {sh:.1f}" width="{sw:.1f}" height="{sh:.1f}" style="background-color: #ffffff; font-family: Segoe UI, Arial, sans-serif;">',
        '  <defs>',
        '    <filter id="pkg-shadow" x="-5%" y="-5%" width="120%" height="120%">',
        '      <feDropShadow dx="2.5" dy="3" stdDeviation="2" flood-color="#000000" flood-opacity="0.15" />',
        '    </filter>',
        '    <marker id="arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">',
        '      <path d="M 0 1.5 L 8 5 L 0 8.5" fill="none" stroke="#1e1e1e" stroke-width="1.3" />',
        '    </marker>',
        '  </defs>',
    ]

    # Frame & Horizontal Dividers
    mx, my, fw, fh = margin_x / scale, margin_y / scale, frame_w / scale, frame_h / scale
    yl2, yl3, yl4 = y_l2 / scale, y_l3 / scale, y_l4 / scale

    svg.append(f'  <rect x="{mx:.1f}" y="{my:.1f}" width="{fw:.1f}" height="{fh:.1f}" fill="none" stroke="#1e1e1e" stroke-width="1.6" />')
    svg.append(f'  <line x1="{mx:.1f}" y1="{yl2:.1f}" x2="{mx+fw:.1f}" y2="{yl2:.1f}" stroke="#1e1e1e" stroke-width="1.6" />')
    svg.append(f'  <line x1="{mx:.1f}" y1="{yl3:.1f}" x2="{mx+fw:.1f}" y2="{yl3:.1f}" stroke="#1e1e1e" stroke-width="1.6" />')
    svg.append(f'  <line x1="{mx:.1f}" y1="{yl4:.1f}" x2="{mx+fw:.1f}" y2="{yl4:.1f}" stroke="#1e1e1e" stroke-width="1.6" />')

    # Layer Notes
    note_w = 116
    note_h = 42
    fold = 11
    nx = mx + fw - 132
    for nt in notes:
        ny = nt["y"] / scale
        pts = f"{nx:.1f},{ny:.1f} {nx+note_w-fold:.1f},{ny:.1f} {nx+note_w:.1f},{ny+fold:.1f} {nx+note_w:.1f},{ny+note_h:.1f} {nx:.1f},{ny+note_h:.1f}"
        flap = f"{nx+note_w-fold:.1f},{ny:.1f} {nx+note_w-fold:.1f},{ny+fold:.1f} {nx+note_w:.1f},{ny+fold:.1f}"
        svg.append(f'  <polygon points="{pts}" fill="#ffffff" stroke="#333333" stroke-width="1.2" />')
        svg.append(f'  <polygon points="{flap}" fill="#f5f5f5" stroke="#333333" stroke-width="1.2" />')
        svg.append(f'  <text x="{nx+8:.1f}" y="{ny+16:.1f}" font-size="10.5" fill="#1e1e1e">{nt["lines"][0]}</text>')
        svg.append(f'  <text x="{nx+8:.1f}" y="{ny+32:.1f}" font-size="10.5" fill="#1e1e1e">{nt["lines"][1]}</text>')

    # Helper for svg lines
    def s_line(p1, p2):
        x1, y1 = p1[0] / scale, p1[1] / scale
        x2, y2 = p2[0] / scale, p2[1] / scale
        return f'<line x1="{x1:.1f}" y1="{y1:.1f}" x2="{x2:.1f}" y2="{y2:.1f}" stroke="#1e1e1e" stroke-width="1.3" stroke-dasharray="5,4" marker-end="url(#arrow)" />'

    def s_poly(pts):
        str_pts = " ".join([f"{p[0]/scale:.1f},{p[1]/scale:.1f}" for p in pts])
        return f'<polyline points="{str_pts}" fill="none" stroke="#1e1e1e" stroke-width="1.3" stroke-dasharray="5,4" marker-end="url(#arrow)" />'

    # Connector lines
    svg.append(s_line((pkgs["p1_citas"]["x"] + pkgs["p1_citas"]["w"], pkgs["p1_citas"]["y"] + 26 * scale),
                      (pkgs["p2_atencion"]["x"], pkgs["p2_atencion"]["y"] + 26 * scale)))
    svg.append(s_line((pkgs["p2_atencion"]["x"] + pkgs["p2_atencion"]["w"], pkgs["p2_atencion"]["y"] + 26 * scale),
                      (pkgs["p3_horarios"]["x"], pkgs["p3_horarios"]["y"] + 26 * scale)))
    svg.append(s_line((pkgs["p4_reportes"]["x"], pkgs["p4_reportes"]["y"] + 26 * scale),
                      (pkgs["p3_horarios"]["x"] + pkgs["p3_horarios"]["w"], pkgs["p3_horarios"]["y"] + 26 * scale)))

    # Arch 1 & 2
    svg.append(s_poly([
        (pkgs["p1_citas"]["x"] + 65 * scale, pkgs["p1_citas"]["y"] - 13 * scale),
        (pkgs["p1_citas"]["x"] + 65 * scale, margin_y + 18 * scale),
        (pkgs["p4_reportes"]["x"] + 30 * scale, margin_y + 18 * scale),
        (pkgs["p4_reportes"]["x"] + 30 * scale, pkgs["p4_reportes"]["y"] - 13 * scale),
    ]))
    svg.append(s_poly([
        (pkgs["p4_reportes"]["x"] + 70 * scale, pkgs["p4_reportes"]["y"] - 13 * scale),
        (pkgs["p4_reportes"]["x"] + 70 * scale, margin_y + 32 * scale),
        (pkgs["p2_atencion"]["x"] + 55 * scale, margin_y + 32 * scale),
        (pkgs["p2_atencion"]["x"] + 55 * scale, pkgs["p2_atencion"]["y"] - 13 * scale),
    ]))

    # L1 to L2
    svg.append(s_line((pkgs["p1_citas"]["x"] + pkgs["p1_citas"]["w"] * 0.85, pkgs["p1_citas"]["y"] + pkgs["p1_citas"]["h"]),
                      (pkgs["p5_seguridad"]["x"] + 20 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["p2_atencion"]["x"] + pkgs["p2_atencion"]["w"] * 0.6, pkgs["p2_atencion"]["y"] + pkgs["p2_atencion"]["h"]),
                      (pkgs["p5_seguridad"]["x"] + 52 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["p3_horarios"]["x"] + pkgs["p3_horarios"]["w"] * 0.4, pkgs["p3_horarios"]["y"] + pkgs["p3_horarios"]["h"]),
                      (pkgs["p5_seguridad"]["x"] + 100 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["p4_reportes"]["x"] + pkgs["p4_reportes"]["w"] * 0.2, pkgs["p4_reportes"]["y"] + pkgs["p4_reportes"]["h"]),
                      (pkgs["p5_seguridad"]["x"] + 130 * scale, pkgs["p5_seguridad"]["y"] - 13 * scale)))

    # L2 to L3
    svg.append(s_line((pkgs["p5_seguridad"]["x"] + 35 * scale, pkgs["p5_seguridad"]["y"] + pkgs["p5_seguridad"]["h"]),
                      (pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"] * 0.7, pkgs["vue_inertia"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["p5_seguridad"]["x"] + 115 * scale, pkgs["p5_seguridad"]["y"] + pkgs["p5_seguridad"]["h"]),
                      (pkgs["laravel_core"]["x"] + 35 * scale, pkgs["laravel_core"]["y"] - 13 * scale)))

    # In L3
    svg.append(s_line((pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"], pkgs["vue_inertia"]["y"] + 25 * scale),
                      (pkgs["laravel_core"]["x"], pkgs["laravel_core"]["y"] + 25 * scale)))
    svg.append(s_line((pkgs["laravel_core"]["x"] + pkgs["laravel_core"]["w"] * 0.9, pkgs["laravel_core"]["y"] + pkgs["laravel_core"]["h"]),
                      (pkgs["eloquent"]["x"] + 20 * scale, pkgs["eloquent"]["y"] - 13 * scale)))

    # L3 to L4
    svg.append(s_line((pkgs["browser"]["x"] + pkgs["browser"]["w"] * 0.5, pkgs["browser"]["y"] + pkgs["browser"]["h"]),
                      (pkgs["proto"]["x"] + pkgs["proto"]["w"] * 0.5, pkgs["proto"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["vue_inertia"]["x"] + pkgs["vue_inertia"]["w"] * 0.6, pkgs["vue_inertia"]["y"] + pkgs["vue_inertia"]["h"]),
                      (pkgs["vps_server"]["x"] + 25 * scale, pkgs["vps_server"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["laravel_core"]["x"] + pkgs["laravel_core"]["w"] * 0.45, pkgs["laravel_core"]["y"] + pkgs["laravel_core"]["h"]),
                      (pkgs["vps_server"]["x"] + 65 * scale, pkgs["vps_server"]["y"] - 13 * scale)))
    svg.append(s_line((pkgs["eloquent"]["x"] + pkgs["eloquent"]["w"] * 0.5, pkgs["eloquent"]["y"] + pkgs["eloquent"]["h"]),
                      (pkgs["pgsql_db"]["x"] + pkgs["pgsql_db"]["w"] * 0.5, pkgs["pgsql_db"]["y"] - 13 * scale)))

    # In L4
    svg.append(s_line((pkgs["vps_server"]["x"], pkgs["vps_server"]["y"] + 24 * scale),
                      (pkgs["proto"]["x"] + pkgs["proto"]["w"], pkgs["proto"]["y"] + 24 * scale)))

    # Draw Packages
    for pid, p in pkgs.items():
        x = p["x"] / scale
        y = p["y"] / scale
        w = p["w"] / scale
        h = p["h"] / scale
        tab_w = min(46, w * 0.44)
        tab_h = 13

        svg.append(f'  <g id="{pid}" filter="url(#pkg-shadow)">')
        svg.append(f'    <rect x="{x:.1f}" y="{y-tab_h:.1f}" width="{tab_w:.1f}" height="{tab_h:.1f}" fill="#ffffff" stroke="#1e1e1e" stroke-width="1.5" />')
        svg.append(f'    <rect x="{x:.1f}" y="{y:.1f}" width="{w:.1f}" height="{h:.1f}" fill="#ffffff" stroke="#1e1e1e" stroke-width="1.5" />')

        lines = p["lines"]
        total_th = len(lines) * 13.5
        start_y = y + (h - total_th) / 2 + 11.5
        for i, ln in enumerate(lines):
            svg.append(f'    <text x="{x+w/2:.1f}" y="{start_y + i*13.5:.1f}" font-size="10" font-weight="bold" fill="#111111" text-anchor="middle">{ln}</text>')
        svg.append('  </g>')

    svg.append('</svg>')

    svg_content = "\n".join(svg)
    svg_path_docs = "docs/diagramas/diagrama_paquetes_capas.svg"
    svg_path_public = "public/images/diagrama_paquetes_capas.svg"
    with open(svg_path_docs, "w", encoding="utf-8") as f:
        f.write(svg_content)
    with open(svg_path_public, "w", encoding="utf-8") as f:
        f.write(svg_content)
    print("SVG generated successfully.")

create_package_diagram()
