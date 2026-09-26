<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($activeKejuaraan['nama'] ?? 'Kejuaraan Atletik') ?> — Informasi Peserta & Jadwal Lomba</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter for Sports & Athletic Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700;1,800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dataTables.bootstrap5.min.css') ?>">
    
    <!-- Early Theme Initializer (Smooth Dark & Light Mode) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('kejurwil_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();

        function toggleKejurwilTheme(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            const html = document.documentElement;
            const isDark = html.getAttribute('data-bs-theme') === 'dark' || html.classList.contains('dark-theme');
            const next = isDark ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', next);
            html.classList.toggle('dark-theme', next === 'dark');
            if (document.body) {
                document.body.classList.toggle('dark-theme', next === 'dark');
            }
            try {
                localStorage.setItem('kejurwil_theme', next);
            } catch(err) {}

            document.querySelectorAll('.theme-icon-dark').forEach(function(el) {
                el.style.display = next === 'dark' ? 'inline-block' : 'none';
            });
            document.querySelectorAll('.theme-icon-light').forEach(function(el) {
                el.style.display = next === 'dark' ? 'none' : 'inline-block';
            });
            const btn = document.getElementById('themeToggleBtnHome');
            if (btn) btn.setAttribute('title', next === 'dark' ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap');
            return false;
        }
        window.toggleKejurwilTheme = toggleKejurwilTheme;
    </script>
    
    <style>
        /* ==========================================================================
           ATHLETIC CHAMPIONSHIP DESIGN SYSTEM (PASI TRACK & FIELD)
           ========================================================================== */
        :root, [data-bs-theme="light"] {
            /* Athletic Palette: Dynamic Crimson, Olympic Gold, Stadium Navy */
            --athletic-red: #d90429;
            --athletic-red-hover: #b00320;
            --athletic-red-light: rgba(217, 4, 41, 0.08);
            --athletic-navy: #0f172a;
            --athletic-gold: #f59e0b;
            --athletic-gold-light: rgba(245, 158, 11, 0.12);
            --athletic-track: #ef233c;

            /* Canvas & Glass tokens */
            --bg-canvas: #f8fafc;
            --bg-body-gradient: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
            --nav-bg: rgba(255, 255, 255, 0.82);
            --nav-border: rgba(15, 23, 42, 0.08);
            --card-bg: #ffffff;
            --card-border: rgba(15, 23, 42, 0.08);
            --card-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            --card-hover-shadow: 0 12px 30px -4px rgba(217, 4, 41, 0.12);
            
            /* Typography */
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            
            /* Table tokens */
            --th-bg: #f1f5f9;
            --th-color: #475569;
            --td-border: rgba(15, 23, 42, 0.06);
            --tr-hover: rgba(217, 4, 41, 0.03);

            /* Bib Token */
            --bib-bg: #ffffff;
            --bib-border: #d90429;
            --bib-color: #0f172a;
            --bib-shadow: 0 2px 8px rgba(217, 4, 41, 0.18);

            /* Bootstrap Overrides */
            --bs-body-bg: #f8fafc !important;
            --bs-body-color: #334155 !important;
        }

        [data-bs-theme="dark"], body.dark-theme {
            /* Dark Stadium Night Palette */
            --athletic-red: #ef233c;
            --athletic-red-hover: #ff4d6d;
            --athletic-red-light: rgba(239, 35, 60, 0.15);
            --athletic-navy: #090d16;
            --athletic-gold: #fbbf24;
            --athletic-gold-light: rgba(251, 191, 36, 0.15);
            --athletic-track: #d90429;

            /* Canvas & Glass tokens */
            --bg-canvas: #090d16;
            --bg-body-gradient: radial-gradient(circle at 50% 0%, #151d30 0%, #090d16 80%);
            --nav-bg: rgba(15, 23, 42, 0.78);
            --nav-border: rgba(255, 255, 255, 0.1);
            --card-bg: rgba(18, 26, 44, 0.85);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            --card-hover-shadow: 0 12px 36px rgba(239, 35, 60, 0.2);
            
            /* Typography */
            --text-heading: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
            
            /* Table tokens */
            --th-bg: #111827;
            --th-color: #94a3b8;
            --td-border: rgba(255, 255, 255, 0.06);
            --tr-hover: rgba(239, 35, 60, 0.08);

            /* Bib Token */
            --bib-bg: #172033;
            --bib-border: #ef233c;
            --bib-color: #f8fafc;
            --bib-shadow: 0 2px 12px rgba(239, 35, 60, 0.35);

            /* Bootstrap Overrides */
            --bs-body-bg: #090d16 !important;
            --bs-body-color: #cbd5e1 !important;
        }

        /* ===== Base Layout ===== */
        html, body {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-body-gradient);
            background-attachment: fixed;
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .font-sport {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono-sport {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ===== Floating Glass Navbar (Smooth & Sleek) ===== */
        .glass-navbar {
            background: var(--nav-bg) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--nav-border) !important;
            border-radius: 50px !important;
            margin: 12px auto 0 auto !important;
            max-width: 1360px;
            width: calc(100% - 24px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            position: sticky !important;
            top: 12px;
            z-index: 1030;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        [data-bs-theme="dark"] .glass-navbar {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        /* Logo Sizing */
        .navbar-brand img {
            height: 38px !important;
            width: 38px !important;
            min-width: 38px !important;
            object-fit: contain !important;
        }

        /* Micro-animations */
        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
        }
        .hover-lift:active {
            transform: translateY(0);
        }

        /* Theme Toggle Button */
        .btn-theme-toggle {
            width: 38px;
            height: 38px;
            border-radius: 50% !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--nav-border) !important;
            background: var(--card-bg) !important;
            color: var(--text-heading);
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease;
        }
        .btn-theme-toggle:hover {
            background: var(--athletic-red-light) !important;
            color: var(--athletic-red) !important;
            border-color: var(--athletic-red) !important;
        }
        .btn-theme-toggle i {
            pointer-events: none !important;
        }
        [data-bs-theme="dark"] .theme-icon-light {
            display: none !important;
        }
        [data-bs-theme="dark"] .theme-icon-dark {
            display: inline-block !important;
            color: #fbbf24 !important;
        }
        :root:not([data-bs-theme="dark"]) .theme-icon-dark,
        [data-bs-theme="light"] .theme-icon-dark {
            display: none !important;
        }
        :root:not([data-bs-theme="dark"]) .theme-icon-light,
        [data-bs-theme="light"] .theme-icon-light {
            display: inline-block !important;
            color: #64748b !important;
        }

        /* Action Nav Buttons */
        .btn-nav-action {
            background: var(--card-bg);
            border: 1px solid var(--nav-border);
            color: var(--text-heading);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.42rem 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-nav-action:hover {
            background: var(--athletic-red-light);
            color: var(--athletic-red);
            border-color: var(--athletic-red);
        }

        .btn-nav-primary {
            background: linear-gradient(135deg, var(--athletic-red) 0%, #b00320 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.84rem;
            padding: 0.45rem 1.15rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(217, 4, 41, 0.3);
            transition: all 0.2s ease;
        }
        .btn-nav-primary:hover {
            box-shadow: 0 6px 20px rgba(217, 4, 41, 0.45);
            transform: translateY(-2px);
            color: #ffffff !important;
        }

        /* ===== Athletic Championship Hero Section ===== */
        .hero-championship {
            position: relative;
            background: linear-gradient(135deg, #111827 0%, #1a101f 40%, #2b0b14 100%);
            color: #ffffff;
            border-radius: 28px;
            margin: 1.25rem auto 1.5rem auto;
            max-width: 1360px;
            width: calc(100% - 24px);
            padding: 3.5rem 2rem 3rem 2rem;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        [data-bs-theme="dark"] .hero-championship {
            background: linear-gradient(135deg, #0d121f 0%, #160f1c 50%, #240a12 100%);
            border: 1px solid rgba(239, 35, 60, 0.2);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
        }

        /* Dynamic Track Speed Lines in Background */
        .hero-championship::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 80%;
            height: 200%;
            background: repeating-linear-gradient(
                -45deg,
                rgba(239, 35, 60, 0.04) 0px,
                rgba(239, 35, 60, 0.04) 14px,
                transparent 14px,
                transparent 28px
            );
            pointer-events: none;
        }

        /* Official Federation Badge */
        .federation-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 6px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 1rem;
        }

        /* Championship Title Typography */
        .championship-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: #ffffff;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
        }
        .championship-title .title-lead {
            display: block;
            font-size: clamp(2.2rem, 4.5vw, 3.25rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
            line-height: 1.08;
        }
        .championship-title .title-sub {
            display: block;
            font-size: clamp(1.35rem, 2.6vw, 2.05rem);
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #ffffff;
            margin-top: 0.35rem;
            line-height: 1.15;
        }
        @media (min-width: 992px) {
            .championship-title .title-sub {
                white-space: nowrap;
            }
        }
        .championship-title span.text-highlight {
            background: linear-gradient(135deg, #ff4d6d 0%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Event Meta Details */
        .event-meta {
            font-size: 0.95rem;
            color: #cbd5e1;
            margin-bottom: 1.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .event-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Athletic Search Console */
        .hero-search-box {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            padding: 6px;
            max-width: 640px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
        }
        [data-bs-theme="dark"] .hero-search-box {
            background: rgba(18, 26, 44, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .hero-search-input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.95rem;
            padding: 8px 14px;
            width: 100%;
            color: var(--text-heading);
            font-weight: 500;
        }
        .hero-search-input::placeholder {
            color: #94a3b8;
        }
        .hero-search-btn {
            background: linear-gradient(135deg, var(--athletic-red) 0%, #b00320 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px 22px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .hero-search-btn:hover {
            background: linear-gradient(135deg, #ef233c 0%, #d90429 100%);
            box-shadow: 0 4px 14px rgba(217, 4, 41, 0.4);
            color: #ffffff;
        }

        /* Scoreboard Stat Cards */
        .scoreboard-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 1.15rem 1.35rem;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .scoreboard-card:hover {
            transform: translateY(-3px);
            border-color: rgba(239, 35, 60, 0.4);
            background: rgba(255, 255, 255, 0.12);
        }
        .scoreboard-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(239, 35, 60, 0.25) 0%, rgba(245, 158, 11, 0.2) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #ffffff;
            flex-shrink: 0;
        }
        .scoreboard-num {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.65rem;
            line-height: 1.1;
            color: #ffffff;
        }
        .scoreboard-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #cbd5e1;
        }

        /* ===== Main Content Area ===== */
        .content-container {
            max-width: 1360px;
            width: calc(100% - 24px);
            margin: 0 auto;
        }

        .championship-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            padding: 1.75rem;
            margin-bottom: 2rem;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        /* Quick Filter Pills (Sporty Category Filter) */
        .quick-filter-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 1.25rem;
        }
        .filter-chip {
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            color: var(--text-body);
            border-radius: 50px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.18s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-chip:hover {
            border-color: var(--athletic-red);
            color: var(--athletic-red);
            background: var(--athletic-red-light);
        }
        .filter-chip.active {
            background: var(--athletic-red);
            color: #ffffff !important;
            border-color: var(--athletic-red);
            box-shadow: 0 3px 10px rgba(217, 4, 41, 0.3);
        }

        /* Filter Controls Box */
        .filter-control-box {
            background: var(--th-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.15rem;
            margin-bottom: 1.5rem;
        }

        /* Active Search Indicator */
        .search-badge-alert {
            background: var(--athletic-red-light);
            border: 1px solid rgba(217, 4, 41, 0.25);
            border-radius: 12px;
            padding: 10px 16px;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        /* ===== Athletic Table & Bib Styling ===== */
        .table-championship {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-championship thead th {
            background-color: var(--th-bg) !important;
            color: var(--th-color) !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid var(--athletic-red) !important;
            padding: 1rem 0.85rem;
            white-space: nowrap;
        }
        .table-championship tbody td {
            padding: 1rem 0.85rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--td-border) !important;
            font-size: 0.88rem;
            color: var(--text-body);
            background: transparent !important;
        }
        .table-championship tbody tr:hover td {
            background-color: var(--tr-hover) !important;
        }

        /* Runner Bib Badge (Authentic Athletic Bib Look) */
        .bib-badge {
            background: var(--bib-bg);
            border: 2px solid var(--bib-border);
            color: var(--bib-color);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 0.95rem;
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: var(--bib-shadow);
            letter-spacing: 0.5px;
            min-width: 58px;
            text-align: center;
        }

        /* Gender Badges */
        .badge-gender-putra {
            background: rgba(14, 116, 144, 0.12);
            color: #0284c7;
            border: 1px solid rgba(2, 132, 199, 0.25);
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 8px;
        }
        .badge-gender-putri {
            background: rgba(219, 39, 119, 0.12);
            color: #db2777;
            border: 1px solid rgba(219, 39, 119, 0.25);
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            padding: 3px 8px;
        }
        [data-bs-theme="dark"] .badge-gender-putra {
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
        }
        [data-bs-theme="dark"] .badge-gender-putri {
            background: rgba(219, 39, 119, 0.2);
            color: #f472b6;
            border-color: rgba(244, 114, 182, 0.3);
        }

        /* Event Chips (Track / Field scoreboard chip) */
        .event-race-chip {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 5px 9px;
            margin: 2px 2px 2px 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-heading);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.15s ease;
        }
        .event-race-chip:hover {
            border-color: var(--athletic-red);
            transform: translateY(-1px);
        }
        .event-heat-tag {
            background: var(--athletic-navy);
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
        }
        [data-bs-theme="dark"] .event-heat-tag {
            background: #1e293b;
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .event-lane-tag {
            color: var(--athletic-red);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .event-final-tag {
            background: #16a34a;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            letter-spacing: 0.3px;
        }

        /* Podium Badges */
        .badge-podium-1 {
            background: #fef08a;
            color: #854d0e;
            border: 1px solid #facc15;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .badge-podium-2 {
            background: #e2e8f0;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .badge-podium-3 {
            background: #fed7aa;
            color: #9a3412;
            border: 1px solid #fb923c;
            font-weight: 800;
            font-size: 0.72rem;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* DataTables Controls Dark Mode Support */
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_length,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate {
            color: #94a3b8 !important;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .page-link {
            background-color: #111827 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
        }
        [data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background-color: var(--athletic-red) !important;
            border-color: var(--athletic-red) !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] .form-control {
            background-color: #111827 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .form-select:focus,
        [data-bs-theme="dark"] .form-control:focus {
            border-color: var(--athletic-red) !important;
            box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.25) !important;
        }

        /* Footer */
        .championship-footer {
            background: #0f172a;
            color: #94a3b8;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 2rem 0;
        }
        [data-bs-theme="dark"] .championship-footer {
            background: #060911;
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- FLOATING GLASS NAVBAR                      -->
<!-- ========================================== -->
<nav class="navbar navbar-expand-lg glass-navbar py-2">
    <div class="container-fluid px-3 px-md-4 d-flex justify-content-between align-items-center">
        <!-- Federation & Event Brand -->
        <a href="<?= base_url() ?>" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
            <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI">
            <div class="d-flex flex-column lh-1">
                <span class="fw-extrabold text-uppercase font-sport" style="font-size: 13.5px; letter-spacing: 0.6px; color: var(--athletic-red);">
                    KEJURWIL ATLETIK
                </span>
                <span class="fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.2px;">
                    PASI KOTA PEKALONGAN 2026
                </span>
            </div>
        </a>

        <!-- Action Buttons (Theme Toggle, Cetak Buku Acara, Excel, Login) -->
        <div class="d-flex align-items-center gap-2">
            <!-- Smooth Theme Toggle Button (Light / Dark) -->
            <button id="themeToggleBtnHome" class="btn btn-theme-toggle hover-lift" type="button" title="Ganti Mode Tampilan" onclick="toggleKejurwilTheme(event)">
                <i class="bi bi-moon-fill theme-icon-light fs-5"></i>
                <i class="bi bi-sun-fill theme-icon-dark fs-5"></i>
            </button>

            <!-- Cetak Buku Acara Resmi -->
            <a href="<?= base_url('buku-acara/cetak') ?>" target="_blank" class="btn btn-nav-action hover-lift d-none d-lg-inline-flex">
                <i class="bi bi-printer text-secondary"></i> Cetak Buku Acara
            </a>
            
            <!-- Unduh Rekap Excel -->
            <a href="<?= base_url('downloads/Buku_Acara_Kejurwil_Atletik.xlsx') ?>" class="btn btn-nav-action hover-lift d-none d-sm-inline-flex">
                <i class="bi bi-file-earmark-excel-fill text-success"></i> Unduh Excel
            </a>

            <!-- Login Panitia / Dashboard Admin -->
            <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-nav-primary hover-lift" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                    <i class="bi bi-speedometer2"></i> <span class="d-none d-sm-inline">Dashboard Admin</span>
                </a>
            <?php else: ?>
                <a href="<?= base_url('login') ?>" class="btn btn-nav-primary hover-lift">
                    <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-sm-inline">Login Panitia</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ========================================== -->
<!-- ATHLETIC CHAMPIONSHIP HERO SECTION         -->
<!-- ========================================== -->
<header class="hero-championship">
    <div class="row align-items-center gy-4 position-relative" style="z-index: 2;">
        <!-- Left Column: Title, Venue & Search Console -->
        <div class="col-lg-7">
            <div class="federation-badge">
                <i class="bi bi-trophy-fill text-warning"></i>
                <span>PENGKOT PASI KOTA PEKALONGAN</span>
            </div>
            
            <h1 class="championship-title">
                <span class="title-lead">KEJURWIL</span>
                <span class="title-sub">ATLETIK KOTA PEKALONGAN 2026</span>
            </h1>

            <div class="event-meta">
                <span class="event-meta-item">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?= esc($activeKejuaraan['tempat'] ?? 'Stadion Hoegeng Kota Pekalongan') ?>
                </span>
                <span class="event-meta-item">
                    <i class="bi bi-calendar3 text-warning"></i> 29 Agustus 2026
                </span>
                <span class="event-meta-item">
                    <i class="bi bi-patch-check-fill text-info"></i> Resmi PASI
                </span>
            </div>

            <!-- Sporty Live Search Console -->
            <form id="heroSearchForm" onsubmit="handleHeroSearch(event)" class="hero-search-box">
                <i class="bi bi-search text-muted ps-2 fs-5"></i>
                <input type="text" id="heroSearchInput" class="hero-search-input" placeholder="Cari Nama Atlet, No Dada (misal: 1021), atau Sekolah..." autocomplete="off">
                <button class="hero-search-btn" type="submit">
                    <i class="bi bi-search"></i> Cari Peserta
                </button>
            </form>
        </div>

        <!-- Right Column: Athletic Scoreboard Stat Counters -->
        <div class="col-lg-5">
            <div class="row g-2">
                <div class="col-12 col-sm-6 col-lg-12">
                    <div class="scoreboard-card">
                        <div class="scoreboard-icon">
                            <i class="bi bi-person-walking"></i>
                        </div>
                        <div>
                            <div class="scoreboard-num"><?= number_format($totalAtlet) ?></div>
                            <div class="scoreboard-label">Total Atlet Terdaftar</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-12">
                    <div class="scoreboard-card">
                        <div class="scoreboard-icon">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <div>
                            <div class="scoreboard-num"><?= number_format($totalSekolah) ?></div>
                            <div class="scoreboard-label">Sekolah & Kontingen</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="scoreboard-card">
                        <div class="scoreboard-icon">
                            <i class="bi bi-stopwatch"></i>
                        </div>
                        <div>
                            <div class="scoreboard-num"><?= number_format($totalAcara) ?></div>
                            <div class="scoreboard-label">Nomor Acara Perlombaan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ========================================== -->
<!-- MAIN CONTENT: START LIST & ALLOCATIONS     -->
<!-- ========================================== -->
<main class="content-container" id="sectionDaftarPeserta">
    <div class="championship-card">
        <!-- Section Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-extrabold font-sport mb-1 text-heading">
                    <i class="bi bi-flag-fill text-danger me-2"></i> Daftar Atlet & Jadwal Perlombaan
                </h3>
                <p class="text-muted small mb-0">
                    Periksa alokasi Nomor Dada, Asal Kontingen, Nomor Acara, serta pembagian Seri/Hit dan Lintasan resmi.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetFilters()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
            </div>
        </div>

        <!-- Quick Filter Pills: Gender & Categories -->
        <div class="quick-filter-bar">
            <span class="text-muted small fw-bold me-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Filter Cepat:</span>
            <button type="button" class="filter-chip active" data-gender="" onclick="applyQuickGender('', this)">
                <i class="bi bi-people-fill"></i> Semua Atlet
            </button>
            <button type="button" class="filter-chip" data-gender="Putra" onclick="applyQuickGender('Putra', this)">
                <i class="bi bi-gender-male"></i> Kategori Putra
            </button>
            <button type="button" class="filter-chip" data-gender="Putri" onclick="applyQuickGender('Putri', this)">
                <i class="bi bi-gender-female"></i> Kategori Putri
            </button>
        </div>

        <!-- Detailed Filter Dropdowns -->
        <div class="filter-control-box">
            <div class="row g-2 align-items-center">
                <!-- Filter Kelompok Umur -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Kelompok Umur (KU):</label>
                    <select id="filterKU" class="form-select form-select-sm">
                        <option value="">Semua Kelompok Umur</option>
                        <?php foreach ($kuList as $ku): ?>
                            <option value="<?= esc($ku['nama']) ?>"><?= esc($ku['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Sekolah / Kontingen -->
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1">Asal Sekolah / Kontingen:</label>
                    <select id="filterSekolah" class="form-select form-select-sm">
                        <option value="">Semua Sekolah / Kontingen (<?= count($sekolahList) ?> Kontingen)</option>
                        <?php foreach ($sekolahList as $s): ?>
                            <option value="<?= esc($s['nama']) ?>"><?= esc($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Live Search Summary -->
                <div class="col-md-3 d-flex align-items-end">
                    <div class="w-100 text-end small text-muted pb-1">
                        Total Kontingen: <strong class="text-heading"><?= count($sekolahList) ?></strong> &bull; Total Atlet: <strong class="text-heading"><?= count($atlets) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Search Notice -->
        <div id="searchActiveInfo" class="search-badge-alert d-none">
            <div>
                <i class="bi bi-search me-2 text-danger"></i> Hasil pencarian untuk: <strong id="searchActiveQuery" class="text-heading"></strong>
                &bull; Ditemukan <strong id="searchActiveCount" class="text-danger">0</strong> atlet.
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" onclick="resetFilters()">
                <i class="bi bi-x-lg me-1"></i> Bersihkan
            </button>
        </div>

        <!-- Championship Athlete Table -->
        <div class="table-responsive">
            <table class="table table-championship align-middle w-100" id="tablePesertaPublik">
                <thead>
                    <tr>
                        <th width="45" class="text-center">NO</th>
                        <th width="105" class="text-center">NO DADA</th>
                        <th width="230">NAMA ATLET</th>
                        <th width="115" class="text-center">KATEGORI</th>
                        <th width="220">ASAL KONTINGEN</th>
                        <th>NOMOR LOMBA, SERI & LINTASAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($atlets as $a): ?>
                        <tr>
                            <!-- No Urut -->
                            <td class="text-center text-muted fw-bold"><?= $no++ ?></td>

                            <!-- No Dada (Authentic Running Bib Look) -->
                            <td class="text-center">
                                <?php if (!empty($a['no_dada'])): ?>
                                    <span class="bib-badge"><?= esc($a['no_dada']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fw-bold">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Nama Atlet -->
                            <td>
                                <div class="fw-bold text-heading font-sport" style="font-size: 0.95rem;">
                                    <?= esc($a['nama']) ?>
                                </div>
                                <div class="small text-muted" style="font-size: 11px;">
                                    <?= $a['gender'] === 'L' ? 'Putra' : 'Putri' ?> &bull; <?= esc($a['nama_ku'] ?? 'KU') ?>
                                </div>
                            </td>

                            <!-- Kategori (Gender & KU) -->
                            <td class="text-center">
                                <span class="d-none"><?= $a['gender'] === 'L' ? 'Putra' : 'Putri' ?> <?= esc($a['nama_ku']) ?></span>
                                <?php if ($a['gender'] === 'L'): ?>
                                    <span class="badge-gender-putra">Putra</span>
                                <?php else: ?>
                                    <span class="badge-gender-putri">Putri</span>
                                <?php endif; ?>
                                <div class="small text-muted mt-1" style="font-size: 10.5px; font-weight: 600;">
                                    <?= esc($a['nama_ku'] ?? '-') ?>
                                </div>
                            </td>

                            <!-- Asal Sekolah / Kontingen -->
                            <td>
                                <div class="fw-bold text-heading"><?= esc($a['nama_sekolah'] ?: '-') ?></div>
                                <?php if (!empty($a['jenjang'])): ?>
                                    <small class="text-muted" style="font-size: 11px;"><?= esc($a['jenjang']) ?></small>
                                <?php endif; ?>
                            </td>

                            <!-- Nomor Lomba yang Diikuti -->
                            <td>
                                <?php if (empty($a['events'])): ?>
                                    <span class="text-muted small fst-italic">Belum dialokasikan ke nomor acara</span>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap align-items-center gap-1">
                                        <?php foreach ($a['events'] as $ev): ?>
                                            <div class="event-race-chip" title="<?= esc($ev['nama_acara']) ?>">
                                                <i class="bi bi-stopwatch text-danger"></i>
                                                <span>Acara <?= esc($ev['nomor_acara']) ?>: <?= esc($ev['nama_lomba'] ?? $ev['nama_acara']) ?></span>
                                                
                                                <?php if (($ev['babak'] ?? '') === 'Final'): ?>
                                                    <span class="event-final-tag">FINAL</span>
                                                <?php else: ?>
                                                    <span class="event-heat-tag">Hit <?= $ev['heat'] ?: 1 ?></span>
                                                <?php endif; ?>

                                                <?php if (!empty($ev['lane'])): ?>
                                                    <?php 
                                                    $evNameCheck = strtolower(($ev['nama_acara'] ?? '') . ' ' . ($ev['nama_lomba'] ?? ''));
                                                    $isFieldCheck = (bool)preg_match('/(tolak|lempar|lompat|lontar|cakram|martil|peluru|lembing)/i', $evNameCheck);
                                                    ?>
                                                    <span class="event-lane-tag">(<?= $isFieldCheck ? 'Giliran ' . esc($ev['lane']) : 'L' . esc($ev['lane']) ?>)</span>
                                                <?php endif; ?>

                                                <?php if (!empty($ev['prestasi'])): ?>
                                                    <span class="badge bg-success py-0 px-1 text-white ms-1 fw-bold" style="font-size: 10px;">
                                                        <?= esc($ev['prestasi']) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($ev['peringkat'])): ?>
                                                    <?php if ($ev['peringkat'] == 1): ?>
                                                        <span class="badge-podium-1 ms-1">🥇 Juara 1</span>
                                                    <?php elseif ($ev['peringkat'] == 2): ?>
                                                        <span class="badge-podium-2 ms-1">🥈 Juara 2</span>
                                                    <?php elseif ($ev['peringkat'] == 3): ?>
                                                        <span class="badge-podium-3 ms-1">🥉 Juara 3</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary text-white py-0 px-1 ms-1 fw-bold" style="font-size: 10px;">
                                                            Peringkat <?= $ev['peringkat'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ========================================== -->
<!-- ATHLETIC CHAMPIONSHIP FOOTER               -->
<!-- ========================================== -->
<footer class="championship-footer">
    <div class="content-container">
        <div class="row align-items-center gy-3">
            <div class="col-md-7 text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                    <img src="<?= base_url('assets/images/pasi-logo.png') ?>" alt="Logo PASI" style="height: 32px; filter: brightness(1.2);">
                    <span class="fw-bold text-white font-sport">Sistem Informasi Kejuaraan Atletik PASI Kota Pekalongan</span>
                </div>
                <small class="text-muted">
                    &copy; <?= date('Y') ?> Pengurus Kota PASI Pekalongan. Hak cipta dilindungi undang-undang.
                </small>
            </div>
            <div class="col-md-5 text-center text-md-end">
                <div class="d-flex justify-content-center justify-content-md-end align-items-center gap-2">
                    <a href="<?= base_url('buku-acara/cetak') ?>" target="_blank" class="btn btn-sm btn-outline-light px-3 fw-bold">
                        <i class="bi bi-printer me-1"></i> Cetak Buku Acara
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-sm btn-danger px-3 fw-bold">
                        <i class="bi bi-lock-fill me-1"></i> Login Panitia
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Vendor Scripts -->
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/dataTables.bootstrap5.min.js') ?>"></script>

<script>
let dataTablePeserta = null;

function updateSearchInfo(query) {
    if (query && query.trim() !== '') {
        const info = dataTablePeserta.page.info();
        $('#searchActiveQuery').text(query.trim());
        $('#searchActiveCount').text(info.recordsDisplay);
        $('#searchActiveInfo').removeClass('d-none').addClass('d-flex');
    } else {
        $('#searchActiveInfo').removeClass('d-flex').addClass('d-none');
    }
}

function handleHeroSearch(e) {
    if (e) e.preventDefault();
    const query = $('#heroSearchInput').val().trim();
    if (dataTablePeserta) {
        dataTablePeserta.search(query).draw();
        updateSearchInfo(query);
    }
    focusPesertaTable();
}

function focusPesertaTable() {
    const el = document.getElementById('sectionDaftarPeserta');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}

function applyQuickGender(gender, element) {
    $('.quick-filter-bar .filter-chip').removeClass('active');
    $(element).addClass('active');
    
    if (dataTablePeserta) {
        dataTablePeserta.column(3).search(gender).draw();
        updateSearchInfo($('#heroSearchInput').val());
    }
}

function resetFilters() {
    $('#heroSearchInput').val('');
    $('#filterKU').val('');
    $('#filterSekolah').val('');
    $('.quick-filter-bar .filter-chip').removeClass('active');
    $('.quick-filter-bar .filter-chip[data-gender=""]').addClass('active');
    $('#searchActiveInfo').removeClass('d-flex').addClass('d-none');
    
    if (dataTablePeserta) {
        dataTablePeserta.search('').columns().search('').draw();
    }
}

$(document).ready(function () {
    // Inisialisasi DataTable Atlet
    dataTablePeserta = $('#tablePesertaPublik').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 250],
        language: {
            search: "Cari cepat di tabel:",
            lengthMenu: "Tampilkan _MENU_ atlet",
            zeroRecords: "Tidak ada atlet yang cocok dengan kriteria",
            info: "Menampilkan _START_ - _END_ dari total _TOTAL_ atlet",
            infoEmpty: "Menampilkan 0 atlet",
            infoFiltered: "(disaring dari _MAX_ total atlet)",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            }
        },
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });

    // Sinkronisasi Live Search dari Hero
    $('#heroSearchInput').on('input keyup', function () {
        const val = this.value;
        dataTablePeserta.search(val).draw();
        updateSearchInfo(val);
    });

    // Filter Kelompok Umur
    $('#filterKU').on('change', function () {
        dataTablePeserta.column(2).search(this.value).draw();
    });

    // Filter Asal Sekolah
    $('#filterSekolah').on('change', function () {
        dataTablePeserta.column(4).search(this.value).draw();
    });
});
</script>

</body>
</html>
