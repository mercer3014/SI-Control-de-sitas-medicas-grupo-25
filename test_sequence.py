import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

def create_sequence_diagram(filename, title, actor_name, lifelines, activations, messages, width=760, height=510):
    scale = 3
    canvas_w = width * scale
    canvas_h = height * scale

    img = Image.new("RGBA", (canvas_w, canvas_h), (255, 255, 255, 255))
    draw = ImageDraw.Draw(img)

    # Fonts
    font_path_regular = "C:/Windows/Fonts/segoeui.ttf"
    font_path_bold = "C:/Windows/Fonts/segoeuib.ttf"
    if not os.path.exists(font_path_regular):
        font_path_regular = "C:/Windows/Fonts/arial.ttf"
        font_path_bold = "C:/Windows/Fonts/arialbd.ttf"

    font_sd = ImageFont.truetype(font_path_regular, 11 * scale)
    font_actor = ImageFont.truetype(font_path_bold, 11.5 * scale)
    font_box = ImageFont.truetype(font_path_bold, 11 * scale)
    font_msg = ImageFont.truetype(font_path_regular, 10.5 * scale)

    margin = 16 * scale
    frame_w = canvas_w - 2 * margin
    frame_h = canvas_h - 2 * margin

    # 1. Outer Frame
    draw.rectangle([margin, margin, margin + frame_w, margin + frame_h], outline=(40, 40, 40), width=int(1.5 * scale))

    # 2. Header Tab "sd CU..."
    sd_text = f"sd {title}"
    sd_bbox = draw.textbbox((0, 0), sd_text, font=font_sd)
    tab_tw = sd_bbox[2] - sd_bbox[0] + 18 * scale
    tab_th = 22 * scale
    tab_cut = 7 * scale

    tab_pts = [
        (margin, margin),
        (margin + tab_tw, margin),
        (margin + tab_tw + tab_cut, margin + tab_cut),
        (margin + tab_tw + tab_cut, margin + tab_th),
        (margin, margin + tab_th),
    ]
    draw.polygon(tab_pts, fill=(255, 255, 255), outline=(40, 40, 40), width=int(1.4 * scale))
    draw.text((margin + 7 * scale, margin + 4 * scale), sd_text, fill=(20, 20, 20), font=font_sd)

    # 3. Calculate Lifeline X positions
    num_lifelines = 1 + len(lifelines)  # 0 is Actor, 1..N are objects
    start_x = margin + 50 * scale
    avail_w = frame_w - 90 * scale
    spacing = avail_w / (num_lifelines - 1)

    lifeline_x = []
    for i in range(num_lifelines):
        lifeline_x.append(start_x + i * spacing)

    top_y = margin + 35 * scale
    bot_y = margin + frame_h - 20 * scale

    # 4. Draw Actor (Index 0)
    act_cx = lifeline_x[0]
    act_top = top_y + 5 * scale
    r_head = 7.5 * scale
    draw.ellipse([act_cx - r_head, act_top, act_cx + r_head, act_top + 2 * r_head], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.4 * scale))
    # Torso
    torso_top = act_top + 2 * r_head
    torso_bot = torso_top + 15 * scale
    draw.line([(act_cx, torso_top), (act_cx, torso_bot)], fill=(30, 30, 30), width=int(1.4 * scale))
    # Arms
    draw.line([(act_cx - 11 * scale, torso_top + 6 * scale), (act_cx + 11 * scale, torso_top + 6 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))
    # Legs
    draw.line([(act_cx, torso_bot), (act_cx - 9 * scale, torso_bot + 13 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))
    draw.line([(act_cx, torso_bot), (act_cx + 9 * scale, torso_bot + 13 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))
    # Actor Label
    lbl_text = f": {actor_name}"
    act_bbox = draw.textbbox((0, 0), lbl_text, font=font_actor)
    act_w = act_bbox[2] - act_bbox[0]
    draw.text((act_cx - act_w / 2, torso_bot + 17 * scale), lbl_text, fill=(0, 0, 0), font=font_actor)

    lifeline_start_y = [torso_bot + 33 * scale]

    # 5. Draw Object Lifeline Boxes (Index 1..N)
    box_w = min(115 * scale, spacing * 0.85)
    box_h = 32 * scale

    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    sdraw = ImageDraw.Draw(shadow_img)

    for i, name in enumerate(lifelines):
        cx = lifeline_x[i + 1]
        bx = cx - box_w / 2
        by = top_y + 20 * scale
        soff = 3 * scale
        sdraw.rectangle([bx + soff, by + soff, bx + box_w + soff, by + box_h + soff], fill=(0, 0, 0, 50))

    shadow_blurred = shadow_img.filter(ImageFilter.GaussianBlur(radius=2 * scale))
    img.paste(shadow_blurred, (0, 0), shadow_blurred)

    for i, name in enumerate(lifelines):
        cx = lifeline_x[i + 1]
        bx = cx - box_w / 2
        by = top_y + 20 * scale

        draw.rectangle([bx, by, bx + box_w, by + box_h], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.4 * scale))
        bbox = draw.textbbox((0, 0), name, font=font_box)
        bw = bbox[2] - bbox[0]
        bh = bbox[3] - bbox[1]
        draw.text((cx - bw / 2, by + (box_h - bh) / 2 - 1 * scale), name, fill=(10, 10, 10), font=font_box)
        lifeline_start_y.append(by + box_h)

    # 6. Draw Dashed Lifelines
    def draw_dashed(x, y1, y2):
        curr = y1
        dash = 5 * scale
        space = 4 * scale
        flag = True
        while curr < y2:
            step = dash if flag else space
            nxt = min(curr + step, y2)
            if flag:
                draw.line([(x, curr), (x, nxt)], fill=(90, 90, 90), width=int(1.2 * scale))
            curr = nxt
            flag = not flag

    for i in range(num_lifelines):
        draw_dashed(lifeline_x[i], lifeline_start_y[i], bot_y)

    # 7. Draw Activation Bars
    # activations: list of (lifeline_idx, y_start, y_end)
    act_w = 9 * scale
    for l_idx, y_s, y_e in activations:
        cx = lifeline_x[l_idx]
        y1 = margin + y_s * scale
        y2 = margin + y_e * scale
        draw.rectangle([cx - act_w / 2, y1, cx + act_w / 2, y2], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.2 * scale))

    # 8. Draw Messages
    # messages: list of (from_idx, to_idx, y, label, is_self)
    for m in messages:
        f_idx = m["from"]
        t_idx = m["to"]
        my = margin + m["y"] * scale
        lbl = m["label"]
        is_self = m.get("is_self", False)

        if is_self:
            cx = lifeline_x[f_idx]
            loop_w = 20 * scale
            loop_h = 16 * scale
            x_start = cx + act_w / 2
            # Self-loop lines: right, down, left
            draw.line([(x_start, my), (x_start + loop_w, my)], fill=(30, 30, 30), width=int(1.3 * scale))
            draw.line([(x_start + loop_w, my), (x_start + loop_w, my + loop_h)], fill=(30, 30, 30), width=int(1.3 * scale))
            draw.line([(x_start + loop_w, my + loop_h), (x_start, my + loop_h)], fill=(30, 30, 30), width=int(1.3 * scale))
            # Arrowhead pointing left
            ah = 6 * scale
            draw.polygon([(x_start, my + loop_h), (x_start + ah, my + loop_h - 4 * scale), (x_start + ah, my + loop_h + 4 * scale)], fill=(30, 30, 30))
            # Label
            draw.text((x_start + loop_w + 5 * scale, my + 1 * scale), lbl, fill=(20, 20, 20), font=font_msg)
        else:
            x1 = lifeline_x[f_idx]
            x2 = lifeline_x[t_idx]
            # Offset to start and end at activation bar edges
            if x2 > x1:
                sx = x1 + (act_w / 2 if f_idx > 0 else 0)
                ex = x2 - act_w / 2
                draw.line([(sx, my), (ex, my)], fill=(30, 30, 30), width=int(1.3 * scale))
                # Solid arrowhead pointing right
                ah = 6 * scale
                draw.polygon([(ex, my), (ex - ah, my - 3.5 * scale), (ex - ah, my + 3.5 * scale)], fill=(30, 30, 30))
            else:
                sx = x1 - (act_w / 2 if f_idx > 0 else 0)
                ex = x2 + act_w / 2
                draw.line([(sx, my), (ex, my)], fill=(30, 30, 30), width=int(1.3 * scale))
                # Solid arrowhead pointing left
                ah = 6 * scale
                draw.polygon([(ex, my), (ex + ah, my - 3.5 * scale), (ex + ah, my + 3.5 * scale)], fill=(30, 30, 30))

            # Label positioned above line
            bbox = draw.textbbox((0, 0), lbl, font=font_msg)
            lw = bbox[2] - bbox[0]
            draw.text((sx + (ex - sx) / 2 - lw / 2, my - 14 * scale), lbl, fill=(20, 20, 20), font=font_msg)

    # Downsample using Lanczos
    target_w = canvas_w // scale
    target_h = canvas_h // scale
    final_img = img.resize((target_w, target_h), Image.Resampling.LANCZOS)

    os.makedirs(os.path.dirname(filename), exist_ok=True)
    final_img.save(filename, "PNG")
    print(f"Generated sequence diagram: {filename}")

# Test run for CU-02 Gestion de Pacientes
create_sequence_diagram(
    filename="docs/diagramas/test_cu02.png",
    title="CU02: Gestion de Pacientes",
    actor_name="Recepcionista",
    lifelines=["Vista_Paciente", "Controller_Paciente", "Paciente"],
    activations=[
        (1, 100, 170), (2, 105, 160), (3, 110, 130), (3, 138, 155),
        (1, 220, 280), (2, 225, 270), (3, 230, 265),
        (1, 330, 390), (2, 335, 380), (3, 340, 375),
    ],
    messages=[
        {"from": 0, "to": 1, "y": 105, "label": "1: SeleccionarPaciente"},
        {"from": 1, "to": 2, "y": 110, "label": "2: MostrarPaciente"},
        {"from": 2, "to": 3, "y": 115, "label": "3: ObtenerPaciente"},
        {"from": 3, "to": 3, "y": 138, "label": "4: BuscarPaciente", "is_self": True},

        {"from": 0, "to": 1, "y": 225, "label": "5: NuevoPaciente"},
        {"from": 1, "to": 2, "y": 230, "label": "6: RegistrarPaciente"},
        {"from": 2, "to": 3, "y": 235, "label": "7: GuardarPaciente"},

        {"from": 0, "to": 1, "y": 335, "label": "8: EditarPaciente"},
        {"from": 1, "to": 2, "y": 340, "label": "9: ModificarPaciente"},
        {"from": 2, "to": 3, "y": 345, "label": "10: ActualizarPaciente"},
    ]
)
