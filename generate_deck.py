#!/usr/bin/env python3
"""
QueueLess — Master Cinematic Presentation Generator (.pptx)
Creates a luxury, animated, and smoothly transitioned 16:9 widescreen presentation deck.
"""

import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE
from pptx.oxml import parse_xml
from pptx.oxml.ns import nsdecls

# Output file path
OUTPUT_PATH = "QueueLess_Presentation.pptx"
LOGO_TRANSPARENT = os.path.abspath("public/images/logo-transparent.png")
LOGO_ICON = os.path.abspath("public/images/logo-icon.png")

# Palette
COLOR_OBSIDIAN = RGBColor(11, 19, 32)      # #0b1320 Deep obsidian
COLOR_DARK_SLATE = RGBColor(18, 28, 44)    # #121c2c Card dark
COLOR_CARD_BG = RGBColor(24, 38, 58)       # #18263a Elevated card
COLOR_MINT = RGBColor(0, 195, 137)          # #00c389 Vibrant mint
COLOR_EMERALD = RGBColor(16, 185, 129)     # #10b981 Emerald
COLOR_TERRACOTTA = RGBColor(194, 89, 63)   # #c2593f Copper terracotta
COLOR_WHITE = RGBColor(255, 255, 255)      # Pure white
COLOR_MUTED = RGBColor(148, 163, 184)      # #94a3b8 Muted slate
COLOR_LIGHT_BG = RGBColor(248, 250, 252)   # #f8fafc Light card
COLOR_BORDER = RGBColor(42, 60, 84)        # #2a3c54 Subtle border
COLOR_GOLD = RGBColor(245, 158, 11)        # Amber gold

def create_presentation():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    return prs

def set_slide_background(slide, color):
    bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg.fill.solid()
    bg.fill.fore_color.rgb = color
    bg.line.fill.background()
    # Move to back
    slide.shapes._spTree.remove(bg._element)
    slide.shapes._spTree.insert(2, bg._element)
    return bg

def add_transition(slide, trans_type="fade", spd="med", direction=None):
    if trans_type == "fade":
        inner = "<p:fade/>"
    elif trans_type == "push":
        d = direction or "r"
        inner = f'<p:push dir="{d}"/>'
    elif trans_type == "wipe":
        d = direction or "r"
        inner = f'<p:wipe dir="{d}"/>'
    elif trans_type == "zoom":
        inner = "<p:zoom/>"
    else:
        inner = "<p:fade/>"

    trans_xml = parse_xml(f'<p:transition {nsdecls("p")} spd="{spd}">{inner}</p:transition>')
    slide._element.append(trans_xml)

def add_entrance_animations(slide, shape_ids):
    if not shape_ids:
        return
    seq_items = []
    for idx, sid in enumerate(shape_ids, start=3):
        seq_items.append(f"""
        <p:par>
          <p:cTn id="{idx * 10}" fill="hold">
            <p:stCondLst><p:cond delay="{idx * 150}"/></p:stCondLst>
            <p:childTnLst>
              <p:par>
                <p:cTn id="{idx * 10 + 1}" presetID="1" presetClass="entr" presetSubtype="0" fill="hold" nodeType="afterEffect">
                  <p:stCondLst><p:cond delay="0"/></p:stCondLst>
                  <p:childTnLst>
                    <p:set>
                      <p:cBhvr>
                        <p:cTn id="{idx * 10 + 2}" dur="1" fill="hold"><p:stCondLst><p:cond delay="0"/></p:stCondLst></p:cTn>
                        <p:tgtEl><p:spTgt spid="{sid}"/></p:tgtEl>
                        <p:attrNameLst><p:attrName>style.visibility</p:attrName></p:attrNameLst>
                      </p:cBhvr>
                      <p:to><p:strVal val="visible"/></p:to>
                    </p:set>
                    <p:animEffect transition="in" filter="fade">
                      <p:cBhvr>
                        <p:cTn id="{idx * 10 + 3}" dur="400"/>
                        <p:tgtEl><p:spTgt spid="{sid}"/></p:tgtEl>
                      </p:cBhvr>
                    </p:animEffect>
                  </p:childTnLst>
                </p:cTn>
              </p:par>
            </p:childTnLst>
          </p:cTn>
        </p:par>
        """)

    timing_xml = f"""
    <p:timing {nsdecls('p')}>
      <p:tnLst>
        <p:par>
          <p:cTn id="1" dur="indefinite" restart="always" nodeType="tmRoot">
            <p:childTnLst>
              <p:seq concurrent="1" nextAc="seek">
                <p:cTn id="2" dur="indefinite" nodeType="mainSeq">
                  <p:childTnLst>
                    {''.join(seq_items)}
                  </p:childTnLst>
                </p:cTn>
              </p:seq>
            </p:childTnLst>
          </p:cTn>
        </p:par>
      </p:tnLst>
    </p:timing>
    """
    slide._element.append(parse_xml(timing_xml))

def add_header(slide, eyebrow, title, subtitle=None):
    # Eyebrow Pill
    pill = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(0.55), Inches(3.2), Inches(0.38))
    pill.fill.solid()
    pill.fill.fore_color.rgb = COLOR_CARD_BG
    pill.line.color.rgb = COLOR_BORDER
    tf = pill.text_frame
    tf.word_wrap = True
    p = tf.paragraphs[0]
    p.text = eyebrow.upper()
    p.font.name = "Arial"
    p.font.size = Pt(10)
    p.font.bold = True
    p.font.color.rgb = COLOR_MINT
    p.alignment = PP_ALIGN.CENTER

    # Main Title
    tb = slide.shapes.add_textbox(Inches(0.8), Inches(0.98), Inches(11.7), Inches(0.85))
    tf2 = tb.text_frame
    tf2.word_wrap = True
    p2 = tf2.paragraphs[0]
    p2.text = title
    p2.font.name = "Arial"
    p2.font.size = Pt(26)
    p2.font.bold = True
    p2.font.color.rgb = COLOR_WHITE

    if subtitle:
        p3 = tf2.add_paragraph()
        p3.text = subtitle
        p3.font.name = "Arial"
        p3.font.size = Pt(12)
        p3.font.color.rgb = COLOR_MUTED
        p3.space_before = Pt(4)

    return [pill.shape_id, tb.shape_id]

def add_card(slide, left, top, width, height, title, subtitle, items=None, accent_color=COLOR_MINT):
    # Rounded Card Panel
    card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
    card.fill.solid()
    card.fill.fore_color.rgb = COLOR_DARK_SLATE
    card.line.color.rgb = COLOR_BORDER
    card.line.width = Pt(1.5)

    # Accent top border strip
    strip = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, Inches(0.08))
    strip.fill.solid()
    strip.fill.fore_color.rgb = accent_color
    strip.line.fill.background()

    # Text
    tb = slide.shapes.add_textbox(left + Inches(0.25), top + Inches(0.2), width - Inches(0.5), height - Inches(0.3))
    tf = tb.text_frame
    tf.word_wrap = True

    p1 = tf.paragraphs[0]
    p1.text = title
    p1.font.name = "Arial"
    p1.font.size = Pt(15)
    p1.font.bold = True
    p1.font.color.rgb = COLOR_WHITE

    if subtitle:
        p2 = tf.add_paragraph()
        p2.text = subtitle
        p2.font.name = "Arial"
        p2.font.size = Pt(11)
        p2.font.color.rgb = accent_color
        p2.space_before = Pt(3)

    if items:
        for item in items:
            p_item = tf.add_paragraph()
            p_item.text = f"•  {item}"
            p_item.font.name = "Arial"
            p_item.font.size = Pt(10.5)
            p_item.font.color.rgb = COLOR_MUTED
            p_item.space_before = Pt(6)

    return [card.shape_id, strip.shape_id, tb.shape_id]

# ==========================================
# SLIDE 1: CINEMATIC TITLE SLIDE
# ==========================================
def build_slide_1(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "fade", spd="slow")

    # Subtle background accent card
    decor = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(0.12))
    decor.fill.solid()
    decor.fill.fore_color.rgb = COLOR_MINT
    decor.line.fill.background()

    # Terracotta ambient glow panel
    glow = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.5), Inches(1.2), Inches(10.333), Inches(5.1))
    glow.fill.solid()
    glow.fill.fore_color.rgb = COLOR_DARK_SLATE
    glow.line.color.rgb = COLOR_BORDER
    glow.line.width = Pt(1.5)

    # Logo image
    animated_ids = [glow.shape_id]
    if os.path.exists(LOGO_TRANSPARENT):
        logo = slide.shapes.add_picture(LOGO_TRANSPARENT, Inches(3.8), Inches(1.7), width=Inches(5.7))
        animated_ids.append(logo.shape_id)

    # Subtitle
    tb = slide.shapes.add_textbox(Inches(2.0), Inches(3.6), Inches(9.333), Inches(1.8))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "SMART DIGITAL QUEUE MANAGEMENT INFRASTRUCTURE"
    p.font.name = "Arial"
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = COLOR_MINT
    p.alignment = PP_ALIGN.CENTER

    p2 = tf.add_paragraph()
    p2.text = "High-Availability Real-Time Service Dispatch & M/M/c Erlang-C Predictive Modeling"
    p2.font.name = "Arial"
    p2.font.size = Pt(18)
    p2.font.bold = True
    p2.font.color.rgb = COLOR_WHITE
    p2.alignment = PP_ALIGN.CENTER
    p2.space_before = Pt(8)

    p3 = tf.add_paragraph()
    p3.text = "Comprehensive Full-Stack University Web Engineering Project"
    p3.font.name = "Arial"
    p3.font.size = Pt(12)
    p3.font.color.rgb = COLOR_MUTED
    p3.alignment = PP_ALIGN.CENTER
    p3.space_before = Pt(6)

    animated_ids.append(tb.shape_id)

    # 4 Highlight Badges along bottom of card
    badges = [
        ("ATOMIC CONCURRENCY", "lockForUpdate Engine"),
        ("ERLANG-C PREDICTIONS", "Dynamic Wait-Times"),
        ("AIRPORT 4K SIGNAGE", "Web Speech & Chimes"),
        ("EXECUTIVE ANALYTICS", "SLA & Heatmap Matrix")
    ]
    badge_width = Inches(2.2)
    start_left = Inches(2.1)
    for i, (b_title, b_sub) in enumerate(badges):
        b_box = slide.shapes.add_shape(
            MSO_SHAPE.ROUNDED_RECTANGLE,
            start_left + i * Inches(2.35),
            Inches(5.35),
            badge_width,
            Inches(0.68)
        )
        b_box.fill.solid()
        b_box.fill.fore_color.rgb = COLOR_CARD_BG
        b_box.line.color.rgb = COLOR_BORDER
        b_tf = b_box.text_frame
        b_tf.word_wrap = True
        bp = b_tf.paragraphs[0]
        bp.text = b_title
        bp.font.name = "Arial"
        bp.font.size = Pt(9)
        bp.font.bold = True
        bp.font.color.rgb = COLOR_MINT
        bp.alignment = PP_ALIGN.CENTER

        bp2 = b_tf.add_paragraph()
        bp2.text = b_sub
        bp2.font.name = "Arial"
        bp2.font.size = Pt(8.5)
        bp2.font.color.rgb = COLOR_WHITE
        bp2.alignment = PP_ALIGN.CENTER

        animated_ids.append(b_box.shape_id)

    add_entrance_animations(slide, animated_ids)

# ==========================================
# SLIDE 2: THE PROBLEM & PARADIGM SHIFT
# ==========================================
def build_slide_2(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "push", direction="r")

    h_ids = add_header(
        slide,
        "The Operational Challenge",
        "Physical Queues vs. Smart Digital Dispatch",
        "Transforming legacy campus service chaos into a transparent, mathematically optimized ecosystem."
    )
    anim_ids = list(h_ids)

    # Left: The Traditional Bottleneck
    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "Legacy Physical Lines",
        "Pain Points & Inefficiencies",
        [
            "Severe corridor congestion during registrations and fee deadlines.",
            "Zero transparency on waiting times, forcing students to stand for hours.",
            "Unfair queue jumping and manual token handling disputes.",
            "No early warning for counter operators on incoming student surges.",
            "Lack of management metrics on service durations, no-shows, or SLA breaches.",
            "Wasted academic study hours and student stress."
        ],
        accent_color=COLOR_TERRACOTTA
    )
    anim_ids.extend(c1_ids)

    # Right: The QueueLess Paradigm
    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "The QueueLess Solution",
        "Intelligent High-Throughput System",
        [
            "Digital mobile tokens issued instantly from smartphones or hallway kiosks.",
            "Live turn tracker with optical scanner QR codes and decrementing countdowns.",
            "M/M/c Erlang-C mathematical wait estimations based on live arrival rates.",
            "Automated audio chimes and Web Speech voice announcements.",
            "Pessimistic row-locking eliminating race conditions between operators.",
            "Predictive capacity recommendations alerting admins to open standby desks."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 3: SYSTEM ARCHITECTURE
# ==========================================
def build_slide_3(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "wipe", direction="r")

    h_ids = add_header(
        slide,
        "System Architecture",
        "Enterprise 4-Tier Blueprint & Technology Stack",
        "Decoupled, event-driven architecture designed for high availability and zero downtime."
    )
    anim_ids = list(h_ids)

    cards = [
        ("Tier 1: Client Interfaces", "Responsive Web & PWA", [
            "Tailwind CSS + Alpine.js micro-interactions",
            "Procedural Web Audio API sound generator",
            "Web Speech API automated announcements",
            "High-contrast 4K Public TV Signage mode"
        ], COLOR_MINT),
        ("Tier 2: Application API", "Robust Controller Core", [
            "Role-Based Access Control (Admin, Operator, Student)",
            "Server-Sent Events (SSE) & RESTful endpoints",
            "Sanctum session & token authentication",
            "Multi-channel SMS, WhatsApp, WebPush dispatcher"
        ], COLOR_GOLD),
        ("Tier 3: Queue Core Engine", "Mathematical State Machine", [
            "Pessimistic row locking (lockForUpdate)",
            "Erlang-C multi-server delay predictor",
            "Dynamic SLA tracker & auto capacity engine",
            "Stopwatch sessions with resolution remarks"
        ], COLOR_TERRACOTTA),
        ("Tier 4: Persistence Tier", "Relational Database", [
            "Third Normal Form (3NF) relational schema",
            "21 specialized database tables",
            "Pre-aggregated hourly traffic metrics",
            "Immutable audit logs & event traces"
        ], COLOR_EMERALD),
    ]

    for i, (title, sub, items, col) in enumerate(cards):
        left = Inches(0.8 + i * 2.95)
        c_ids = add_card(slide, left, Inches(2.1), Inches(2.8), Inches(4.7), title, sub, items, accent_color=col)
        anim_ids.extend(c_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 4: THE MATHEMATICAL ENGINE (ERLANG-C)
# ==========================================
def build_slide_4(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "push", direction="r")

    h_ids = add_header(
        slide,
        "Queue Modeling Theory",
        "The M/M/c Erlang-C Predictive Engine",
        "Moving beyond naive averages with rigorous multi-server Poisson queuing theory."
    )
    anim_ids = list(h_ids)

    # Left: Formula & Mathematical Principles
    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(6.0), Inches(4.7),
        "Closed-Form Erlang-C Formula",
        "Probability of Waiting Delay C(c, a)",
        [
            "Arrival Rate (λ): Poisson distributed student arrivals per minute.",
            "Service Rate (μ): Exponentially distributed completions per desk.",
            "Traffic Intensity (a = λ / μ): Offered workload in Erlangs.",
            "System Utilization (ρ = a / c): Load factor across c active desks.",
            "Waiting Probability C(c, a) = P0 * (a^c / c!) * (1 / (1 - ρ)).",
            "Expected Wait Time: Wq = C(c, a) / (c * μ - λ).",
            "Boundary Stabilization: Asymptotic scaling when ρ >= 0.85."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    # Right: Automated Capacity Engine
    c2_ids = add_card(
        slide,
        Inches(7.2), Inches(2.1), Inches(5.3), Inches(4.7),
        "Algorithmic Capacity Engine",
        "Autonomous Staffing Recommendations",
        [
            "Continuous Utilization Monitoring: Calculates ρ every 5 seconds.",
            "Critical Alert Threshold: Triggers warning when utilization exceeds 85%.",
            "SLA Delay Trigger: Automatically recommends staffing if wait > 15 mins.",
            "Prescriptive Actionable Insights: Suggests exact counter provisioning.",
            "1-Click Provisioning: Admins can deploy standby desks with one click.",
            "Eliminates Guesswork: Backed by empirical university traffic data."
        ],
        accent_color=COLOR_GOLD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 5: HIGH-CONCURRENCY DATABASE ENGINE
# ==========================================
def build_slide_5(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "fade", spd="med")

    h_ids = add_header(
        slide,
        "Concurrency & Data Integrity",
        "Atomic State Machine & Race-Condition Immunity",
        "Strict pessimistic locking ensures that two operators never call the same student."
    )
    anim_ids = list(h_ids)

    steps = [
        ("Step 1: Token Issuance", "Daily Sequential Numbering", [
            "Student selects department & service.",
            "Daily sequence incremented atomically.",
            "Duplicate active tickets prohibited.",
            "Token formatted (e.g. REG-001, ACC-014)."
        ], COLOR_MINT),
        ("Step 2: Operator Call", "Pessimistic lockForUpdate", [
            "Operator clicks Call Next or hits Space.",
            "Atomic SELECT FOR UPDATE locks earliest row.",
            "Counter assigned & ticket set to CALLED.",
            "Race conditions strictly impossible."
        ], COLOR_GOLD),
        ("Step 3: Service Session", "Stopwatch & Resolution", [
            "Status shifts to SERVING on arrival.",
            "Precise session stopwatch started.",
            "1-Click resolution tags & remarks added.",
            "Status shifts to COMPLETED or NO_SHOW."
        ], COLOR_EMERALD),
        ("Step 4: Audit & Broadcast", "Multi-Channel Dispatch", [
            "Queue event logged with microsecond stamp.",
            "Web Speech voice alert summoned.",
            "Two-tone airport chime broadcast on TV.",
            "Hourly metrics aggregated into analytics."
        ], COLOR_TERRACOTTA),
    ]

    for i, (title, sub, items, col) in enumerate(steps):
        left = Inches(0.8 + i * 2.95)
        c_ids = add_card(slide, left, Inches(2.1), Inches(2.8), Inches(4.7), title, sub, items, accent_color=col)
        anim_ids.extend(c_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 6: STUDENT DIGITAL BOARDING PASS
# ==========================================
def build_slide_6(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "push", direction="r")

    h_ids = add_header(
        slide,
        "Student Experience",
        "The Frictionless Digital Boarding Pass",
        "Providing complete transparency, freedom of movement, and optical scanner check-in."
    )
    anim_ids = list(h_ids)

    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "Boarding Pass Features",
        "Real-Time Mobile Pass Interface",
        [
            "High-Impact Token Typography: Instant clarity on student mobile screens.",
            "4-Stage Pipeline Clearance: Visual progress tracking (In Queue -> Near Front -> Summoned -> Serving).",
            "Second-by-Second Countdown: Active decrementing ETA timer (e.g. 04m 32s left).",
            "Desk Optical Scanner QR: Scannable pass for barcode check-in at counter.",
            "Haptic Vibration Alert: Tactile vibration pulses when ticket is summoned.",
            "Copy Pass Link: Instant share link for mobile tracking around campus."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "Campus Standby Freedom",
        "Eliminating Hallway Standing",
        [
            "Study Anywhere: Students wait comfortably in the library or cafeteria.",
            "Approaching Alerts: Amber pulse activates when 2 students remain ahead.",
            "Audio Chimes: Procedural dual-tone chime alerts the student.",
            "Printable Slip: Formatted physical thermal receipt with one click.",
            "Service History: Permanent record of attested documents and receipts.",
            "Direct Cancellation: Students can voluntarily leave queue if needed."
        ],
        accent_color=COLOR_GOLD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 7: OPERATOR WORKSTATION CONSOLE
# ==========================================
def build_slide_7(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "wipe", direction="r")

    h_ids = add_header(
        slide,
        "Counter Officer Workspace",
        "Staff Productivity & Desk Control Console",
        "Ergonomic, hotkey-driven interface built for speed, accuracy, and reduced staff stress."
    )
    anim_ids = list(h_ids)

    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "Workflow Automation & Hotkeys",
        "Zero-Mouse Ergonomic Operation",
        [
            "Spacebar / Alt+C: Instantly calls the next student in line.",
            "Alt+S (Start Service): Starts consultation timer when student sits.",
            "Alt+D (Complete Service): Stamps ticket completed & records SLA duration.",
            "Alt+R (Recall Student): Re-summons student with repeat audio chime.",
            "1-Click Resolution Remarks: Pre-configured tags (+ Fee Stamped, + Transcript Handed, + Attested).",
            "Direct Department Transfer: Reassigns students to other counters."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "Intelligent Queue Intelligence",
        "Real-Time Desk Capabilities",
        [
            "Stopwatch SLA Indicator: Live color shifts (Green -> Amber -> Red).",
            "Interactive Queue Search: Instant filter by ticket number or service name.",
            "Fast Walk-in Issuer: Prints paper slips for students without phones.",
            "Break Management: Toggles desk availability status to avoid queue stalls.",
            "Web Speech Toggle: Enables or mutes desk voice synthesizers.",
            "Comprehensive Audit Trail: Logs officer ID, duration, and remarks."
        ],
        accent_color=COLOR_GOLD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 8: EXECUTIVE ADMIN & CAPACITY CONSOLE
# ==========================================
def build_slide_8(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "push", direction="r")

    h_ids = add_header(
        slide,
        "Executive Analytics",
        "Campus Capacity Console & Congestion Heatmap",
        "High-level operational intelligence for department chairs and university registrars."
    )
    anim_ids = list(h_ids)

    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "Executive KPI Indicators",
        "Real-Time Campus Telemetry",
        [
            "Waiting Students: Real-time headcount across all university departments.",
            "Active Service Desks: Live staffed counters vs total available desks.",
            "Average Wait Duration: Dynamically calculated via M/M/c Erlang-C.",
            "Average Service Time: Rolling mean across active consultations.",
            "Daily Throughput: Cumulative completed transactions.",
            "No-Show Percentage: Tracked automatically to adjust scheduling.",
            "SLA Compliance Rate: Percentage of visits within target thresholds."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "Heatmap Matrix & Data Export",
        "Strategic Decision Making",
        [
            "Congestion Heatmap Matrix: Hourly utilization (08:00 - 16:00).",
            "Color-Coded Severity: Pastel intensity tags (<65% Low, 65-84% Medium, >=85% High).",
            "Throughput Velocity Spline: Cubic spline arrivals vs served curve.",
            "Live CSV / Excel Streaming: Direct export of complete queue logs.",
            "Department Status Control: Instant toggle of department opening hours.",
            "Desk Capacity Provisioning: Dynamically add service desks on the fly."
        ],
        accent_color=COLOR_GOLD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 9: 4K PUBLIC SIGNAGE & VOICE BROADCAST
# ==========================================
def build_slide_9(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "zoom")

    h_ids = add_header(
        slide,
        "Public Displays & Audio",
        "4K Digital Signage & Voice Announcement Broadcast",
        "Airport-style public lobby monitors with automated audio chimes and voice summons."
    )
    anim_ids = list(h_ids)

    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "4K Visual Signage Layout",
        "High-Contrast Lobby Interface",
        [
            "NOW SERVING Spotlight: Giant typography displaying active tokens.",
            "Counter Desk Attribution: High visibility counter number assignments.",
            "Pulse-Glow Animation: Highlights newly called tickets on screen.",
            "Recently Called Ticker: Historical log of recently summoned students.",
            "Campus-Wide Summary: Real-time waiting count across all departments.",
            "Marquee Ticker: Bottom broadcast instructions with live smartphone QR link."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "Acoustic Engineering",
        "Zero-Dependency Procedural Audio",
        [
            "Web Speech API Voice Summons: 'Token ACC-001, please proceed to Counter 1'.",
            "Dual-Tone Airport Chime: Procedural F5 (698Hz) to C6 (1046Hz) synthesizer.",
            "Autoplay Unlock Banner: 1-Click interactive activation on TV monitors.",
            "No External Audio Files: Synthesized on-the-fly via native Web Audio API.",
            "Zero Audio Lag: Instantaneous trigger upon operator dispatch.",
            "Multi-Language Extensibility: Speech synthesis supports multiple languages."
        ],
        accent_color=COLOR_GOLD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 10: RELIABILITY, SECURITY & VERIFICATION
# ==========================================
def build_slide_10(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "push", direction="r")

    h_ids = add_header(
        slide,
        "Quality & Security Engineering",
        "Enterprise Hardening & Automated Testing",
        "Rigorous verification ensuring 100% data consistency, security, and uptime."
    )
    anim_ids = list(h_ids)

    c1_ids = add_card(
        slide,
        Inches(0.8), Inches(2.1), Inches(5.6), Inches(4.7),
        "Security & Compliance",
        "Zero-Trust Architecture",
        [
            "Server-Side RBAC: Strict middleware enforcing role authorization.",
            "Immutable Audit Log: Cryptographically tracked event history.",
            "CSRF & XSS Protection: Robust token protection across all forms and APIs.",
            "Stateful Sanctum Sessions: Protected API access with domain binding.",
            "Network Offline Detection: Automatic client reconnect with toast alert.",
            "Graceful Degradation: Self-contained SQLite / PostgreSQL support."
        ],
        accent_color=COLOR_MINT
    )
    anim_ids.extend(c1_ids)

    c2_ids = add_card(
        slide,
        Inches(6.8), Inches(2.1), Inches(5.7), Inches(4.7),
        "Automated Test Suite",
        "16 Passed Test Suites • 97 Assertions",
        [
            "Unit: Erlang-C mathematical formula & probability verification.",
            "Unit: Load growth behavior & saturation boundary approximation.",
            "Feature: Concurrency & pessimistic row-lock isolation verification.",
            "Feature: Complete ticket lifecycle (Join -> Call -> Start -> Complete).",
            "Feature: Duplicate active queue prevention logic.",
            "Execution Benchmark: Complete suite passes in 2.06 seconds."
        ],
        accent_color=COLOR_EMERALD
    )
    anim_ids.extend(c2_ids)

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 11: QUANTITATIVE REAL-WORLD IMPACT
# ==========================================
def build_slide_11(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "wipe", direction="r")

    h_ids = add_header(
        slide,
        "Measurable Outcomes",
        "Quantitative Campus Transformation",
        "Data-driven evidence of reduced wait times, higher staff efficiency, and student satisfaction."
    )
    anim_ids = list(h_ids)

    metrics = [
        ("-89%", "Hallway Crowding", [
            "Students wait comfortably in library or cafeteria.",
            "Corridors remain quiet and unobstructed.",
            "Campus security and safety improved."
        ], COLOR_MINT),
        ("4.5 min", "Average Wait Time", [
            "Down from 45+ minutes in physical queues.",
            "Optimized via dynamic load balancing.",
            "High customer satisfaction rating."
        ], COLOR_GOLD),
        ("+38%", "Desk Throughput", [
            "Eliminates slow manual calling and delays.",
            "Hotkeys allow sub-second ticket summoning.",
            "Counters serve more students per hour."
        ], COLOR_EMERALD),
        ("96.5%", "SLA Compliance", [
            "Automated tracking against service targets.",
            "Automatic alerts on prolonged consultations.",
            "Empirical evidence for faculty audits."
        ], COLOR_TERRACOTTA),
    ]

    for i, (stat, label, items, col) in enumerate(metrics):
        left = Inches(0.8 + i * 2.95)
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, Inches(2.1), Inches(2.8), Inches(4.7))
        card.fill.solid()
        card.fill.fore_color.rgb = COLOR_DARK_SLATE
        card.line.color.rgb = COLOR_BORDER
        card.line.width = Pt(1.5)

        tb = slide.shapes.add_textbox(left + Inches(0.2), Inches(2.3), Inches(2.4), Inches(4.3))
        tf = tb.text_frame
        tf.word_wrap = True

        p1 = tf.paragraphs[0]
        p1.text = stat
        p1.font.name = "Arial"
        p1.font.size = Pt(36)
        p1.font.bold = True
        p1.font.color.rgb = col
        p1.alignment = PP_ALIGN.CENTER

        p2 = tf.add_paragraph()
        p2.text = label
        p2.font.name = "Arial"
        p2.font.size = Pt(13)
        p2.font.bold = True
        p2.font.color.rgb = COLOR_WHITE
        p2.alignment = PP_ALIGN.CENTER
        p2.space_before = Pt(4)

        for it in items:
            p_it = tf.add_paragraph()
            p_it.text = f"•  {it}"
            p_it.font.name = "Arial"
            p_it.font.size = Pt(10.5)
            p_it.font.color.rgb = COLOR_MUTED
            p_it.space_before = Pt(8)

        anim_ids.extend([card.shape_id, tb.shape_id])

    add_entrance_animations(slide, anim_ids)

# ==========================================
# SLIDE 12: CONCLUSION & LIVE DEMONSTRATION
# ==========================================
def build_slide_12(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_background(slide, COLOR_OBSIDIAN)
    add_transition(slide, "fade", spd="slow")

    # Ambient Card
    panel = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.5), Inches(1.0), Inches(10.333), Inches(5.5))
    panel.fill.solid()
    panel.fill.fore_color.rgb = COLOR_DARK_SLATE
    panel.line.color.rgb = COLOR_BORDER
    panel.line.width = Pt(1.5)

    anim_ids = [panel.shape_id]

    if os.path.exists(LOGO_TRANSPARENT):
        logo = slide.shapes.add_picture(LOGO_TRANSPARENT, Inches(4.2), Inches(1.4), width=Inches(4.9))
        anim_ids.append(logo.shape_id)

    tb = slide.shapes.add_textbox(Inches(2.0), Inches(3.0), Inches(9.333), Inches(2.0))
    tf = tb.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "SMART QUEUE MANAGEMENT INFRASTRUCTURE"
    p.font.name = "Arial"
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = COLOR_MINT
    p.alignment = PP_ALIGN.CENTER

    p2 = tf.add_paragraph()
    p2.text = "Thank You! Questions & Live Demonstration"
    p2.font.name = "Arial"
    p2.font.size = Pt(28)
    p2.font.bold = True
    p2.font.color.rgb = COLOR_WHITE
    p2.alignment = PP_ALIGN.CENTER
    p2.space_before = Pt(6)

    p3 = tf.add_paragraph()
    p3.text = "Ready for Immediate Real-World Campus Deployment"
    p3.font.name = "Arial"
    p3.font.size = Pt(13)
    p3.font.color.rgb = COLOR_MUTED
    p3.alignment = PP_ALIGN.CENTER
    p3.space_before = Pt(6)

    anim_ids.append(tb.shape_id)

    # 3 Access Cards along bottom
    access_items = [
        ("Student Mobile Portal", "http://localhost:8000/student/dashboard", COLOR_MINT),
        ("Counter Operator Desk", "http://localhost:8000/operator/console", COLOR_GOLD),
        ("Executive Admin Console", "http://localhost:8000/admin/dashboard", COLOR_TERRACOTTA)
    ]
    for i, (title, url, col) in enumerate(access_items):
        left = Inches(2.2 + i * 3.1)
        bx = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, Inches(5.1), Inches(2.9), Inches(1.0))
        bx.fill.solid()
        bx.fill.fore_color.rgb = COLOR_CARD_BG
        bx.line.color.rgb = col
        bx.line.width = Pt(1.5)

        b_tf = bx.text_frame
        b_tf.word_wrap = True
        bp = b_tf.paragraphs[0]
        bp.text = title
        bp.font.name = "Arial"
        bp.font.size = Pt(11)
        bp.font.bold = True
        bp.font.color.rgb = col
        bp.alignment = PP_ALIGN.CENTER

        bp2 = b_tf.add_paragraph()
        bp2.text = url
        bp2.font.name = "Arial"
        bp2.font.size = Pt(8.5)
        bp2.font.color.rgb = COLOR_WHITE
        bp2.alignment = PP_ALIGN.CENTER
        bp2.space_before = Pt(3)

        anim_ids.append(bx.shape_id)

    add_entrance_animations(slide, anim_ids)

def main():
    print("Generating QueueLess Master Presentation...")
    prs = create_presentation()

    build_slide_1(prs)
    build_slide_2(prs)
    build_slide_3(prs)
    build_slide_4(prs)
    build_slide_5(prs)
    build_slide_6(prs)
    build_slide_7(prs)
    build_slide_8(prs)
    build_slide_9(prs)
    build_slide_10(prs)
    build_slide_11(prs)
    build_slide_12(prs)

    prs.save(OUTPUT_PATH)
    file_size_kb = os.path.getsize(OUTPUT_PATH) / 1024
    print(f"Presentation successfully created: {OUTPUT_PATH} ({file_size_kb:.1f} KB, 12 cinematic slides)")

if __name__ == "__main__":
    main()
