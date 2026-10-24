import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

def draw_sequence_diagram(config):
    filename = config["filename"]
    title = config["title"]
    actor_name = config["actor"]
    lifelines = config["lifelines"]
    activations = config["activations"]
    messages = config["messages"]
    width = config.get("width", 840)
    height = config.get("height", 500)

    scale = 3
    canvas_w = width * scale
    canvas_h = height * scale

    img = Image.new("RGBA", (canvas_w, canvas_h), (255, 255, 255, 255))
    draw = ImageDraw.Draw(img)

    font_path_regular = "C:/Windows/Fonts/segoeui.ttf"
    font_path_bold = "C:/Windows/Fonts/segoeuib.ttf"
    if not os.path.exists(font_path_regular):
        font_path_regular = "C:/Windows/Fonts/arial.ttf"
        font_path_bold = "C:/Windows/Fonts/arialbd.ttf"

    font_sd = ImageFont.truetype(font_path_regular, 11 * scale)
    font_actor = ImageFont.truetype(font_path_bold, 11.5 * scale)
    font_box = ImageFont.truetype(font_path_bold, 10 * scale)
    font_msg = ImageFont.truetype(font_path_regular, 10 * scale)

    margin = 15 * scale
    frame_w = canvas_w - 2 * margin
    frame_h = canvas_h - 2 * margin

    # 1. Outer Frame
    draw.rectangle([margin, margin, margin + frame_w, margin + frame_h], outline=(40, 40, 40), width=int(1.5 * scale))

    # 2. Header Tab "sd CU..."
    sd_text = f"sd {title}"
    sd_bbox = draw.textbbox((0, 0), sd_text, font=font_sd)
    tab_tw = sd_bbox[2] - sd_bbox[0] + 16 * scale
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

    # 3. Lifeline X Positions
    num_lifelines = 1 + len(lifelines)
    start_x = margin + 50 * scale
    right_padding = 130 * scale
    avail_w = frame_w - (50 * scale) - right_padding
    spacing = avail_w / (num_lifelines - 1)

    lifeline_x = [start_x + i * spacing for i in range(num_lifelines)]

    top_y = margin + 30 * scale
    bot_y = margin + frame_h - 18 * scale

    # 4. Draw Actor (Index 0)
    act_cx = lifeline_x[0]
    act_top = top_y + 4 * scale
    r_head = 7 * scale
    draw.ellipse([act_cx - r_head, act_top, act_cx + r_head, act_top + 2 * r_head], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.4 * scale))
    torso_top = act_top + 2 * r_head
    torso_bot = torso_top + 14 * scale
    draw.line([(act_cx, torso_top), (act_cx, torso_bot)], fill=(30, 30, 30), width=int(1.4 * scale))
    draw.line([(act_cx - 10 * scale, torso_top + 5 * scale), (act_cx + 10 * scale, torso_top + 5 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))
    draw.line([(act_cx, torso_bot), (act_cx - 8 * scale, torso_bot + 12 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))
    draw.line([(act_cx, torso_bot), (act_cx + 8 * scale, torso_bot + 12 * scale)], fill=(30, 30, 30), width=int(1.4 * scale))

    lbl_text = f": {actor_name}"
    act_bbox = draw.textbbox((0, 0), lbl_text, font=font_actor)
    act_w = act_bbox[2] - act_bbox[0]
    draw.text((act_cx - act_w / 2, torso_bot + 16 * scale), lbl_text, fill=(0, 0, 0), font=font_actor)

    lifeline_start_y = [torso_bot + 32 * scale]

    # 5. Draw Object Lifeline Boxes
    box_w = min(128 * scale, spacing * 0.88)
    box_h = 30 * scale

    shadow_img = Image.new("RGBA", (canvas_w, canvas_h), (0, 0, 0, 0))
    sdraw = ImageDraw.Draw(shadow_img)

    for i, name in enumerate(lifelines):
        cx = lifeline_x[i + 1]
        bx = cx - box_w / 2
        by = top_y + 18 * scale
        soff = 3 * scale
        sdraw.rectangle([bx + soff, by + soff, bx + box_w + soff, by + box_h + soff], fill=(0, 0, 0, 45))

    shadow_blurred = shadow_img.filter(ImageFilter.GaussianBlur(radius=2 * scale))
    img.paste(shadow_blurred, (0, 0), shadow_blurred)

    for i, name in enumerate(lifelines):
        cx = lifeline_x[i + 1]
        bx = cx - box_w / 2
        by = top_y + 18 * scale

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
    act_w = 9 * scale
    for l_idx, y_s, y_e in activations:
        cx = lifeline_x[l_idx]
        y1 = margin + y_s * scale
        y2 = margin + y_e * scale
        draw.rectangle([cx - act_w / 2, y1, cx + act_w / 2, y2], fill=(255, 255, 255), outline=(30, 30, 30), width=int(1.2 * scale))

    # 8. Draw Messages
    for m in messages:
        f_idx = m["from"]
        t_idx = m["to"]
        my = margin + m["y"] * scale
        lbl = m["label"]
        is_self = m.get("is_self", False)

        if is_self:
            cx = lifeline_x[f_idx]
            loop_w = 18 * scale
            loop_h = 15 * scale
            x_start = cx + act_w / 2
            draw.line([(x_start, my), (x_start + loop_w, my)], fill=(30, 30, 30), width=int(1.3 * scale))
            draw.line([(x_start + loop_w, my), (x_start + loop_w, my + loop_h)], fill=(30, 30, 30), width=int(1.3 * scale))
            draw.line([(x_start + loop_w, my + loop_h), (x_start, my + loop_h)], fill=(30, 30, 30), width=int(1.3 * scale))
            ah = 5.5 * scale
            draw.polygon([(x_start, my + loop_h), (x_start + ah, my + loop_h - 3.5 * scale), (x_start + ah, my + loop_h + 3.5 * scale)], fill=(30, 30, 30))
            draw.text((x_start + loop_w + 4 * scale, my + 1 * scale), lbl, fill=(20, 20, 20), font=font_msg)
        else:
            x1 = lifeline_x[f_idx]
            x2 = lifeline_x[t_idx]
            if x2 > x1:
                sx = x1 + (act_w / 2 if f_idx > 0 else 0)
                ex = x2 - act_w / 2
                draw.line([(sx, my), (ex, my)], fill=(30, 30, 30), width=int(1.3 * scale))
                ah = 5.5 * scale
                draw.polygon([(ex, my), (ex - ah, my - 3.5 * scale), (ex - ah, my + 3.5 * scale)], fill=(30, 30, 30))
            else:
                sx = x1 - (act_w / 2 if f_idx > 0 else 0)
                ex = x2 + act_w / 2
                draw.line([(sx, my), (ex, my)], fill=(30, 30, 30), width=int(1.3 * scale))
                ah = 5.5 * scale
                draw.polygon([(ex, my), (ex + ah, my - 3.5 * scale), (ex + ah, my + 3.5 * scale)], fill=(30, 30, 30))

            bbox = draw.textbbox((0, 0), lbl, font=font_msg)
            lw = bbox[2] - bbox[0]
            draw.text((sx + (ex - sx) / 2 - lw / 2, my - 13 * scale), lbl, fill=(20, 20, 20), font=font_msg)

    target_w = canvas_w // scale
    target_h = canvas_h // scale
    final_img = img.resize((target_w, target_h), Image.Resampling.LANCZOS)

    os.makedirs(os.path.dirname(filename), exist_ok=True)
    final_img.save(filename, "PNG")

    base_name = os.path.basename(filename)
    public_path = os.path.join("public/images", base_name)
    artifact_path = os.path.join(r"C:\Users\Cerezo Alex\.gemini\antigravity\brain\79c3592a-8363-4fa4-a132-af215ad350b3", base_name)
    
    os.makedirs("public/images", exist_ok=True)
    final_img.save(public_path, "PNG")
    final_img.save(artifact_path, "PNG")
    print(f"Generated: {filename}")


def generate_all():
    diagrams = [
        # CU-01: Gestionar Usuarios
        {
            "filename": "docs/diagramas/cu01_gestionar_usuarios.png",
            "title": "CU01: Gestionar Usuarios",
            "actor": "Administrador",
            "width": 890,
            "height": 520,
            "lifelines": ["VistaUsuario", "Controller_Usuario", "Usuario", "Rol", "Permiso"],
            "activations": [
                (1, 95, 175), (2, 100, 168), (3, 105, 140), (4, 110, 160), (5, 115, 135), (5, 140, 155),
                (1, 215, 290), (2, 220, 285), (3, 225, 255), (4, 255, 280), (5, 260, 275),
                (1, 330, 405), (2, 335, 400), (3, 340, 370), (4, 370, 395), (5, 375, 390),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarUsuario"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : BuscarUsuario"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerUsuario"},
                {"from": 3, "to": 4, "y": 115, "label": "4 : SolicitarRol"},
                {"from": 4, "to": 5, "y": 120, "label": "5 : BuscarPermisos"},
                {"from": 5, "to": 5, "y": 140, "label": "6 : VerificarPermisos", "is_self": True},

                {"from": 0, "to": 1, "y": 220, "label": "7 : NuevoUsuario"},
                {"from": 1, "to": 2, "y": 225, "label": "8 : RegistrarUsuario"},
                {"from": 2, "to": 3, "y": 230, "label": "9 : CrearUsuario"},
                {"from": 2, "to": 4, "y": 255, "label": "10 : AsignarRol"},
                {"from": 4, "to": 5, "y": 260, "label": "11 : ConfigurarPermisos"},

                {"from": 0, "to": 1, "y": 335, "label": "12 : EditarUsuario"},
                {"from": 1, "to": 2, "y": 340, "label": "13 : BuscarUsuario"},
                {"from": 2, "to": 3, "y": 345, "label": "14 : Buscar"},
                {"from": 3, "to": 4, "y": 370, "label": "15 : ObtenerRol"},
                {"from": 4, "to": 5, "y": 375, "label": "16 : ObtenerPermisos"},
            ]
        },

        # CU-02: Gestionar Roles y Permisos
        {
            "filename": "docs/diagramas/cu02_gestionar_roles_permisos.png",
            "title": "CU02: Gestionar Roles y Permisos",
            "actor": "Administrador",
            "width": 780,
            "height": 500,
            "lifelines": ["Vista_Rol", "Controller_Rol", "Rol", "Permiso"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 135), (4, 115, 145),
                (1, 210, 280), (2, 215, 270), (3, 220, 255), (4, 245, 265),
                (1, 320, 390), (2, 325, 380), (4, 335, 370),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarRol"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : MostrarRoles"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerRoles"},
                {"from": 3, "to": 4, "y": 115, "label": "4 : ObtenerPermisos"},

                {"from": 0, "to": 1, "y": 215, "label": "5 : ModificarPermisos"},
                {"from": 1, "to": 2, "y": 220, "label": "6 : ActualizarPermisos"},
                {"from": 2, "to": 3, "y": 225, "label": "7 : SincronizarPermisos"},
                {"from": 3, "to": 4, "y": 245, "label": "8 : AsignarPermisos"},

                {"from": 0, "to": 1, "y": 325, "label": "9 : ValidarAcceso"},
                {"from": 1, "to": 2, "y": 330, "label": "10 : VerificarPermisos"},
                {"from": 2, "to": 4, "y": 335, "label": "11 : ConsultarPermiso"},
            ]
        },

        # CU-03: Consultar Bitácora de Auditoría
        {
            "filename": "docs/diagramas/cu03_consultar_bitacora.png",
            "title": "CU03: Consultar Bitácora de Auditoría",
            "actor": "Administrador",
            "width": 780,
            "height": 500,
            "lifelines": ["Vista_Bitacora", "Controller_Bitacora", "Bitacora", "ServerLogs"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 125), (3, 132, 150),
                (1, 210, 275), (2, 215, 265), (3, 220, 255),
                (1, 320, 385), (2, 325, 375), (4, 330, 365),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarBitacora"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : FiltrarLogs"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerRegistros"},
                {"from": 3, "to": 3, "y": 132, "label": "4 : FormatearAuditoria", "is_self": True},

                {"from": 0, "to": 1, "y": 215, "label": "5 : VerDetalleEvento"},
                {"from": 1, "to": 2, "y": 220, "label": "6 : ConsultarDetalle"},
                {"from": 2, "to": 3, "y": 225, "label": "7 : ObtenerValoresPrevios"},

                {"from": 0, "to": 1, "y": 325, "label": "8 : VerLogsServidor"},
                {"from": 1, "to": 2, "y": 330, "label": "9 : LeerArchivosLog"},
                {"from": 2, "to": 4, "y": 335, "label": "10 : ObtenerLineasLog"},
            ]
        },

        # CU-04: Gestionar Respaldos de Base de Datos
        {
            "filename": "docs/diagramas/cu04_gestionar_respaldos.png",
            "title": "CU04: Gestionar Respaldos de Base de Datos",
            "actor": "Administrador",
            "width": 760,
            "height": 490,
            "lifelines": ["Vista_Respaldo", "Controller_Backup", "BaseDeDatos"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 145),
                (1, 210, 280), (2, 215, 270), (3, 220, 240), (3, 248, 265),
                (1, 320, 385), (2, 325, 375), (1, 350, 380),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarRespaldos"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : ListarRespaldos"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerArchivosSQL"},

                {"from": 0, "to": 1, "y": 215, "label": "4 : SolicitarRespaldo"},
                {"from": 1, "to": 2, "y": 220, "label": "5 : EjecutarBackup"},
                {"from": 2, "to": 3, "y": 225, "label": "6 : EjecutarPgDump"},
                {"from": 3, "to": 3, "y": 248, "label": "7 : GenerarArchivoSQL", "is_self": True},

                {"from": 0, "to": 1, "y": 325, "label": "8 : DescargarRespaldo"},
                {"from": 1, "to": 2, "y": 330, "label": "9 : PrepararDescarga"},
                {"from": 2, "to": 1, "y": 350, "label": "10 : EnviarArchivoSQL"},
            ]
        },

        # CU-05: Gestionar Especialidades
        {
            "filename": "docs/diagramas/cu05_gestionar_especialidades.png",
            "title": "CU05: Gestionar Especialidades",
            "actor": "Administrador",
            "width": 760,
            "height": 490,
            "lifelines": ["Vista_Especialidad", "Controller_Especialidad", "Especialidad"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 125), (3, 132, 150),
                (1, 210, 275), (2, 215, 265), (3, 220, 255),
                (1, 320, 385), (2, 325, 375), (3, 330, 365),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarEspecialidad"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : MostrarEspecialidades"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerEspecialidades"},
                {"from": 3, "to": 3, "y": 132, "label": "4 : FiltrarActivas", "is_self": True},

                {"from": 0, "to": 1, "y": 215, "label": "5 : NuevaEspecialidad"},
                {"from": 1, "to": 2, "y": 220, "label": "6 : RegistrarEspecialidad"},
                {"from": 2, "to": 3, "y": 225, "label": "7 : GuardarEspecialidad"},

                {"from": 0, "to": 1, "y": 325, "label": "8 : EditarEspecialidad"},
                {"from": 1, "to": 2, "y": 330, "label": "9 : ModificarEspecialidad"},
                {"from": 2, "to": 3, "y": 335, "label": "10 : ActualizarEspecialidad"},
            ]
        },

        # CU-06: Gestionar Médicos
        {
            "filename": "docs/diagramas/cu06_gestionar_medicos.png",
            "title": "CU06: Gestionar Médicos",
            "actor": "Administrador",
            "width": 780,
            "height": 500,
            "lifelines": ["Vista_Medico", "Controller_Medico", "Medico", "Especialidad"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 135), (4, 115, 145),
                (1, 210, 280), (2, 215, 270), (3, 220, 250), (4, 245, 265),
                (1, 320, 390), (2, 325, 380), (3, 330, 355), (3, 360, 375),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarMedico"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : MostrarMedico"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerMedico"},
                {"from": 3, "to": 4, "y": 115, "label": "4 : ObtenerEspecialidad"},

                {"from": 0, "to": 1, "y": 215, "label": "5 : NuevoMedico"},
                {"from": 1, "to": 2, "y": 220, "label": "6 : RegistrarMedico"},
                {"from": 2, "to": 3, "y": 225, "label": "7 : GuardarMedico"},
                {"from": 2, "to": 4, "y": 245, "label": "8 : AsignarEspecialidad"},

                {"from": 0, "to": 1, "y": 325, "label": "9 : EditarMedico"},
                {"from": 1, "to": 2, "y": 330, "label": "10 : ModificarMedico"},
                {"from": 2, "to": 3, "y": 335, "label": "11 : ActualizarMedico"},
                {"from": 3, "to": 3, "y": 360, "label": "12 : ActualizarTarifa", "is_self": True},
            ]
        },

        # CU-07: Gestionar Perfil Médico Base
        {
            "filename": "docs/diagramas/cu07_perfil_medico_base.png",
            "title": "CU07: Gestionar Perfil Médico Base",
            "actor": "Medico",
            "width": 930,
            "height": 510,
            "lifelines": ["Vista_PerfilMedico", "Controller_PerfilMedico", "Medico", "Especialidad", "Persona"],
            "activations": [
                (1, 95, 175), (2, 100, 170), (3, 105, 140), (4, 115, 145), (5, 140, 165),
                (1, 215, 285), (2, 220, 280), (3, 225, 275), (3, 250, 268),
                (1, 325, 395), (2, 330, 390), (5, 335, 385),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarPerfil"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : MostrarPerfil"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerDatosPerfil"},
                {"from": 3, "to": 4, "y": 115, "label": "4 : ObtenerEspecialidad"},
                {"from": 3, "to": 5, "y": 140, "label": "5 : ObtenerDatosPersonales"},

                {"from": 0, "to": 1, "y": 220, "label": "6 : EditarPerfil"},
                {"from": 1, "to": 2, "y": 225, "label": "7 : ModificarDatos"},
                {"from": 2, "to": 3, "y": 230, "label": "8 : ActualizarPerfil"},
                {"from": 3, "to": 3, "y": 250, "label": "9 : ActualizarTarifa", "is_self": True},

                {"from": 0, "to": 1, "y": 330, "label": "10 : EditarContacto"},
                {"from": 1, "to": 2, "y": 335, "label": "11 : ModificarContacto"},
                {"from": 2, "to": 5, "y": 340, "label": "12 : ActualizarPersona"},
            ]
        },

        # CU-08: Gestionar Pacientes
        {
            "filename": "docs/diagramas/cu08_gestionar_pacientes.png",
            "title": "CU08: Gestionar Pacientes",
            "actor": "Recepcionista",
            "width": 760,
            "height": 490,
            "lifelines": ["Vista_Paciente", "Controller_Paciente", "Paciente"],
            "activations": [
                (1, 95, 165), (2, 100, 155), (3, 105, 125), (3, 132, 150),
                (1, 210, 275), (2, 215, 265), (3, 220, 255),
                (1, 320, 385), (2, 325, 375), (3, 330, 365),
            ],
            "messages": [
                {"from": 0, "to": 1, "y": 100, "label": "1 : SeleccionarPaciente"},
                {"from": 1, "to": 2, "y": 105, "label": "2 : MostrarPaciente"},
                {"from": 2, "to": 3, "y": 110, "label": "3 : ObtenerPaciente"},
                {"from": 3, "to": 3, "y": 132, "label": "4 : BuscarPaciente", "is_self": True},

                {"from": 0, "to": 1, "y": 215, "label": "5 : NuevoPaciente"},
                {"from": 1, "to": 2, "y": 220, "label": "6 : RegistrarPaciente"},
                {"from": 2, "to": 3, "y": 225, "label": "7 : GuardarPaciente"},

                {"from": 0, "to": 1, "y": 325, "label": "8 : EditarPaciente"},
                {"from": 1, "to": 2, "y": 330, "label": "9 : ModificarPaciente"},
                {"from": 2, "to": 3, "y": 335, "label": "10 : ActualizarPaciente"},
            ]
        },
    ]

    for diag in diagrams:
        draw_sequence_diagram(diag)

if __name__ == "__main__":
    generate_all()
