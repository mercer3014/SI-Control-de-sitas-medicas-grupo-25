import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

def create_deployment_diagram():
    scale = 3  # 3x supersampling for ultra crispness
    canvas_w = 980 * scale
    canvas_h = 440 * scale

    # High-res RGBA image
    img = Image.new("RGBA", (canvas_w, canvas_h), (255, 255, 255, 255))
    draw = ImageDraw.Draw(img)

    # Fonts
    font_path_regular = "C:/Windows/Fonts/segoeui.ttf"
    font_path_bold = "C:/Windows/Fonts/segoeuib.ttf"
    if not os.path.exists(font_path_regular):
        font_path_regular = "C:/Windows/Fonts/arial.ttf"
        font_path_bold = "C:/Windows/Fonts/arialbd.ttf"

    font_stereo = ImageFont.truetype(font_path_regular, 13 * scale)
    font_name = ImageFont.truetype(font_path_bold, 15 * scale)
    font_label = ImageFont.truetype(font_path_bold, 13 * scale)

    # 3D Node box parameters (isometric/oblique angle matching classmate's diagram)
    dx = 13 * scale
    dy = -10 * scale
    line_w = int(1.8 * scale)

    # Layout Coordinates
    y_top = 35 * scale
    y_mid = 175 * scale
    y_bot = 320 * scale

    # Client X
    x_client = 45 * scale
    
    # Server X (Centered)
    w_srv_local = 280 * scale
    w_srv_vps = 260 * scale
    w_resend = 200 * scale

    x_srv_local = 410 * scale
    x_srv_vps = 420 * scale
    x_resend = 450 * scale  # Center-aligned with servers

    # DB X
    x_db = 750 * scale

    h_box = 56 * scale

    nodes = {
        "desktop": {
            "x": x_client,
            "y": 100 * scale,
            "w": 205 * scale,
            "h": h_box,
            "stereo": "«Cliente»",
            "name": "Dispositivo Desktop",
        },
        "mobile": {
            "x": x_client + 5 * scale,
            "y": 320 * scale,
            "w": 185 * scale,
            "h": h_box,
            "stereo": "«Cliente»",
            "name": "Dispositivo Móvil",
        },
        "server_local": {
            "x": x_srv_local,
            "y": y_top,
            "w": w_srv_local,
            "h": h_box,
            "stereo": "«Servidor»",
            "name": "Servidor local Laravel & PHP",
        },
        "db_local": {
            "x": x_db,
            "y": y_top,
            "w": 180 * scale,
            "h": h_box,
            "stereo": "«Base de datos»",
            "name": "PostgreSQL Local",
        },
        "resend": {
            "x": x_resend,
            "y": y_mid,
            "w": w_resend,
            "h": h_box,
            "stereo": "«Servidor externo»",
            "name": "Resend API",
        },
        "server_vps": {
            "x": x_srv_vps,
            "y": y_bot,
            "w": w_srv_vps,
            "h": h_box,
            "stereo": "«Servidor»",
            "name": "Servidor VPS Laravel & PHP",
        },
        "db_vps": {
            "x": x_db,
            "y": y_bot,
            "w": 200 * scale,
            "h": h_box,
            "stereo": "«Base de datos»",
            "name": "Servidor VPS PostgreSQL",
        },
    }

    # Shadow layer
    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    sdraw = ImageDraw.Draw(shadow_img)

    for nid, n in nodes.items():
        x, y, w, h = n["x"], n["y"], n["w"], n["h"]
        soff_x = 4 * scale
        soff_y = 5 * scale
        shadow_pts = [
            (x + soff_x, y + h + soff_y),
            (x + w + soff_x, y + h + soff_y),
            (x + w + dx + soff_x, y + h + dy + soff_y),
            (x + w + dx + soff_x, y + dy + soff_y + 8 * scale),
            (x + w + dx, y + dy + 8 * scale),
            (x + w, y + h),
        ]
        sdraw.polygon(shadow_pts, fill=(0, 0, 0, 65))

    shadow_blurred = shadow_img.filter(ImageFilter.GaussianBlur(radius=2.2 * scale))
    img.paste(shadow_blurred, (0, 0), shadow_blurred)

    conns = []

    # 1. Desktop -> Local Server
    p_dt_r = (nodes["desktop"]["x"] + nodes["desktop"]["w"] + dx, nodes["desktop"]["y"] + 20 * scale)
    p_sl_l = (nodes["server_local"]["x"], nodes["server_local"]["y"] + nodes["server_local"]["h"] * 0.70)
    conns.append((p_dt_r, p_sl_l, "TCP / IP", 0.40, (0, -13 * scale)))

    # 2. Desktop -> VPS Server
    p_dt_br = (nodes["desktop"]["x"] + nodes["desktop"]["w"] + dx * 0.4, nodes["desktop"]["y"] + nodes["desktop"]["h"] + dy * 0.4)
    p_sv_tl = (nodes["server_vps"]["x"], nodes["server_vps"]["y"] + 15 * scale)
    conns.append((p_dt_br, p_sv_tl, "TCP / IP", 0.30, (20 * scale, -13 * scale)))

    # 3. Mobile -> VPS Server (Straight horizontal)
    y_m = nodes["mobile"]["y"] + 24 * scale
    p_mb_r = (nodes["mobile"]["x"] + nodes["mobile"]["w"] + dx, y_m)
    p_sv_bl = (nodes["server_vps"]["x"], y_m)
    conns.append((p_mb_r, p_sv_bl, "TCP / IP", 0.45, (0, -13 * scale)))

    # 4. Local Server -> Local DB (Strictly horizontal)
    y_h1 = nodes["server_local"]["y"] + 20 * scale
    p_sl_r = (nodes["server_local"]["x"] + nodes["server_local"]["w"] + dx, y_h1)
    p_dl_l = (nodes["db_local"]["x"], y_h1)
    conns.append((p_sl_r, p_dl_l, "TCP / IP", 0.50, (0, -13 * scale)))

    # 5. Local Server -> Resend API (Vertical)
    x_v1 = nodes["resend"]["x"] + nodes["resend"]["w"] / 2
    p_sl_b = (x_v1, nodes["server_local"]["y"] + nodes["server_local"]["h"])
    p_rs_t = (x_v1, nodes["resend"]["y"] + dy)
    conns.append((p_sl_b, p_rs_t, "HTTPS", 0.50, (30 * scale, 0)))

    # 6. Resend API -> VPS Server (Vertical)
    p_rs_b = (x_v1, nodes["resend"]["y"] + nodes["resend"]["h"])
    p_sv_t = (x_v1, nodes["server_vps"]["y"] + dy)
    conns.append((p_rs_b, p_sv_t, "HTTPS", 0.50, (30 * scale, 0)))

    # 7. VPS Server -> VPS DB (Strictly horizontal)
    y_h2 = nodes["server_vps"]["y"] + 20 * scale
    p_sv_r = (nodes["server_vps"]["x"] + nodes["server_vps"]["w"] + dx, y_h2)
    p_dv_l = (nodes["db_vps"]["x"], y_h2)
    conns.append((p_sv_r, p_dv_l, "TCP / IP", 0.50, (0, -13 * scale)))

    # DRAW CONNECTIONS & LABELS
    for p1, p2, lbl, t, (off_x, off_y) in conns:
        draw.line([p1, p2], fill=(20, 20, 20), width=int(1.6 * scale))

        lx = p1[0] + (p2[0] - p1[0]) * t + off_x
        ly = p1[1] + (p2[1] - p1[1]) * t + off_y

        bbox = draw.textbbox((0, 0), lbl, font=font_label)
        bw = bbox[2] - bbox[0]
        bh = bbox[3] - bbox[1]

        # Draw white background box for label to prevent line clash
        pad = 2 * scale
        draw.rectangle(
            [lx - bw / 2 - pad, ly - bh / 2 - pad, lx + bw / 2 + pad, ly + bh / 2 + pad],
            fill=(255, 255, 255)
        )
        draw.text((lx - bw / 2, ly - bh / 2), lbl, fill=(20, 20, 20), font=font_label)

    # DRAW 3D NODES
    for nid, n in nodes.items():
        x, y, w, h = n["x"], n["y"], n["w"], n["h"]

        # Top Face
        top_pts = [(x, y), (x + dx, y + dy), (x + w + dx, y + dy), (x + w, y)]
        draw.polygon(top_pts, fill=(255, 255, 255), outline=(0, 0, 0), width=line_w)

        # Right Face
        right_pts = [(x + w, y), (x + w + dx, y + dy), (x + w + dx, y + h + dy), (x + w, y + h)]
        draw.polygon(right_pts, fill=(255, 255, 255), outline=(0, 0, 0), width=line_w)

        # Front Face
        front_box = [x, y, x + w, y + h]
        draw.rectangle(front_box, fill=(255, 255, 255), outline=(0, 0, 0), width=line_w)

        # Stereotype Text
        st = n["stereo"]
        s_box = draw.textbbox((0, 0), st, font=font_stereo)
        s_w = s_box[2] - s_box[0]
        draw.text((x + (w - s_w) / 2, y + 8 * scale), st, fill=(30, 30, 30), font=font_stereo)

        # Name Text
        nm = n["name"]
        n_box = draw.textbbox((0, 0), nm, font=font_name)
        n_w = n_box[2] - n_box[0]
        draw.text((x + (w - n_w) / 2, y + 27 * scale), nm, fill=(0, 0, 0), font=font_name)

    target_w = canvas_w // scale
    target_h = canvas_h // scale
    final_img = img.resize((target_w, target_h), Image.Resampling.LANCZOS)

    png_path_docs = "docs/diagramas/diagrama_despliegue.png"
    png_path_public = "public/images/diagrama_despliegue.png"
    artifact_path = r"C:\Users\Cerezo Alex\.gemini\antigravity\brain\79c3592a-8363-4fa4-a132-af215ad350b3\diagrama_despliegue.png"

    final_img.save(png_path_docs, "PNG")
    final_img.save(png_path_public, "PNG")
    final_img.save(artifact_path, "PNG")
    print("PNG generated successfully.")

    generate_svg(nodes, conns, dx, dy, line_w, canvas_w, canvas_h, scale)

def generate_svg(nodes, conns, dx, dy, line_w, canvas_w, canvas_h, scale):
    sw = canvas_w / scale
    sh = canvas_h / scale
    sdx = dx / scale
    sdy = dy / scale
    slw = line_w / scale

    svg_lines = [
        f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {sw:.1f} {sh:.1f}" width="{sw:.1f}" height="{sh:.1f}" style="background-color: #ffffff; font-family: Segoe UI, Arial, sans-serif;">',
        '  <defs>',
        '    <filter id="shadow" x="-5%" y="-5%" width="120%" height="120%">',
        '      <feDropShadow dx="3" dy="4" stdDeviation="3" flood-color="#000000" flood-opacity="0.15" />',
        '    </filter>',
        '  </defs>',
    ]

    for p1, p2, lbl, t, (off_x, off_y) in conns:
        x1, y1 = p1[0] / scale, p1[1] / scale
        x2, y2 = p2[0] / scale, p2[1] / scale
        svg_lines.append(f'  <line x1="{x1:.1f}" y1="{y1:.1f}" x2="{x2:.1f}" y2="{y2:.1f}" stroke="#1e1e1e" stroke-width="1.6" />')

        lx = x1 + (x2 - x1) * t + (off_x / scale)
        ly = y1 + (y2 - y1) * t + (off_y / scale)
        svg_lines.append(f'  <rect x="{lx - 25:.1f}" y="{ly - 10:.1f}" width="50" height="20" fill="#ffffff" />')
        svg_lines.append(f'  <text x="{lx:.1f}" y="{ly:.1f}" font-size="13" font-weight="bold" fill="#1e1e1e" text-anchor="middle" dominant-baseline="middle">{lbl}</text>')

    for nid, n in nodes.items():
        x = n["x"] / scale
        y = n["y"] / scale
        w = n["w"] / scale
        h = n["h"] / scale

        svg_lines.append(f'  <g id="{nid}" filter="url(#shadow)">')
        svg_lines.append(f'    <polygon points="{x:.1f},{y:.1f} {x+sdx:.1f},{y+sdy:.1f} {x+w+sdx:.1f},{y+sdy:.1f} {x+w:.1f},{y:.1f}" fill="#ffffff" stroke="#000000" stroke-width="{slw:.1f}" />')
        svg_lines.append(f'    <polygon points="{x+w:.1f},{y:.1f} {x+w+sdx:.1f},{y+sdy:.1f} {x+w+sdx:.1f},{y+h+sdy:.1f} {x+w:.1f},{y+h:.1f}" fill="#ffffff" stroke="#000000" stroke-width="{slw:.1f}" />')
        svg_lines.append(f'    <rect x="{x:.1f}" y="{y:.1f}" width="{w:.1f}" height="{h:.1f}" fill="#ffffff" stroke="#000000" stroke-width="{slw:.1f}" />')
        svg_lines.append(f'    <text x="{x+w/2:.1f}" y="{y+18:.1f}" font-size="13" fill="#222222" text-anchor="middle">{n["stereo"]}</text>')
        svg_lines.append(f'    <text x="{x+w/2:.1f}" y="{y+38:.1f}" font-size="15" font-weight="bold" fill="#000000" text-anchor="middle">{n["name"]}</text>')
        svg_lines.append('  </g>')

    svg_lines.append('</svg>')

    svg_content = "\n".join(svg_lines)
    svg_path_docs = "docs/diagramas/diagrama_despliegue.svg"
    svg_path_public = "public/images/diagrama_despliegue.svg"
    with open(svg_path_docs, "w", encoding="utf-8") as f:
        f.write(svg_content)
    with open(svg_path_public, "w", encoding="utf-8") as f:
        f.write(svg_content)
    print("SVG generated successfully.")

create_deployment_diagram()
