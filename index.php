<?php
require_once 'config.php';

// ---------------------------------------------------------------
// CONTACT FORM HANDLING
// ---------------------------------------------------------------
$contact_success = '';
$contact_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $contact_error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $contact_success = "Thank you! Your message has been sent.";
    }
}

// ---------------------------------------------------------------
// FETCH ALL DYNAMIC DATA (ordered by admin sort_order)
// ---------------------------------------------------------------
$services     = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, id ASC")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC")->fetchAll();
$stats        = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC, id ASC")->fetchAll();
$pricing      = $pdo->query("SELECT * FROM pricing ORDER BY sort_order ASC, id ASC")->fetchAll();

$heroVideo    = 'videos/hero-video.mp4';
$hasHeroVideo = file_exists(__DIR__ . '/' . $heroVideo);
$isLoggedIn   = isset($_SESSION['user_id']);

// Find the featured plan (for the "Most Popular" ribbon)
$featuredPlanId = 0;
foreach ($pricing as $p) {
    if (!empty($p['featured'])) { $featuredPlanId = $p['id']; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaal Solutions (Pty) Ltd — Innovative IT Solutions</title>
    <meta name="description" content="VaalTech Solutions delivers software engineering, networking, smart home automation, cybersecurity and cloud solutions for businesses and homes.">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ============================================================
       HOMEPAGE — PREMIUM PUBLIC SITE
       ============================================================ */
    :root {
        --hp-brand:    #0a84ff;
        --hp-brand-2:  #7c3aed;
        --hp-accent:   #ec4899;
        --hp-ink:      #0b1220;
        --hp-ink-2:    #131a2b;
        --hp-muted:    #64748b;
        --hp-line:     #e6e9ef;
        --hp-bg:       #f6f8fc;
        --hp-grad:     linear-gradient(135deg, #0a84ff, #7c3aed);
        --hp-grad-ink: linear-gradient(135deg, #0b1220 0%, #16213e 50%, #1e1b4b 100%);
        --hp-shadow:   0 12px 32px rgba(11,18,32,.08);
        --hp-shadow-lg:0 28px 70px rgba(11,18,32,.16);
        --hp-radius:   16px;
        --hp-radius-lg:24px;
        --hp-ease:     cubic-bezier(.22,.61,.36,1);
    }

    * { box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; }
    h1, h2, h3, h4 { font-family: 'Sora', 'Inter', sans-serif; letter-spacing: -0.02em; }

    section { padding: 100px 8%; position: relative; }

    /* ===== NAVBAR ===== */
    nav { padding: 14px 6%; }
    nav .nav-cta {
        padding: 10px 22px;
        background: var(--hp-grad);
        color: #fff !important;
        border-radius: 999px;
        font-weight: 600;
        font-size: .88rem;
        box-shadow: 0 8px 20px rgba(10,132,255,.3);
        transition: transform .3s var(--hp-ease), box-shadow .3s var(--hp-ease);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    nav .nav-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(10,132,255,.45);
    }

    /* ===== HERO ===== */
    .hero { min-height: 720px; padding-top: 80px; }
    .hero-content { max-width: 1000px; }
    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 18px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 999px;
        color: #fff;
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 26px;
        backdrop-filter: blur(10px);
    }
    .hero-eyebrow .dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #22d3ee;
        animation: pulseDot 2s infinite;
    }
    @keyframes pulseDot {
        0%   { box-shadow: 0 0 0 0 rgba(34,211,238,.6); }
        70%  { box-shadow: 0 0 0 12px rgba(34,211,238,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,211,238,0); }
    }
    .hero-content h1 {
        font-size: clamp(2.4rem, 5.5vw, 4.4rem);
        letter-spacing: -0.035em;
        font-weight: 800;
        line-height: 1.05;
    }
    .hero-content p.hero-sub {
        font-size: clamp(1rem, 1.7vw, 1.25rem);
        max-width: 680px;
        margin: 0 auto 42px;
    }
    .hero-trust {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 60px;
        flex-wrap: wrap;
        color: rgba(255,255,255,.7);
        font-size: .85rem;
        font-weight: 500;
    }
    .hero-trust-item { display: flex; align-items: center; gap: 8px; }
    .hero-trust-item i { color: #22d3ee; }

    /* ===== SECTION HEADINGS ===== */
    .section-kicker {
        display: block;
        text-align: center;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: var(--hp-brand);
        margin-bottom: 12px;
    }
    h2.section-title {
        text-align: center;
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        font-weight: 800;
        margin: 0 0 14px;
        letter-spacing: -0.03em;
    }
    .section-sub {
        text-align: center;
        max-width: 640px;
        margin: 0 auto 50px;
        color: var(--hp-muted);
        font-size: 1rem;
        line-height: 1.7;
    }

    /* ===== SERVICES ===== */
    .services { background: var(--hp-bg); }
    .services-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        max-width: 1300px;
        margin: 0 auto;
    }
    .service-card {
        background: #fff;
        padding: 36px 30px;
        border-radius: var(--hp-radius-lg);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
        transition: all .4s var(--hp-ease);
        position: relative;
        overflow: hidden;
        text-align: left;
    }
    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--hp-shadow-lg);
        border-color: rgba(10,132,255,.2);
    }
    .service-card .service-num {
        position: absolute;
        top: 24px; right: 26px;
        font-family: 'Sora', sans-serif;
        font-size: .8rem;
        font-weight: 800;
        color: rgba(10,132,255,.15);
        letter-spacing: .1em;
    }
    .service-card .service-icon {
        width: 68px; height: 68px;
        border-radius: 20px;
        background: var(--hp-grad);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 22px;
        box-shadow: 0 12px 28px rgba(10,132,255,.35);
        transition: transform .4s var(--hp-ease);
    }
    .service-card:hover .service-icon { transform: rotate(-6deg) scale(1.08); }
    .service-card h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0 0 10px;
        color: var(--hp-ink);
    }
    .service-card p {
        margin: 0;
        color: var(--hp-muted);
        font-size: .92rem;
        line-height: 1.7;
    }
    .service-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        width: 100%;
        height: 4px;
        background: var(--hp-grad);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .5s var(--hp-ease);
    }
    .service-card:hover::after { transform: scaleX(1); }

    /* ===== WHY US ===== */
    .why-us { background: linear-gradient(180deg, #fff 0%, var(--hp-bg) 100%); }
    .why-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 22px;
        max-width: 1300px;
        margin: 0 auto;
    }
    .why-card {
        background: #fff;
        padding: 32px 26px;
        border-radius: var(--hp-radius-lg);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
        transition: all .35s var(--hp-ease);
        position: relative;
        overflow: hidden;
    }
    .why-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--hp-shadow-lg);
        border-color: rgba(10,132,255,.25);
    }
    .why-card .why-num {
        font-family: 'Sora', sans-serif;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .15em;
        color: var(--hp-brand);
        margin-bottom: 16px;
        display: block;
    }
    .why-card h3 { font-size: 1.1rem; font-weight: 700; margin: 0 0 10px; }
    .why-card p { color: var(--hp-muted); font-size: .92rem; line-height: 1.7; margin: 0; }
    .why-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 4px;
        height: 0;
        background: var(--hp-grad);
        transition: height .4s var(--hp-ease);
        border-radius: 0 4px 4px 0;
    }
    .why-card:hover::before { height: 60%; }

    /* ===== PROJECTS ===== */
    .portfolio { background: #fff; }
    .portfolio-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 26px;
        max-width: 1300px;
        margin: 0 auto;
    }
    .portfolio-card {
        position: relative;
        overflow: hidden;
        border-radius: var(--hp-radius-lg);
        box-shadow: var(--hp-shadow);
        aspect-ratio: 4/3;
        cursor: pointer;
        transition: all .4s var(--hp-ease);
    }
    .portfolio-card:hover { box-shadow: var(--hp-shadow-lg); transform: translateY(-6px); }
    .portfolio-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .6s var(--hp-ease);
    }
    .portfolio-card:hover img { transform: scale(1.1); }
    .portfolio-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 30%, rgba(11,18,32,.92));
        z-index: 1;
        transition: background .4s var(--hp-ease);
    }
    .portfolio-overlay {
        position: absolute;
        inset: auto 0 0 0;
        padding: 30px 28px;
        z-index: 2;
        color: #fff;
        transform: translateY(0);
        transition: transform .4s var(--hp-ease);
    }
    .portfolio-card:hover .portfolio-overlay { transform: translateY(-6px); }
    .portfolio-overlay h3 {
        margin: 0 0 6px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #fff;
    }
    .portfolio-overlay .view-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .78rem;
        font-weight: 600;
        color: #22d3ee;
        opacity: 0;
        transform: translateY(8px);
        transition: all .35s var(--hp-ease);
    }
    .portfolio-card:hover .view-tag {
        opacity: 1;
        transform: translateY(0);
    }

    /* ===== PROCESS ===== */
    .process {
        background: var(--hp-ink);
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .process::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 20%, rgba(10,132,255,.25), transparent 55%),
                    radial-gradient(circle at 80% 80%, rgba(124,58,237,.25), transparent 55%);
        pointer-events: none;
    }
    .process h2.section-title { color: #fff; position: relative; }
    .process .section-sub { color: rgba(255,255,255,.6); position: relative; }
    .process .section-kicker { color: #22d3ee; position: relative; }
    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 26px;
        max-width: 1300px;
        margin: 0 auto;
        position: relative;
    }
    .process-step {
        padding: 30px 26px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: var(--hp-radius);
        backdrop-filter: blur(20px);
        transition: all .35s var(--hp-ease);
    }
    .process-step:hover {
        background: rgba(255,255,255,.07);
        transform: translateY(-6px);
        border-color: rgba(79,195,247,.35);
    }
    .process-step .step-circle {
        width: 48px; height: 48px;
        border-radius: 14px;
        background: var(--hp-grad);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 20px;
        box-shadow: 0 12px 26px rgba(10,132,255,.35);
    }
    .process-step h3 { color: #fff; font-size: 1rem; font-weight: 700; margin: 0 0 10px; }
    .process-step p { color: rgba(255,255,255,.65); font-size: .88rem; line-height: 1.7; margin: 0; }

    /* ===== PRICING ===== */
    .pricing { background: var(--hp-bg); }
    .pricing-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 26px;
        max-width: 1200px;
        margin: 0 auto;
        align-items: stretch;
    }
    .price-card {
        background: #fff;
        padding: 40px 32px;
        border-radius: var(--hp-radius-lg);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
        transition: all .4s var(--hp-ease);
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .price-card:hover { transform: translateY(-8px); box-shadow: var(--hp-shadow-lg); }

    .price-card.featured {
        background: var(--hp-grad-ink);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 30px 70px rgba(11,18,32,.35);
        transform: scale(1.04);
    }
    .price-card.featured:hover { transform: scale(1.06) translateY(-8px); }
    .price-card.featured::before {
        content: 'MOST POPULAR';
        position: absolute;
        top: 20px; right: -36px;
        background: var(--hp-grad);
        color: #fff;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .18em;
        padding: 6px 42px;
        transform: rotate(45deg);
        box-shadow: 0 6px 20px rgba(10,132,255,.4);
    }
    .price-card h3 {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .18em;
        color: var(--hp-muted);
        margin: 0 0 8px;
    }
    .price-card.featured h3 { color: rgba(255,255,255,.7); }

    .price-card h1 {
        font-family: 'Sora', sans-serif;
        font-size: 2.8rem;
        font-weight: 800;
        letter-spacing: -0.035em;
        margin: 0 0 6px;
        background: var(--hp-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
    }
    .price-card.featured h1 {
        background: linear-gradient(135deg, #4fc3f7, #a78bfa);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .price-card .price-note {
        font-size: .8rem;
        color: var(--hp-muted);
        margin-bottom: 26px;
        display: block;
    }
    .price-card.featured .price-note { color: rgba(255,255,255,.5); }

    .price-card ul {
        list-style: none;
        padding: 0;
        margin: 0 0 30px;
        flex: 1;
    }
    .price-card ul li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        font-size: .92rem;
        color: #334155;
        border-bottom: 1px dashed rgba(11,18,32,.06);
    }
    .price-card.featured ul li {
        color: rgba(255,255,255,.85);
        border-bottom-color: rgba(255,255,255,.1);
    }
    .price-card ul li:last-child { border-bottom: none; }
    .price-card ul li i {
        color: #10b981;
        font-size: .85rem;
        margin-top: 4px;
        flex-shrink: 0;
    }
    .price-card.featured ul li i { color: #22d3ee; }

    .price-card button {
        width: 100%;
        padding: 15px 24px;
        border: none;
        border-radius: 999px;
        background: var(--hp-grad);
        color: #fff;
        font-family: inherit;
        font-weight: 700;
        font-size: .95rem;
        cursor: pointer;
        transition: all .35s var(--hp-ease);
        box-shadow: 0 12px 28px rgba(10,132,255,.3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .price-card button:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(10,132,255,.45); }
    .price-card.featured button {
        background: #fff;
        color: var(--hp-ink);
        box-shadow: 0 18px 40px rgba(255,255,255,.15);
    }
    .price-card.featured button:hover { background: #22d3ee; color: #fff; }

    /* ===== ABOUT ===== */
    .about { background: #fff; }
    .about-text {
        max-width: 880px;
        margin: 0 auto;
        text-align: center;
    }
    .about-text p {
        margin: 16px 0;
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.85;
    }

    /* ===== TESTIMONIALS ===== */
    .testimonials {
        background: var(--hp-grad-ink);
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .testimonials::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 20%, rgba(10,132,255,.25), transparent 55%),
                    radial-gradient(circle at 80% 80%, rgba(124,58,237,.25), transparent 55%);
        pointer-events: none;
    }
    .testimonials h2.section-title { color: #fff; position: relative; }
    .testimonials .section-kicker { color: #22d3ee; position: relative; }
    .testimonial-slider {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        min-height: 200px;
        position: relative;
        padding: 50px 40px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: var(--hp-radius-lg);
        backdrop-filter: blur(20px);
    }
    .testimonial-slider::before {
        content: '"';
        position: absolute;
        top: -40px; left: 40px;
        font-family: 'Sora', serif;
        font-size: 8rem;
        color: #22d3ee;
        opacity: .25;
        line-height: 1;
    }
    .testimonial { display: none; }
    .testimonial.active { display: block; animation: fadeUp .6s var(--hp-ease); }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .testimonial p {
        font-size: 1.2rem;
        font-style: italic;
        line-height: 1.7;
        color: rgba(255,255,255,.92);
        margin: 0 0 22px;
        font-weight: 300;
    }
    .testimonial h4 {
        color: #22d3ee;
        font-weight: 700;
        font-size: .82rem;
        letter-spacing: .15em;
        text-transform: uppercase;
        margin: 0;
    }
    .testimonial-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 30px;
    }
    .testimonial-dots button {
        width: 8px; height: 8px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,.25);
        cursor: pointer;
        padding: 0;
        transition: all .3s;
    }
    .testimonial-dots button.active {
        background: #22d3ee;
        width: 24px;
        border-radius: 4px;
    }

    /* ===== STATS ===== */
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0;
        background: #fff;
        padding: 80px 8%;
        border-top: 1px solid var(--hp-line);
        border-bottom: 1px solid var(--hp-line);
    }
    .stat-box {
        padding: 26px 20px;
        text-align: center;
        position: relative;
        transition: transform .35s var(--hp-ease);
    }
    .stat-box:hover { transform: translateY(-6px); }
    .stat-box:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0; top: 25%;
        height: 50%;
        width: 1px;
        background: linear-gradient(180deg, transparent, var(--hp-line), transparent);
    }
    .stat-box h2 {
        font-family: 'Sora', sans-serif;
        font-size: clamp(2.4rem, 4vw, 3.2rem);
        font-weight: 800;
        letter-spacing: -0.035em;
        margin: 0 0 8px;
        background: var(--hp-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .stat-box p {
        margin: 0;
        color: var(--hp-muted);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    /* ===== CTA BANNER ===== */
    .cta-banner { padding: 0; background: var(--hp-bg); }
    .cta-inner {
        margin: 0 auto;
        max-width: 1100px;
        background: var(--hp-grad);
        border-radius: var(--hp-radius-lg);
        padding: 70px 60px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        flex-wrap: wrap;
        position: relative;
        overflow: hidden;
        box-shadow: 0 40px 80px rgba(10,132,255,.35);
    }
    .cta-inner::before {
        content: '';
        position: absolute;
        top: -50%; right: -10%;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.2), transparent 60%);
        pointer-events: none;
    }
    .cta-text { position: relative; z-index: 1; flex: 1; min-width: 280px; }
    .cta-text h2 {
        color: #fff;
        text-align: left;
        margin: 0 0 12px;
        font-size: clamp(1.6rem, 3vw, 2.4rem);
        font-weight: 800;
        letter-spacing: -0.025em;
    }
    .cta-text p { color: rgba(255,255,255,.9); margin: 0; font-size: 1.02rem; max-width: 520px; }
    .cta-actions { position: relative; z-index: 1; display: flex; gap: 14px; flex-wrap: wrap; }
    .cta-btn {
        padding: 16px 32px;
        border-radius: 999px;
        font-weight: 700;
        font-size: .98rem;
        transition: all .3s var(--hp-ease);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }
    .cta-btn-white { background: #fff; color: var(--hp-ink); box-shadow: 0 18px 40px rgba(0,0,0,.15); }
    .cta-btn-white:hover { transform: translateY(-3px); box-shadow: 0 24px 50px rgba(0,0,0,.25); color: var(--hp-ink); }
    .cta-btn-outline {
        background: rgba(255,255,255,.1);
        color: #fff;
        border: 1.5px solid rgba(255,255,255,.55);
        backdrop-filter: blur(10px);
    }
    .cta-btn-outline:hover { background: rgba(255,255,255,.2); transform: translateY(-3px); color: #fff; }

    /* ===== CONTACT ===== */
    .contact { background: #fff; }
    .contact-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 22px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .contact-box {
        background: #fff;
        padding: 34px 24px;
        border-radius: var(--hp-radius-lg);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
        text-align: center;
        transition: all .35s var(--hp-ease);
    }
    .contact-box:hover { transform: translateY(-6px); box-shadow: var(--hp-shadow-lg); border-color: rgba(10,132,255,.2); }
    .contact-box i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px; height: 60px;
        border-radius: 18px;
        background: var(--hp-grad);
        color: #fff;
        font-size: 1.4rem;
        margin-bottom: 16px;
        box-shadow: 0 12px 26px rgba(10,132,255,.3);
    }
    .contact-box p { margin: 0; color: #334155; font-weight: 500; }

    .contact-form {
        max-width: 720px;
        margin: 0 auto;
        padding: 60px 8%;
    }
    .contact-form h2 {
        text-align: center;
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        margin: 0 0 32px;
    }
    .contact-form form {
        display: flex;
        flex-direction: column;
        gap: 14px;
        background: #fff;
        padding: 40px 36px;
        border-radius: var(--hp-radius-lg);
        box-shadow: var(--hp-shadow);
        border: 1px solid var(--hp-line);
        margin-bottom: 30px;
    }
    .contact-form input,
    .contact-form textarea {
        padding: 14px 18px;
        border: 1.5px solid var(--hp-line);
        border-radius: 12px;
        font-size: .98rem;
        font-family: inherit;
        outline: none;
        transition: all .3s var(--hp-ease);
        background: #fafbfd;
        color: var(--hp-ink);
    }
    .contact-form input:focus,
    .contact-form textarea:focus {
        border-color: var(--hp-brand);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(10,132,255,.12);
    }
    .contact-form textarea { min-height: 140px; resize: vertical; font-family: inherit; }
    .contact-form button {
        padding: 15px 32px;
        border: none;
        background: var(--hp-grad);
        color: #fff;
        border-radius: 999px;
        font-weight: 700;
        font-size: .98rem;
        cursor: pointer;
        transition: all .35s var(--hp-ease);
        width: fit-content;
        align-self: flex-start;
        box-shadow: 0 14px 30px rgba(10,132,255,.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .contact-form button:hover { transform: translateY(-3px); box-shadow: 0 22px 45px rgba(10,132,255,.45); }

    .social-links { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .social-links a {
        width: 48px; height: 48px;
        border-radius: 14px;
        background: #fff;
        color: var(--hp-ink);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        box-shadow: var(--hp-shadow);
        transition: all .35s var(--hp-ease);
        border: 1px solid var(--hp-line);
    }
    .social-links a:hover {
        background: var(--hp-grad);
        color: #fff;
        transform: translateY(-5px) rotate(-4deg);
        box-shadow: 0 18px 36px rgba(10,132,255,.4);
        border-color: transparent;
    }

    .error, .success {
        text-align: left;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: .9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

    /* ===== FOOTER ===== */
    .site-footer {
        background: var(--hp-ink);
        color: rgba(255,255,255,.7);
        padding: 70px 8% 30px;
        font-size: .9rem;
        position: relative;
        overflow: hidden;
    }
    .site-footer::before {
        content: '';
        position: absolute;
        top: 0; left: 50%;
        transform: translateX(-50%);
        width: 160px; height: 3px;
        background: var(--hp-grad);
        border-radius: 0 0 4px 4px;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr 1fr 1.2fr;
        gap: 50px;
        max-width: 1300px;
        margin: 0 auto 50px;
    }
    .footer-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .footer-brand img { height: 44px; border-radius: 10px; background: #fff; padding: 4px; }
    .footer-brand strong {
        font-family: 'Sora', sans-serif;
        font-size: 1.15rem;
        color: #fff;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .footer-col p { line-height: 1.75; margin: 0 0 20px; color: rgba(255,255,255,.6); }
    .footer-col h4 {
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .15em;
        text-transform: uppercase;
        margin-bottom: 22px;
        font-family: 'Inter', sans-serif;
    }
    .footer-col ul { list-style: none; padding: 0; margin: 0; }
    .footer-col ul li { margin-bottom: 12px; }
    .footer-col ul a {
        color: rgba(255,255,255,.65);
        transition: all .25s var(--hp-ease);
        font-size: .9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .footer-col ul a:hover { color: #22d3ee; transform: translateX(3px); }
    .footer-col ul a i { font-size: .7rem; opacity: .5; }
    .footer-social { display: flex; gap: 10px; margin-top: 18px; }
    .footer-social a {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: rgba(255,255,255,.06);
        color: rgba(255,255,255,.75);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .3s var(--hp-ease);
        border: 1px solid rgba(255,255,255,.08);
    }
    .footer-social a:hover {
        background: var(--hp-grad);
        color: #fff;
        transform: translateY(-4px) rotate(-4deg);
        box-shadow: 0 12px 26px rgba(10,132,255,.4);
        border-color: transparent;
    }
    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid rgba(255,255,255,.08);
        max-width: 1300px;
        margin: 0 auto;
        color: rgba(255,255,255,.5);
        font-size: .82rem;
    }
    .footer-bottom span { color: #22d3ee; }

    /* ===== SCROLL TO TOP ===== */
    .to-top {
        position: fixed;
        bottom: 28px; right: 28px;
        width: 48px; height: 48px;
        border-radius: 14px;
        background: var(--hp-grad);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        box-shadow: 0 14px 30px rgba(10,132,255,.4);
        opacity: 0;
        pointer-events: none;
        transition: all .35s var(--hp-ease);
        z-index: 900;
    }
    .to-top.show { opacity: 1; pointer-events: auto; }
    .to-top:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(10,132,255,.55); }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .footer-grid { grid-template-columns: 1fr 1fr; gap: 40px; }
        .cta-inner { padding: 50px 40px; }
        section { padding: 80px 6%; }
    }
    @media (max-width: 768px) {
        section { padding: 70px 5%; }
        nav .nav-cta { display: none; }
        .hero { min-height: 640px; }
        .hero-trust { gap: 18px; font-size: .78rem; }
        .cta-inner { flex-direction: column; text-align: center; padding: 44px 26px; }
        .cta-text { text-align: center; }
        .cta-text h2, .cta-text p { text-align: center; margin-left: auto; margin-right: auto; }
        .cta-actions { justify-content: center; width: 100%; }
        .cta-btn { flex: 1; justify-content: center; }
        .site-footer { padding: 50px 6% 24px; }
        .footer-grid { grid-template-columns: 1fr; gap: 34px; }
        .price-card.featured { transform: none; }
        .price-card.featured:hover { transform: translateY(-8px); }
    }
    @media (max-width: 480px) {
        .hero-trust { display: none; }
        .cta-actions { flex-direction: column; }
        .stat-box::after { display: none !important; }
        .contact-form form { padding: 28px 22px; }
    }
    </style>
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav>
    <div class="logo">
        <img src="images/log1.jpg" alt="Vaal Solutions logo">
    </div>
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#portfolio">Projects</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul>
    <?php if ($isLoggedIn): ?>
        <a href="admin.php" class="nav-cta"><i class="fa fa-gauge-high"></i> Dashboard</a>
    <?php else: ?>
        <a href="login.php" class="nav-cta"><i class="fa fa-arrow-right-to-bracket"></i> Get Started</a>
    <?php endif; ?>
    <div class="menu-btn"><i class="fa fa-bars"></i></div>
</nav>

<!-- ================= HERO ================= -->
<section class="hero" id="home">
    <?php if ($hasHeroVideo): ?>
        <video autoplay muted loop playsinline class="hero-video">
            <source src="<?= htmlspecialchars($heroVideo) ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="overlay"></div>

    <div class="hero-content">
        <span class="hero-eyebrow">
            <span class="dot"></span>
            Trusted IT Partner in South Africa
        </span>

        <h1>We Build <span class="typing"></span></h1>

        <p class="hero-sub">
            Innovative IT solutions for businesses, homes and organizations —
            from software engineering to smart automation and cybersecurity.
        </p>

        <div class="hero-buttons">
            <a class="primary-btn" href="#pricing">
                <i class="fa fa-rocket"></i> View Pricing
            </a>
            <a class="secondary-btn" href="#services">
                Explore Services <i class="fa fa-arrow-down"></i>
            </a>
        </div>

        <div class="hero-trust">
            <div class="hero-trust-item"><i class="fa fa-check-circle"></i> 24/7 Support</div>
            <div class="hero-trust-item"><i class="fa fa-shield-halved"></i> Certified Engineers</div>
            <div class="hero-trust-item"><i class="fa fa-bolt"></i> Fast Turnaround</div>
        </div>
    </div>
</section>

<!-- ================= SERVICES ================= -->
<?php if (!empty($services)): ?>
<section class="services" id="services">
    <span class="section-kicker">What We Do</span>
    <h2 class="section-title">Our Services</h2>
    <p class="section-sub">End-to-end technology solutions tailored to your business — built, delivered and supported by our in-house team.</p>

    <div class="services-container">
        <?php foreach ($services as $i => $s): ?>
        <div class="service-card">
            <span class="service-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
            <div class="service-icon">
                <i class="<?= htmlspecialchars($s['icon'] ?: 'fa-solid fa-cog') ?>"></i>
            </div>
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <p><?= htmlspecialchars($s['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ================= WHY US ================= -->
<section class="why-us" id="why-us">
    <span class="section-kicker">Why Choose Us</span>
    <h2 class="section-title">Built On Trust &amp; Innovation</h2>
    <p class="section-sub">Four reasons our clients keep coming back — and why we're the partner of choice across Gauteng.</p>

    <div class="why-grid">
        <div class="why-card">
            <span class="why-num">01</span>
            <h3><i class="fa fa-medal" style="color:#0a84ff;margin-right:8px;"></i> Expert Team</h3>
            <p>Certified engineers and developers with years of hands-on experience delivering real business value.</p>
        </div>
        <div class="why-card">
            <span class="why-num">02</span>
            <h3><i class="fa fa-bolt" style="color:#f59e0b;margin-right:8px;"></i> Fast Delivery</h3>
            <p>Agile workflows that ship projects on time — without compromising on quality or security.</p>
        </div>
        <div class="why-card">
            <span class="why-num">03</span>
            <h3><i class="fa fa-shield-halved" style="color:#10b981;margin-right:8px;"></i> Secure By Design</h3>
            <p>Every solution is hardened from day one — encryption, audits and continuous monitoring included.</p>
        </div>
        <div class="why-card">
            <span class="why-num">04</span>
            <h3><i class="fa fa-headset" style="color:#7c3aed;margin-right:8px;"></i> Lifetime Support</h3>
            <p>Round-the-clock assistance, remote troubleshooting and proactive maintenance for all clients.</p>
        </div>
    </div>
</section>

<!-- ================= PROJECTS ================= -->
<?php if (!empty($projects)): ?>
<section class="portfolio" id="portfolio">
    <span class="section-kicker">Recent Work</span>
    <h2 class="section-title">Our Projects</h2>
    <p class="section-sub">A snapshot of the systems, platforms and infrastructure we've delivered for clients across the region.</p>

    <div class="portfolio-container">
        <?php foreach ($projects as $p): ?>
        <div class="portfolio-card">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
            <div class="portfolio-overlay">
                <h3><?= htmlspecialchars($p['title']) ?></h3>
                <span class="view-tag"><i class="fa fa-arrow-right"></i> View case study</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ================= PROCESS ================= -->
<section class="process">
    <span class="section-kicker">How We Work</span>
    <h2 class="section-title">Our Process</h2>
    <p class="section-sub">A proven four-step approach that keeps you informed from first call to final deployment.</p>

    <div class="process-steps">
        <div class="process-step">
            <div class="step-circle">1</div>
            <h3>Discover</h3>
            <p>We listen to your goals, audit your current systems and map out the best path forward.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">2</div>
            <h3>Design</h3>
            <p>Our team architects a tailored solution — scalable, secure and aligned with your budget.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">3</div>
            <h3>Deploy</h3>
            <p>We build, test and roll out with minimal downtime — keeping you informed every step.</p>
        </div>
        <div class="process-step">
            <div class="step-circle">4</div>
            <h3>Support</h3>
            <p>Continuous monitoring, updates and 24/7 helpdesk so your systems never miss a beat.</p>
        </div>
    </div>
</section>

<!-- ================= PRICING ================= -->
<?php if (!empty($pricing)): ?>
<section class="pricing" id="pricing">
    <span class="section-kicker">Simple Pricing</span>
    <h2 class="section-title">Choose Your Plan</h2>
    <p class="section-sub">Transparent packages designed to fit every stage of your business. No hidden fees — ever.</p>

    <div class="pricing-container">
        <?php foreach ($pricing as $plan): ?>
        <div class="price-card <?= (!empty($plan['featured'])) ? 'featured' : '' ?>">
            <h3><?= htmlspecialchars($plan['name']) ?></h3>
            <h1><?= htmlspecialchars($plan['price']) ?></h1>
            <span class="price-note">
                <?= (!empty($plan['featured'])) ? 'Best value for growing teams' : 'Great for getting started' ?>
            </span>

            <ul>
                <?php
                $lines = preg_split('/\r\n|\r|\n/', $plan['features']);
                foreach ($lines as $f):
                    if (trim($f) === '') continue;
                ?>
                    <li><i class="fa fa-circle-check"></i> <span><?= htmlspecialchars(trim($f)) ?></span></li>
                <?php endforeach; ?>
            </ul>

            <button type="button">
                <i class="fa fa-rocket"></i> Choose <?= htmlspecialchars($plan['name']) ?>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ================= ABOUT ================= -->
<section class="about" id="about">
    <span class="section-kicker">Who We Are</span>
    <h2 class="section-title">About Us</h2>
    <div class="about-text">
        <p>VaalTech Solutions (Pty) Ltd delivers innovative IT solutions designed for businesses, homes and organizations.</p>
        <p>We specialize in software engineering, networking infrastructure, smart technology, cloud systems and digital transformation.</p>
    </div>
</section>

<!-- ================= TESTIMONIALS ================= -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials">
    <span class="section-kicker">Testimonials</span>
    <h2 class="section-title">What Clients Say</h2>
    <p class="section-sub">Real feedback from the businesses and families we've served.</p>

    <div class="testimonial-slider" id="testimonialSlider">
        <?php foreach ($testimonials as $i => $t): ?>
        <div class="testimonial <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
            <p>"<?= htmlspecialchars($t['quote']) ?>"</p>
            <h4>- <?= htmlspecialchars($t['author']) ?></h4>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (count($testimonials) > 1): ?>
    <div class="testimonial-dots" id="testimonialDots">
        <?php foreach ($testimonials as $i => $t): ?>
            <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="Go to testimonial <?= $i+1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ================= STATS ================= -->
<?php if (!empty($stats)): ?>
<section class="stats">
    <?php foreach ($stats as $s): ?>
    <div class="stat-box">
        <h2 class="counter" data-target="<?= (int)$s['target'] ?>">0</h2>
        <p><?= htmlspecialchars($s['label']) ?></p>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ================= CTA BANNER ================= -->
<section class="cta-banner">
    <div class="cta-inner">
        <div class="cta-text">
            <h2>Ready to transform your business?</h2>
            <p>Let's talk about your project. Free consultation, no obligation — just straight answers.</p>
        </div>
        <div class="cta-actions">
            <a href="#contact" class="cta-btn cta-btn-white"><i class="fa fa-paper-plane"></i> Get in Touch</a>
            <a href="login.php" class="cta-btn cta-btn-outline"><i class="fa fa-user-plus"></i> Create Account</a>
        </div>
    </div>
</section>

<!-- ================= CONTACT ================= -->
<section class="contact" id="contact">
    <span class="section-kicker">Get In Touch</span>
    <h2 class="section-title">Contact Us</h2>
    <p class="section-sub">Have a project in mind? Send us a message and we'll get back to you within 24 hours.</p>

    <div class="contact-container">
        <div class="contact-box"><i class="fa-solid fa-phone"></i><p>+27 12 345 6789</p></div>
        <div class="contact-box"><i class="fa-solid fa-envelope"></i><p>info@vaaltech.co.za</p></div>
        <div class="contact-box"><i class="fa-solid fa-location-dot"></i><p>Gauteng, Vanderbijlpark, South Africa</p></div>
    </div>
</section>

<section class="contact-form">
    <h2>Send us a message</h2>

    <?php if ($contact_success): ?>
        <p class="success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($contact_success) ?></p>
    <?php endif; ?>

    <?php if ($contact_error): ?>
        <p class="error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($contact_error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text"  name="name"    placeholder="Your Name"    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        <input type="email" name="email"   placeholder="Your Email"   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <textarea           name="message" placeholder="Tell us about your project..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <button type="submit" name="contact_submit"><i class="fa fa-paper-plane"></i> Send Message</button>
    </form>

    <div class="social-links" aria-label="Social media and email">
        <a href="https://wa.me/27123456789" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="mailto:info@vaaltech.co.za" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <div class="footer-brand">
                <img src="images/log1.jpg" alt="Vaal Solutions logo">
                <strong>Vaal Solutions</strong>
            </div>
            <p>Innovative IT solutions for businesses, homes and organizations across South Africa. Software, networks, security and smart technology — all under one roof.</p>
            <div class="footer-social">
                <a href="https://wa.me/27123456789" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Services</h4>
            <ul>
                <?php foreach (array_slice($services, 0, 5) as $s): ?>
                    <li><a href="#services"><i class="fa fa-chevron-right"></i> <?= htmlspecialchars($s['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#about"><i class="fa fa-chevron-right"></i> About Us</a></li>
                <li><a href="#portfolio"><i class="fa fa-chevron-right"></i> Projects</a></li>
                <li><a href="#pricing"><i class="fa fa-chevron-right"></i> Pricing</a></li>
                <li><a href="#contact"><i class="fa fa-chevron-right"></i> Contact</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Get In Touch</h4>
            <ul>
                <li><a href="tel:+27123456789"><i class="fa fa-phone"></i> +27 12 345 6789</a></li>
                <li><a href="mailto:info@vaaltech.co.za"><i class="fa fa-envelope"></i> info@vaaltech.co.za</a></li>
                <li><a href="#contact"><i class="fa fa-location-dot"></i> Vanderbijlpark, Gauteng</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        © <?= date('Y') ?> Vaal Solutions (Pty) Ltd. All Rights Reserved. Crafted with <span>♥</span> in South Africa.
    </div>
</footer>

<button class="to-top" id="toTop" aria-label="Back to top"><i class="fa fa-arrow-up"></i></button>

<script src="script.js"></script>
<script>
// ---- Back to top ----
const toTop = document.getElementById('toTop');
window.addEventListener('scroll', () => toTop.classList.toggle('show', window.scrollY > 500));
toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ---- Testimonial slider with dots ----
(function () {
    const slides = document.querySelectorAll('.testimonial');
    const dots   = document.querySelectorAll('#testimonialDots button');
    if (slides.length <= 1) return;

    let current = 0;

    function show(index) {
        slides.forEach((s, i) => s.classList.toggle('active', i === index));
        dots.forEach((d, i) => d.classList.toggle('active', i === index));
        current = index;
    }

    dots.forEach(dot => {
        dot.addEventListener('click', () => show(parseInt(dot.dataset.index)));
    });

    let autoplay = setInterval(() => show((current + 1) % slides.length), 6000);

    const slider = document.getElementById('testimonialSlider');
    slider.addEventListener('mouseenter', () => clearInterval(autoplay));
    slider.addEventListener('mouseleave', () => {
        autoplay = setInterval(() => show((current + 1) % slides.length), 6000);
    });
})();
</script>
</body>
</html>