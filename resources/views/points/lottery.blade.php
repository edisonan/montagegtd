@extends('layouts.app')

@section('title', '积分抽奖 - 蒙太奇')
@section('description', '蒙太奇积分抽奖，九宫格、大转盘、翻牌、老虎机、刮刮乐多种玩法，好礼即时到账')

@section('content')
    <style>
        .lottery-app {
            --radius: 22px;
            --radius-sm: 14px;
            --bg-1: #f0fdf4;
            --bg-2: #f8fafc;
            --glow-1: rgba(52, 211, 153, .38);
            --glow-2: rgba(34, 211, 238, .30);
            --surface: rgba(255, 255, 255, .82);
            --surface-2: rgba(255, 255, 255, .55);
            --solid: #ffffff;
            --border: rgba(16, 185, 129, .22);
            --border-strong: rgba(16, 185, 129, .45);
            --text: #065f46;
            --strong: #022c22;
            --muted: #64748b;
            --primary: #10b981;
            --primary-2: #059669;
            --accent: #22d3ee;
            --on-primary: #ffffff;
            --cell-bg: linear-gradient(180deg, #ffffff, #f0fdf4);
            --cell-border: rgba(16, 185, 129, .22);
            --center-bg: radial-gradient(circle at 30% 20%, #6ee7b7, #059669);
            --center-text: #ffffff;
            --chip: rgba(16, 185, 129, .12);
            --chip-border: rgba(16, 185, 129, .35);
            --chip-text: #065f46;
            --track: rgba(16, 185, 129, .18);
            --shadow: 0 22px 44px -28px rgba(5, 150, 105, .55);

            position: relative;
            border-radius: var(--radius);
            padding: clamp(16px, 2.4vw, 28px);
            color: var(--text);
            overflow: hidden;
            background:
                radial-gradient(1100px 560px at 0% 0%, var(--glow-1), transparent 60%),
                radial-gradient(900px 520px at 100% 0%, var(--glow-2), transparent 55%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
            box-shadow: var(--shadow);
            transition: background .45s ease, color .45s ease, box-shadow .45s ease;
        }

        .lottery-app::before,
        .lottery-app::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(60px);
            opacity: .55;
            pointer-events: none;
            z-index: 0;
        }
        .lottery-app::before {
            width: 320px;
            height: 320px;
            top: -120px;
            right: -60px;
            background: var(--glow-1);
            animation: orbFloat 9s ease-in-out infinite;
        }
        .lottery-app::after {
            width: 260px;
            height: 260px;
            bottom: -110px;
            left: -70px;
            background: var(--glow-2);
            animation: orbFloat 11s ease-in-out infinite reverse;
        }
        .lottery-app[data-theme="midnight"] {
            --bg-1: #0b1020; --bg-2: #05070f;
            --glow-1: rgba(99, 102, 241, .45); --glow-2: rgba(34, 211, 238, .35);
            --surface: rgba(15, 23, 42, .78); --surface-2: rgba(30, 41, 59, .5);
            --solid: #0f172a;
            --border: rgba(99, 102, 241, .32); --border-strong: rgba(34, 211, 238, .55);
            --text: #c7d2fe; --strong: #f8fafc; --muted: #94a3b8;
            --primary: #22d3ee; --primary-2: #6366f1; --accent: #a855f7; --on-primary: #04121f;
            --cell-bg: linear-gradient(180deg, rgba(30, 41, 59, .92), rgba(15, 23, 42, .92));
            --cell-border: rgba(99, 102, 241, .3);
            --center-bg: radial-gradient(circle at 30% 20%, #818cf8, #4338ca);
            --center-text: #f8fafc;
            --chip: rgba(99, 102, 241, .18); --chip-border: rgba(99, 102, 241, .45); --chip-text: #c7d2fe;
            --track: rgba(99, 102, 241, .25);
            --shadow: 0 26px 54px -30px rgba(56, 189, 248, .6);
        }
        .lottery-app[data-theme="gold"] {
            --bg-1: #1a1206; --bg-2: #0a0703;
            --glow-1: rgba(245, 158, 11, .45); --glow-2: rgba(239, 68, 68, .3);
            --surface: rgba(28, 20, 8, .82); --surface-2: rgba(60, 42, 15, .45);
            --solid: #171006;
            --border: rgba(245, 158, 11, .32); --border-strong: rgba(251, 191, 36, .6);
            --text: #fde68a; --strong: #fffbeb; --muted: #d6b26a;
            --primary: #f59e0b; --primary-2: #d97706; --accent: #fbbf24; --on-primary: #1a1206;
            --cell-bg: linear-gradient(180deg, rgba(60, 42, 15, .85), rgba(23, 16, 6, .92));
            --cell-border: rgba(245, 158, 11, .3);
            --center-bg: radial-gradient(circle at 30% 20%, #fbbf24, #b45309);
            --center-text: #1a1206;
            --chip: rgba(245, 158, 11, .18); --chip-border: rgba(245, 158, 11, .5); --chip-text: #fde68a;
            --track: rgba(245, 158, 11, .25);
            --shadow: 0 26px 54px -30px rgba(245, 158, 11, .65);
        }
        .lottery-app[data-theme="candy"] {
            --bg-1: #fdf2f8; --bg-2: #f5f3ff;
            --glow-1: rgba(244, 114, 182, .4); --glow-2: rgba(167, 139, 250, .4);
            --surface: rgba(255, 255, 255, .85); --surface-2: rgba(255, 255, 255, .55);
            --solid: #ffffff;
            --border: rgba(244, 114, 182, .28); --border-strong: rgba(244, 114, 182, .55);
            --text: #9d174d; --strong: #4c0519; --muted: #6b7280;
            --primary: #ec4899; --primary-2: #a855f7; --accent: #f472b6; --on-primary: #ffffff;
            --cell-bg: linear-gradient(180deg, #ffffff, #fdf2f8);
            --cell-border: rgba(244, 114, 182, .26);
            --center-bg: radial-gradient(circle at 30% 20%, #f9a8d4, #a855f7);
            --center-text: #ffffff;
            --chip: rgba(244, 114, 182, .14); --chip-border: rgba(244, 114, 182, .4); --chip-text: #9d174d;
            --track: rgba(244, 114, 182, .2);
            --shadow: 0 22px 44px -28px rgba(236, 72, 153, .55);
        }
        .lottery-app[data-theme="ocean"] {
            --bg-1: #eff6ff; --bg-2: #ecfeff;
            --glow-1: rgba(59, 130, 246, .4); --glow-2: rgba(6, 182, 212, .35);
            --surface: rgba(255, 255, 255, .85); --surface-2: rgba(255, 255, 255, .55);
            --solid: #ffffff;
            --border: rgba(59, 130, 246, .25); --border-strong: rgba(59, 130, 246, .5);
            --text: #1e3a8a; --strong: #0f172a; --muted: #64748b;
            --primary: #3b82f6; --primary-2: #0ea5e9; --accent: #06b6d4; --on-primary: #ffffff;
            --cell-bg: linear-gradient(180deg, #ffffff, #eff6ff);
            --cell-border: rgba(59, 130, 246, .22);
            --center-bg: radial-gradient(circle at 30% 20%, #38bdf8, #2563eb);
            --center-text: #ffffff;
            --chip: rgba(59, 130, 246, .12); --chip-border: rgba(59, 130, 246, .4); --chip-text: #1e3a8a;
            --track: rgba(59, 130, 246, .2);
            --shadow: 0 22px 44px -28px rgba(59, 130, 246, .55);
        }

        .lottery-app > * { position: relative; z-index: 1; }

        .lottery-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--chip);
            border: 1px solid var(--chip-border);
            color: var(--chip-text);
            text-transform: uppercase;
        }
        .lottery-hero h1 {
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 800;
            line-height: 1.15;
            margin: 10px 0 6px;
            color: var(--strong);
            letter-spacing: -.02em;
        }
        .lottery-hero p { font-size: 14px; color: var(--muted); margin: 0; }
        .hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }

        .switch-row { margin-top: 12px; }
        .switch-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .theme-switch, .mode-switch { display: flex; flex-wrap: wrap; gap: 8px; }
        .theme-dot, .mode-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            padding: 6px 13px 6px 9px;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all .2s ease;
            backdrop-filter: blur(6px);
        }
        .mode-chip { border-radius: 12px; padding: 8px 14px; font-size: 12.5px; }
        .theme-dot:hover, .mode-chip:hover { transform: translateY(-1px); color: var(--text); }
        .theme-dot .swatch {
            width: 14px; height: 14px; border-radius: 999px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .35);
        }
        .theme-dot.is-active {
            color: var(--strong);
            border-color: var(--border-strong);
            background: var(--chip);
            box-shadow: 0 10px 22px -14px var(--glow-1);
        }
        .mode-chip.is-active {
            color: var(--on-primary);
            border-color: var(--border-strong);
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 24px -16px var(--glow-1);
        }
        .swatch-aurora { background: linear-gradient(135deg, #34d399, #22d3ee); }
        .swatch-midnight { background: linear-gradient(135deg, #6366f1, #22d3ee); }
        .swatch-gold { background: linear-gradient(135deg, #fbbf24, #b45309); }
        .swatch-candy { background: linear-gradient(135deg, #f472b6, #a855f7); }
        .swatch-ocean { background: linear-gradient(135deg, #38bdf8, #2563eb); }

        .soft-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--chip-text);
            background: var(--chip);
            border: 1px solid var(--chip-border);
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease;
            text-decoration: none;
        }
        .soft-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -16px var(--glow-1); }

        .sound-toggle {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: 13px; font-weight: 600; color: var(--chip-text);
            padding: 7px 13px; border: 1px solid var(--chip-border);
            border-radius: 999px; background: var(--chip); cursor: pointer; user-select: none;
        }
        .sound-toggle input { accent-color: var(--primary); }

        .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
        .stat-card {
            border-radius: var(--radius-sm); border: 1px solid var(--border);
            background: var(--surface); backdrop-filter: blur(10px); padding: 13px 15px;
        }
        .stat-card .label { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 6px; }
        .stat-card .value { font-size: 22px; font-weight: 800; color: var(--strong); margin-top: 4px; line-height: 1; }

        .lottery-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; }
        @media (min-width: 1024px) {
            .lottery-grid { grid-template-columns: minmax(0, 3fr) minmax(280px, 1fr); }
        }

        .panel {
            border-radius: var(--radius); border: 1px solid var(--border);
            background: var(--surface); backdrop-filter: blur(12px); overflow: hidden;
        }
        .panel-head {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            padding: 14px 18px; border-bottom: 1px solid var(--border);
        }
        .panel-head .title { display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--strong); }
        .panel-head .hint { font-size: 12px; color: var(--muted); }
        .panel-body { padding: 18px; }

        .pool-shell {
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: linear-gradient(160deg, var(--surface), var(--surface-2));
            padding: clamp(14px, 2vw, 20px);
            position: relative;
            overflow: hidden;
        }
        .pool-shell + .pool-shell { margin-top: 18px; }
        .pool-top {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 12px; margin-bottom: 6px;
        }
        .pool-name { font-size: 17px; font-weight: 800; color: var(--strong); display: flex; align-items: center; gap: 8px; }
        .pool-desc { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .pool-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .pool-pity-label { font-size: 12px; color: var(--text); font-weight: 600; }

        .ten-btn {
            border: 1px solid var(--border-strong);
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: var(--on-primary); border-radius: 999px; font-size: 13px; font-weight: 700;
            padding: 8px 15px; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease;
        }
        .ten-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 26px -16px var(--glow-1); }
        .ten-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

        .pity-track { width: 100%; height: 8px; border-radius: 999px; background: var(--track); overflow: hidden; margin-top: 10px; }
        .pity-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--primary), var(--accent)); transition: width .4s ease; }
        .pity-hint {
            margin-top: 8px; font-size: 12px; color: var(--chip-text);
            background: var(--chip); border: 1px dashed var(--chip-border); border-radius: 10px; padding: 7px 10px;
        }

        .play-btn {
            border: 0; border-radius: 999px; padding: 11px 20px; font-size: 15px; font-weight: 800;
            cursor: pointer; color: var(--primary-2); background: #ffffff;
            box-shadow: 0 12px 22px -14px rgba(0, 0, 0, .5); transition: transform .18s ease;
        }
        .play-btn:hover { transform: translateY(-1px) scale(1.02); }
        .play-btn:disabled { opacity: .6; cursor: not-allowed; }

        /* ---------- 九宫格 ---------- */
        .lucky-board { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
        .lc-cell {
            min-height: 96px; border: 1px solid var(--cell-border); border-radius: var(--radius-sm);
            background: var(--cell-bg); padding: 10px; display: flex; flex-direction: column;
            justify-content: space-between; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            box-shadow: 0 10px 22px -18px var(--glow-1); position: relative; overflow: hidden;
        }
        .lc-title { font-size: 13px; font-weight: 700; color: var(--strong); line-height: 1.25; word-break: break-word; }
        .lc-meta { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-top: 8px; }
        .lc-prob { font-size: 11px; color: var(--muted); }
        .lc-cell.is-active {
            transform: translateY(-3px) scale(1.04); border-color: var(--border-strong);
            box-shadow: 0 0 0 2px var(--glow-1), 0 18px 30px -18px var(--glow-1);
        }
        .lc-cell.is-win {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--glow-1), 0 22px 36px -18px var(--glow-1);
            animation: winPulse .7s ease;
        }
        .lc-center {
            border: 1px solid var(--border-strong); background: var(--center-bg); color: var(--center-text);
            align-items: center; justify-content: center; text-align: center;
            box-shadow: inset 0 0 22px rgba(255, 255, 255, .18), 0 16px 30px -18px var(--glow-1);
        }
        .lc-center .cost { font-size: 12px; opacity: .92; margin-bottom: 6px; font-weight: 600; }
        .lc-center .pity-mini { font-size: 11px; opacity: .9; margin-top: 7px; }

        /* ---------- 大转盘 ---------- */
        .wheel-stage { display: flex; justify-content: center; padding: 18px 0 8px; }
        .wheel-wrap { position: relative; width: min(340px, 76vw); aspect-ratio: 1 / 1; }
        .wheel-disc {
            position: absolute; inset: 0; border-radius: 50%;
            border: 8px solid var(--solid);
            box-shadow: 0 0 0 4px var(--border-strong), 0 26px 50px -26px var(--glow-1);
            will-change: transform;
        }
        .wheel-disc::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%;
            background: radial-gradient(circle, transparent 58%, rgba(255, 255, 255, .18) 60%, transparent 72%);
            pointer-events: none;
        }
        .wheel-labels { position: absolute; inset: 0; pointer-events: none; }
        .wheel-label-rot { position: absolute; inset: 0; display: flex; justify-content: center; }
        .wheel-label-rot > span {
            margin-top: 9%; max-width: 52%; font-size: 11px; font-weight: 700; color: rgba(255, 255, 255, .96);
            text-shadow: 0 1px 3px rgba(0, 0, 0, .45); text-align: center; line-height: 1.15;
        }
        .wheel-hub {
            position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
            width: 27%; aspect-ratio: 1 / 1; border-radius: 50%; border: 4px solid var(--solid);
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: var(--on-primary); font-size: 15px; font-weight: 800; cursor: pointer;
            box-shadow: 0 12px 26px -14px rgba(0, 0, 0, .55); z-index: 3;
        }
        .wheel-hub:hover { filter: brightness(1.06); }
        .wheel-pointer {
            position: absolute; left: 50%; top: -8px; transform: translateX(-50%);
            width: 0; height: 0; border-left: 13px solid transparent; border-right: 13px solid transparent;
            border-top: 24px solid var(--primary-2); z-index: 4;
            filter: drop-shadow(0 3px 4px rgba(0, 0, 0, .35));
        }
        .stage-hint { text-align: center; font-size: 12px; color: var(--muted); margin-top: 8px; }

        /* ---------- 翻牌 ---------- */
        .card-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
        .flip-card { perspective: 900px; aspect-ratio: 3 / 4; cursor: pointer; }
        .flip-inner { position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transition: transform .55s cubic-bezier(.3, .8, .3, 1); }
        .flip-card.is-flipped .flip-inner { transform: rotateY(180deg); }
        .flip-face {
            position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden;
            border-radius: 12px; border: 1px solid var(--cell-border);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 6px; text-align: center; overflow: hidden;
        }
        .flip-down { background: linear-gradient(160deg, var(--primary), var(--primary-2)); color: var(--on-primary); font-size: 22px; }
        .flip-down::after { content: '翻'; position: absolute; bottom: 8px; font-size: 10px; opacity: .8; letter-spacing: .2em; }
        .flip-up { background: var(--cell-bg); transform: rotateY(180deg); }
        .flip-name { font-size: 12px; font-weight: 800; color: var(--strong); line-height: 1.2; word-break: break-word; }

        /* ---------- 老虎机 ---------- */
        .slot-stage { display: flex; gap: 12px; justify-content: center; padding: 20px 0 10px; }
        .slot-reel {
            width: min(128px, 28vw); height: 100px; border-radius: 14px; border: 1px solid var(--cell-border);
            background: var(--cell-bg); overflow: hidden; display: flex; align-items: center; justify-content: center;
            padding: 8px; text-align: center; font-size: 15px; font-weight: 800; color: var(--strong);
            box-shadow: inset 0 14px 18px -18px rgba(0, 0, 0, .5), inset 0 -14px 18px -18px rgba(0, 0, 0, .5);
        }
        .slot-reel.is-spinning { animation: reelShake .16s linear infinite; }
        .slot-reel.is-settled { animation: winPulse .5s ease; border-color: var(--primary); }

        /* ---------- 刮刮乐 ---------- */
        .scratch-stage { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 16px 0 6px; }
        .scratch-wrap {
            position: relative; width: min(340px, 82vw); aspect-ratio: 16 / 10; border-radius: 16px;
            overflow: hidden; border: 1px solid var(--cell-border);
            background: var(--cell-bg); box-shadow: 0 18px 34px -22px var(--glow-1);
        }
        .scratch-prize {
            position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 4px; padding: 16px; text-align: center;
        }
        .scratch-prize-name { font-size: clamp(18px, 4vw, 24px); font-weight: 900; color: var(--strong); line-height: 1.2; }
        .scratch-prize-sub { font-size: 12px; color: var(--muted); }
        .scratch-canvas { position: absolute; inset: 0; width: 100%; height: 100%; touch-action: none; cursor: crosshair; }

        .rarity-badge {
            display: inline-flex; align-items: center; justify-content: center; min-width: 38px;
            border-radius: 999px; font-size: 10px; font-weight: 800; letter-spacing: .04em;
            padding: 2px 8px; border: 1px solid transparent;
        }
        .rarity-ssr { color: #7c2d12; background: #ffedd5; border-color: #fdba74; }
        .rarity-sr { color: #5b21b6; background: #ede9fe; border-color: #c4b5fd; }
        .rarity-r { color: #1d4ed8; background: #dbeafe; border-color: #93c5fd; }
        .rarity-n { color: #374151; background: #f3f4f6; border-color: #d1d5db; }

        .log-list { max-height: 560px; overflow: auto; display: flex; flex-direction: column; gap: 8px; }
        .log-item {
            border: 1px solid var(--border); border-radius: 12px; background: var(--surface-2);
            padding: 9px 11px; animation: fadeSlide .3s ease;
        }
        .log-item .name { font-size: 13px; font-weight: 700; color: var(--strong); }
        .log-item .time { font-size: 11px; color: var(--muted); margin-top: 2px; }
        .empty-tip { color: var(--muted); font-size: 13px; text-align: center; padding: 26px 0; }

        .shop-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
        .shop-card {
            border: 1px solid var(--border); border-radius: var(--radius-sm);
            background: linear-gradient(160deg, var(--surface), var(--surface-2)); padding: 14px;
            display: flex; flex-direction: column; transition: transform .2s ease, box-shadow .2s ease;
        }
        .shop-card:hover { transform: translateY(-3px); box-shadow: 0 18px 34px -24px var(--glow-1); }
        .shop-card .name { font-weight: 700; color: var(--strong); }
        .shop-card .desc { font-size: 12px; color: var(--muted); margin-top: 5px; flex: 1; }
        .shop-card .foot { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; gap: 8px; }
        .price-tag { font-size: 15px; font-weight: 800; color: var(--primary-2); }
        .price-tag small { font-size: 11px; font-weight: 600; }

        .draw-result-modal, .order-modal {
            position: fixed; inset: 0; background: rgba(2, 6, 23, .55); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center; z-index: 1200; padding: 18px;
        }
        .draw-result-modal.show, .order-modal.show { display: flex; }
        .draw-result-card, .order-modal-card {
            width: min(560px, 94vw); max-height: 88vh; overflow: auto; border-radius: 20px;
            border: 1px solid var(--border-strong); background: var(--solid);
            box-shadow: 0 34px 70px -36px rgba(2, 6, 23, .7); padding: 22px; color: var(--text);
            animation: popIn .28s cubic-bezier(.2, .8, .3, 1.2);
        }
        .order-modal-card { width: min(780px, 94vw); }
        .modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .modal-head .title { font-size: 17px; font-weight: 800; color: var(--strong); }

        .result-wrap { text-align: center; }
        .result-rarity {
            display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .1em;
            padding: 4px 14px; border-radius: 999px; margin-bottom: 12px;
        }
        .result-name { font-size: 26px; font-weight: 900; color: var(--strong); margin-bottom: 6px; }
        .result-sub { font-size: 13px; color: var(--muted); }
        .result-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; margin-top: 16px; text-align: left; }
        .result-chip {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            border: 1px solid var(--border); border-radius: 12px; background: var(--surface-2);
            padding: 8px 10px; font-size: 12px; color: var(--text);
        }
        .result-chip .idx { color: var(--muted); font-weight: 700; }

        .order-item { border: 1px solid var(--border); border-radius: 12px; background: var(--surface-2); padding: 12px 14px; margin-bottom: 10px; }
        .order-item .row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .order-item .gname { font-weight: 700; color: var(--strong); }
        .order-item .time { font-size: 11px; color: var(--muted); }
        .order-item .meta { font-size: 12px; color: var(--muted); margin-top: 5px; }

        .lottery-tip {
            position: fixed; right: 18px; top: 82px; z-index: 1300; min-width: 220px; max-width: min(420px, 90vw);
            border-radius: 12px; border: 1px solid var(--border-strong); background: var(--solid); color: var(--text);
            box-shadow: 0 20px 40px -22px rgba(2, 6, 23, .55); padding: 11px 14px; font-size: 13px;
            opacity: 0; transform: translateY(-8px); pointer-events: none; transition: all .22s ease;
        }
        .lottery-tip.show { opacity: 1; transform: translateY(0); }
        .lottery-tip.error { border-color: #fca5a5; background: #fef2f2; color: #991b1b; }

        .lottery-fx { pointer-events: none; position: absolute; inset: 0; overflow: hidden; z-index: 2; }
        .lottery-fx .spark {
            position: absolute; width: 9px; height: 9px; border-radius: 999px;
            background: radial-gradient(circle, #fef08a, var(--primary));
            animation: sparkFly .95s ease-out forwards;
        }

        @keyframes sparkFly {
            0% { opacity: 0; transform: translate(0, 0) scale(.2); }
            18% { opacity: 1; }
            100% { opacity: 0; transform: translate(var(--tx), var(--ty)) scale(1.25); }
        }
        @keyframes popIn { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes winPulse { 0% { transform: scale(1); } 45% { transform: scale(1.07); } 100% { transform: scale(1); } }
        @keyframes reelShake { 0% { transform: translateY(-3px); } 50% { transform: translateY(3px); } 100% { transform: translateY(-3px); } }
        @keyframes orbFloat { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-18px, 22px); } }
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 640px) {
            .stat-grid { gap: 8px; }
            .stat-card { padding: 10px; }
            .stat-card .value { font-size: 18px; }
            .lc-cell { min-height: 84px; padding: 8px; }
            .lc-title { font-size: 12px; }
            .card-grid { gap: 7px; }
            .slot-reel { height: 84px; font-size: 13px; }
        }
    </style>

    <div id="lotteryApp" class="lottery-app max-w-6xl mx-auto" data-theme="aurora">
        <div class="lottery-hero">
            <div>
                <span class="hero-badge"><i class="fas fa-wand-magic-sparkles"></i> Lucky Draw</span>
                <h1>积分抽奖</h1>
                <p>五种玩法随机转出好礼，消耗 AP 立即开奖</p>

                <div class="switch-row">
                    <div class="switch-label">玩法</div>
                    <div class="mode-switch" id="modeSwitch">
                        <button type="button" class="mode-chip is-active" data-mode="grid" onclick="applyMode('grid')"><i class="fas fa-table-cells"></i>九宫格</button>
                        <button type="button" class="mode-chip" data-mode="wheel" onclick="applyMode('wheel')"><i class="fas fa-dharmachakra"></i>大转盘</button>
                        <button type="button" class="mode-chip" data-mode="cards" onclick="applyMode('cards')"><i class="fas fa-clone"></i>翻牌</button>
                        <button type="button" class="mode-chip" data-mode="slot" onclick="applyMode('slot')"><i class="fas fa-dice"></i>老虎机</button>
                        <button type="button" class="mode-chip" data-mode="scratch" onclick="applyMode('scratch')"><i class="fas fa-hand-sparkles"></i>刮刮乐</button>
                    </div>
                </div>

                <div class="switch-row">
                    <div class="switch-label">皮肤</div>
                    <div class="theme-switch" id="themeSwitch">
                        <button type="button" class="theme-dot is-active" data-theme="aurora" onclick="applyTheme('aurora')"><span class="swatch swatch-aurora"></span>极光</button>
                        <button type="button" class="theme-dot" data-theme="midnight" onclick="applyTheme('midnight')"><span class="swatch swatch-midnight"></span>暗夜</button>
                        <button type="button" class="theme-dot" data-theme="gold" onclick="applyTheme('gold')"><span class="swatch swatch-gold"></span>鎏金</button>
                        <button type="button" class="theme-dot" data-theme="candy" onclick="applyTheme('candy')"><span class="swatch swatch-candy"></span>糖果</button>
                        <button type="button" class="theme-dot" data-theme="ocean" onclick="applyTheme('ocean')"><span class="swatch swatch-ocean"></span>海洋</button>
                    </div>
                </div>
            </div>
            <div class="hero-actions">
                <label class="sound-toggle">
                    <input type="checkbox" id="soundEnabled" checked>
                    音效
                </label>
                <button type="button" class="soft-btn" onclick="openOrdersModal()"><i class="fas fa-receipt"></i>订单记录</button>
                <a href="/point-mall" class="soft-btn"><i class="fas fa-arrow-left"></i>返回商城</a>
            </div>
        </div>

        <div class="stat-grid" id="lotteryStats">
            <div class="stat-card"><div class="label"><i class="fas fa-layer-group"></i>奖池</div><div class="value">-</div></div>
            <div class="stat-card"><div class="label"><i class="fas fa-shield-halved"></i>保底进度</div><div class="value">-</div></div>
            <div class="stat-card"><div class="label"><i class="fas fa-clock-rotate-left"></i>最近记录</div><div class="value">-</div></div>
        </div>

        <div class="lottery-grid">
            <div class="panel">
                <div class="panel-head">
                    <div class="title"><i class="fas fa-dice"></i><span id="boardTitle">幸运九宫格</span></div>
                    <div class="hint">概率实时展示 · 十连更划算</div>
                </div>
                <div class="panel-body" id="poolList">
                    <div class="empty-tip">加载中...</div>
                </div>
            </div>
            <aside class="panel">
                <div class="panel-head">
                    <div class="title"><i class="fas fa-list-ul"></i>最近抽奖记录</div>
                </div>
                <div class="panel-body">
                    <div class="log-list" id="drawLogList"><div class="empty-tip">加载中...</div></div>
                </div>
            </aside>
        </div>

        <div class="panel" style="margin-top:16px;">
            <div class="panel-head">
                <div class="title"><i class="fas fa-ticket"></i>抽奖券购买</div>
                <div class="hint">先购买抽奖券，再参与抽奖</div>
            </div>
            <div class="panel-body">
                <div id="lotteryGoodsList" class="shop-grid">
                    <div class="empty-tip">加载抽奖券中...</div>
                </div>
            </div>
        </div>
    </div>

    <div id="drawResultModal" class="draw-result-modal">
        <div class="draw-result-card">
            <div class="modal-head">
                <div class="title">抽奖结果</div>
                <button type="button" class="soft-btn" onclick="closeDrawModal()">关闭</button>
            </div>
            <div id="drawResultContent" class="result-wrap text-sm">加载中...</div>
        </div>
    </div>

    <div id="lotteryTip" class="lottery-tip"></div>

    <div id="ordersModal" class="order-modal">
        <div class="order-modal-card">
            <div class="modal-head">
                <div class="title">兑换订单记录</div>
                <button type="button" class="soft-btn" onclick="closeOrdersModal()">关闭</button>
            </div>
            <div id="ordersModalList">加载中...</div>
        </div>
    </div>

    <script>
        const PITY_CAP = 29;
        const THEME_KEY = 'lottery-theme';
        const MODE_KEY = 'lottery-mode';
        const THEMES = ['aurora', 'midnight', 'gold', 'candy', 'ocean'];
        const MODES = ['grid', 'wheel', 'cards', 'slot', 'scratch'];
        const MODE_TITLES = { grid: '幸运九宫格', wheel: '幸运转盘', cards: '翻牌翻翻乐', slot: '好运老虎机', scratch: '刮刮乐' };

        const lotteryState = {
            theme: 'aurora',
            mode: 'grid',
            pools: [],
            poolItems: {},
            pity: {},
            logs: [],
            meta: {},
            board: {}
        };
        let latestOrders = [];

        function getResultData(resp) { return resp && (resp.result || resp.data) ? (resp.result || resp.data) : {}; }
        function esc(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        function api(path, opts = {}) {
            const fetcher = window.taskApiFetch || window.fetch;
            const tokenNode = document.querySelector('meta[name="csrf-token"]');
            const csrf = tokenNode ? tokenNode.getAttribute('content') : '';
            const options = Object.assign({ method: 'GET' }, opts);
            options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
            if (options.method !== 'GET') options.headers['X-CSRF-TOKEN'] = csrf;
            return fetcher('/api/v2' + path, options).then(r => r.json());
        }
        function requestDraw(poolId, times) {
            return api('/point-mall/lottery/draw', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pool_id: Number(poolId), times: Number(times) || 1 })
            });
        }
        function rarityClass(rarity) {
            const value = String(rarity || 'N').toLowerCase();
            if (value === 'ssr') return 'rarity-ssr';
            if (value === 'sr') return 'rarity-sr';
            if (value === 'r') return 'rarity-r';
            return 'rarity-n';
        }
        function findRarity(poolId, rewardName) {
            const items = (lotteryState.meta[poolId] && lotteryState.meta[poolId].items) || [];
            const name = String(rewardName || '').trim();
            const hit = items.find(it => String(it.reward_name || '').trim() === name);
            return hit ? String(hit.rarity || 'N') : 'N';
        }
        function firstOf(result) {
            return Array.isArray(result && result.results) ? (result.results[0] || {}) : (result || {});
        }

        function getState(poolId) {
            const key = String(poolId);
            if (!lotteryState.board[key]) lotteryState.board[key] = { locked: false, rotation: 0 };
            return lotteryState.board[key];
        }
        function resetPoolState(poolId) {
            const key = String(poolId);
            const st = lotteryState.board[key];
            if (st) {
                if (st.timer) clearInterval(st.timer);
                if (st._wheelRaf) cancelAnimationFrame(st._wheelRaf);
                if (st._slotTimers) st._slotTimers.forEach(t => clearInterval(t));
            }
            lotteryState.board[key] = { locked: false, rotation: 0 };
        }
        function stopAllAnimations() {
            Object.keys(lotteryState.board).forEach(k => resetPoolState(k));
        }

        function applyTheme(theme) {
            if (THEMES.indexOf(theme) < 0) theme = 'aurora';
            lotteryState.theme = theme;
            const app = document.getElementById('lotteryApp');
            if (app) app.setAttribute('data-theme', theme);
            document.querySelectorAll('#themeSwitch .theme-dot').forEach(btn => {
                btn.classList.toggle('is-active', btn.getAttribute('data-theme') === theme);
            });
            try { localStorage.setItem(THEME_KEY, theme); } catch (e) {}
        }

        function applyMode(mode) {
            if (MODES.indexOf(mode) < 0) mode = 'grid';
            stopAllAnimations();
            lotteryState.mode = mode;
            document.querySelectorAll('#modeSwitch .mode-chip').forEach(btn => {
                btn.classList.toggle('is-active', btn.getAttribute('data-mode') === mode);
            });
            const title = document.getElementById('boardTitle');
            if (title) title.textContent = MODE_TITLES[mode] || '抽奖';
            try { localStorage.setItem(MODE_KEY, mode); } catch (e) {}
            renderPools();
            afterRenderPools();
        }

        function initPrefs() {
            let theme = 'aurora';
            let mode = 'grid';
            try {
                theme = localStorage.getItem(THEME_KEY) || 'aurora';
                mode = localStorage.getItem(MODE_KEY) || 'grid';
            } catch (e) {}
            applyTheme(theme);
            if (MODES.indexOf(mode) < 0) mode = 'grid';
            lotteryState.mode = mode;
            document.querySelectorAll('#modeSwitch .mode-chip').forEach(btn => {
                btn.classList.toggle('is-active', btn.getAttribute('data-mode') === mode);
            });
            const title = document.getElementById('boardTitle');
            if (title) title.textContent = MODE_TITLES[mode] || '抽奖';
        }

        function renderStats() {
            const pools = lotteryState.pools;
            const logs = lotteryState.logs;
            const pityMap = lotteryState.pity;
            const maxPity = pools.reduce((m, p) => Math.max(m, Number(pityMap[p.id] || 0)), 0);
            document.getElementById('lotteryStats').innerHTML = `
                <div class="stat-card"><div class="label"><i class="fas fa-layer-group"></i>奖池</div><div class="value">${pools.length}</div></div>
                <div class="stat-card"><div class="label"><i class="fas fa-shield-halved"></i>保底进度</div><div class="value">${Math.min(PITY_CAP, maxPity)}<small style="font-size:12px;">/${PITY_CAP}</small></div></div>
                <div class="stat-card"><div class="label"><i class="fas fa-clock-rotate-left"></i>最近记录</div><div class="value">${logs.length}</div></div>
            `;
        }

        function renderLogs() {
            const node = document.getElementById('drawLogList');
            const logs = lotteryState.logs;
            if (!logs.length) { node.innerHTML = '<div class="empty-tip">暂无记录</div>'; return; }
            node.innerHTML = logs.map(l => {
                let resultName = '-';
                try {
                    const payload = JSON.parse(l.result_payload || '{}');
                    resultName = payload.reward_name || '-';
                } catch (e) {}
                return `<div class="log-item"><div class="name">${esc(resultName)}</div><div class="time">${esc(l.created_at)}</div></div>`;
            }).join('');
        }

        function buildPityHint(progress) {
            const v = Math.min(PITY_CAP, Number(progress || 0));
            const left = Math.max(0, PITY_CAP + 1 - v);
            if (left <= 1) return '下一抽触发保底，必出非 AP 奖励';
            if (left <= 5) return `保底临近，还差 ${left} 次，建议攒一波十连`;
            return `距离保底还差 ${left} 次，继续冲`;
        }

        function renderPools() {
            const poolList = document.getElementById('poolList');
            const pools = lotteryState.pools;
            if (!pools.length) { poolList.innerHTML = '<div class="empty-tip">暂无奖池</div>'; return; }
            poolList.innerHTML = pools.map(p => {
                resetPoolState(p.id);
                const pity = Math.min(PITY_CAP, Number(lotteryState.pity[p.id] || 0));
                const percent = Math.round((pity / PITY_CAP) * 100);
                const items = lotteryState.poolItems[p.id] || [];
                lotteryState.meta[p.id] = { items: items, rewardNames: items.map(it => String(it.reward_name || '')) };
                return `
                    <div class="pool-shell">
                        <div class="lottery-fx" id="lottery-fx-${p.id}"></div>
                        <div class="pool-top">
                            <div>
                                <div class="pool-name"><i class="fas fa-crown"></i>${esc(p.name || '幸运奖池')}</div>
                                <div class="pool-desc">${esc(p.description || '')}</div>
                            </div>
                            <div class="pool-actions">
                                <span class="pool-pity-label">保底 ${pity}/${PITY_CAP}</span>
                                <button type="button" class="ten-btn" onclick="tenDraw(${p.id})">十连抽</button>
                            </div>
                        </div>
                        <div class="pity-track"><div class="pity-fill" style="width:${percent}%"></div></div>
                        <div class="pity-hint">${buildPityHint(pity)}</div>
                        <div>${buildModeBoard(p.id, items, Number(p.cost_ap || 0), pity)}</div>
                    </div>
                `;
            }).join('');
        }

        function buildModeBoard(poolId, items, costAp, pity) {
            switch (lotteryState.mode) {
                case 'wheel': return buildWheel(poolId, items, costAp, pity);
                case 'cards': return buildCards(poolId);
                case 'slot': return buildSlot(poolId, items);
                case 'scratch': return buildScratch(poolId);
                default: return buildGrid(poolId, items, costAp, pity);
            }
        }

        /* ---------------- 九宫格 ---------------- */
        function buildGrid(poolId, items, costAp, pity) {
            const cells = [];
            for (let i = 0; i < 8; i++) {
                if (!items.length) {
                    cells.push(`<div class="lc-cell" id="lottery-cell-${poolId}-${i}" data-board-cell="${poolId}" data-cell-idx="${i}"><div class="lc-title">待配置</div><div class="lc-meta"><span class="rarity-badge rarity-n">N</span><span class="lc-prob">0.00%</span></div></div>`);
                    continue;
                }
                const item = items[i % items.length];
                cells.push(`
                    <div class="lc-cell" id="lottery-cell-${poolId}-${i}" data-board-cell="${poolId}" data-cell-idx="${i}">
                        <div class="lc-title">${esc(item.reward_name || '未知奖励')}</div>
                        <div class="lc-meta">
                            <span class="rarity-badge ${rarityClass(item.rarity)}">${esc(item.rarity || 'N')}</span>
                            <span class="lc-prob">${Number(item.probability || 0).toFixed(1)}%</span>
                        </div>
                    </div>
                `);
            }
            cells.splice(4, 0, `
                <div class="lc-cell lc-center">
                    <div class="cost">${Number(costAp || 0)} AP / 次</div>
                    <button type="button" class="play-btn" onclick="drawGrid(${poolId}, 1)">立即抽奖</button>
                    <div class="pity-mini">保底 ${Math.min(PITY_CAP, Number(pity || 0))}/${PITY_CAP}</div>
                </div>
            `);
            return `<div class="lucky-board">${cells.join('')}</div>`;
        }

        function drawGrid(poolId, times) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            startGridPending(poolId);
            showTip('抽奖中，请稍候...');
            requestDraw(poolId, times).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    stopGrid(poolId); st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                settleGrid(poolId, firstOf(result).reward_name, () => finishDraw(poolId, result));
            }).catch(() => {
                stopGrid(poolId); st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }
        function startGridPending(poolId) {
            const st = getState(poolId);
            let idx = st.current >= 0 ? st.current : 0;
            st.timer = setInterval(() => {
                idx = (idx + 1) % 8;
                st.current = idx;
                activateGridCell(poolId, idx, false);
            }, 80);
        }
        function stopGrid(poolId) {
            const st = getState(poolId);
            if (st.timer) { clearInterval(st.timer); st.timer = null; }
            clearGridHighlight(poolId);
        }
        function clearGridHighlight(poolId) {
            document.querySelectorAll(`[data-board-cell="${poolId}"]`).forEach(el => {
                el.classList.remove('is-active'); el.classList.remove('is-win');
            });
        }
        function activateGridCell(poolId, idx, isWin) {
            clearGridHighlight(poolId);
            const node = document.getElementById(`lottery-cell-${poolId}-${idx}`);
            if (!node) return;
            node.classList.add('is-active');
            if (isWin) node.classList.add('is-win');
        }
        function resolveTargetIndex(poolId, rewardName) {
            const names = (lotteryState.meta[poolId] && lotteryState.meta[poolId].rewardNames) || [];
            if (!names.length) return Math.floor(Math.random() * 8);
            const target = String(rewardName || '').trim();
            if (!target) return Math.floor(Math.random() * names.length);
            const exact = names.findIndex(n => String(n || '').trim() === target);
            if (exact >= 0) return exact;
            const fuzzy = names.findIndex(n => String(n || '').indexOf(target) >= 0 || target.indexOf(String(n || '')) >= 0);
            return fuzzy >= 0 ? fuzzy : Math.floor(Math.random() * names.length);
        }
        function settleGrid(poolId, rewardName, onDone) {
            const st = getState(poolId);
            if (st.timer) { clearInterval(st.timer); st.timer = null; }
            const targetIdx = resolveTargetIndex(poolId, rewardName);
            let current = st.current >= 0 ? st.current : 0;
            let steps = 18 + ((targetIdx - current + 8) % 8);
            let delay = 70;
            const tick = () => {
                current = (current + 1) % 8;
                st.current = current;
                steps--;
                const isFinal = steps <= 0 && current === targetIdx;
                activateGridCell(poolId, current, isFinal);
                if (isFinal) { if (typeof onDone === 'function') onDone(); return; }
                delay = Math.min(210, delay + 7);
                setTimeout(tick, delay);
            };
            setTimeout(tick, 60);
        }

        /* ---------------- 大转盘 ---------------- */
        const WHEEL_PALETTE = ['#34d399', '#22d3ee', '#818cf8', '#f472b6', '#fbbf24', '#fb7185', '#4ade80', '#60a5fa'];
        function buildWheel(poolId, items, costAp, pity) {
            const list = items.length ? items.slice(0, 8) : [{ reward_name: '待配置', rarity: 'N' }];
            const n = list.length;
            const seg = 360 / n;
            const stops = list.map((it, i) => `${WHEEL_PALETTE[i % WHEEL_PALETTE.length]} ${i * seg}deg ${(i + 1) * seg}deg`).join(', ');
            const labels = list.map((it, i) => {
                const mid = i * seg + seg / 2;
                return `<div class="wheel-label-rot" style="transform:rotate(${mid}deg)"><span>${esc(it.reward_name || '奖励')}</span></div>`;
            }).join('');
            return `
                <div class="wheel-stage">
                    <div class="wheel-wrap">
                        <div class="wheel-pointer"></div>
                        <div class="wheel-disc" id="wheel-disc-${poolId}" style="background:conic-gradient(from 0deg, ${stops});"></div>
                        <div class="wheel-labels">${labels}</div>
                        <button type="button" class="wheel-hub" onclick="drawWheel(${poolId}, 1)">抽</button>
                    </div>
                </div>
                <div class="stage-hint">${Number(costAp || 0)} AP / 次 · 保底 ${Math.min(PITY_CAP, Number(pity || 0))}/${PITY_CAP}</div>
            `;
        }
        function setWheelRotation(poolId, deg) {
            const disc = document.getElementById(`wheel-disc-${poolId}`);
            if (disc) disc.style.transform = `rotate(${deg}deg)`;
        }
        function startWheelPending(poolId) {
            const st = getState(poolId);
            st._wheelPending = true;
            st._wheelLast = performance.now();
            const loop = (now) => {
                if (!st._wheelPending) return;
                const dt = Math.min(60, now - st._wheelLast);
                st._wheelLast = now;
                st.rotation = (st.rotation || 0) + dt * 0.55;
                setWheelRotation(poolId, st.rotation);
                st._wheelRaf = requestAnimationFrame(loop);
            };
            st._wheelRaf = requestAnimationFrame(loop);
        }
        function stopWheelPending(poolId) {
            const st = getState(poolId);
            st._wheelPending = false;
            if (st._wheelRaf) cancelAnimationFrame(st._wheelRaf);
        }
        function settleWheel(poolId, sectorIndex, onDone) {
            const st = getState(poolId);
            stopWheelPending(poolId);
            const items = (lotteryState.meta[poolId] && lotteryState.meta[poolId].items) || [];
            const n = Math.max(1, Math.min(8, items.length || 1));
            const seg = 360 / n;
            const sector = ((sectorIndex % n) + n) % n;
            const center = sector * seg + seg / 2;
            const targetMod = ((360 - center) % 360 + 360) % 360;
            const start = st.rotation || 0;
            const startMod = ((start % 360) + 360) % 360;
            const delta = (targetMod - startMod + 360) % 360;
            const total = start + 360 * 4 + delta;
            const duration = 2600;
            const t0 = performance.now();
            const frame = (now) => {
                const t = Math.min(1, (now - t0) / duration);
                const ease = 1 - Math.pow(1 - t, 3);
                const rot = start + (total - start) * ease;
                setWheelRotation(poolId, rot);
                if (t < 1) { st._wheelRaf = requestAnimationFrame(frame); }
                else { st.rotation = total; if (onDone) onDone(); }
            };
            st._wheelRaf = requestAnimationFrame(frame);
        }
        function drawWheel(poolId, times) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            startWheelPending(poolId);
            showTip('转盘转动中...');
            requestDraw(poolId, times).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    stopWheelPending(poolId); st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                settleWheel(poolId, pickWheelIndex(poolId, firstOf(result).reward_name), () => finishDraw(poolId, result));
            }).catch(() => {
                stopWheelPending(poolId); st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }
        function pickWheelIndex(poolId, rewardName) {
            const items = (lotteryState.meta[poolId] && lotteryState.meta[poolId].items) || [];
            const cap = Math.min(8, items.length || 1);
            const name = String(rewardName || '').trim();
            const exact = items.slice(0, cap).findIndex(it => String(it.reward_name || '').trim() === name);
            if (exact >= 0) return exact;
            const fuzzy = items.slice(0, cap).findIndex(it => String(it.reward_name || '').indexOf(name) >= 0 || name.indexOf(String(it.reward_name || '')) >= 0);
            return fuzzy >= 0 ? fuzzy : Math.floor(Math.random() * cap);
        }

        /* ---------------- 翻牌 ---------------- */
        function buildCards(poolId) {
            let cards = '';
            for (let i = 0; i < 10; i++) {
                cards += `
                    <div class="flip-card" id="flip-${poolId}-${i}" onclick="onCardClick(${poolId}, ${i})">
                        <div class="flip-inner">
                            <div class="flip-face flip-down"><i class="fas fa-gift"></i></div>
                            <div class="flip-face flip-up" id="flip-up-${poolId}-${i}"><span class="flip-name">-</span></div>
                        </div>
                    </div>`;
            }
            return `<div class="stage-hint" style="text-align:center;margin-top:14px;">点击任意卡牌翻开 · 或使用上方「十连抽」</div><div class="card-grid">${cards}</div>`;
        }
        function onCardClick(poolId, index) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            showTip('抽奖中，请稍候...');
            requestDraw(poolId, 1).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                revealCard(poolId, index, result.reward_name);
                setTimeout(() => finishDraw(poolId, result), 650);
            }).catch(() => {
                st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }
        function drawCardsTen(poolId) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            showTip('抽奖中，请稍候...');
            requestDraw(poolId, 10).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                const results = result.results || [];
                let i = 0;
                const flipNext = () => {
                    if (i >= results.length || i >= 10) {
                        setTimeout(() => finishDraw(poolId, result), 550);
                        return;
                    }
                    revealCard(poolId, i, results[i].reward_name);
                    playWinSound(1);
                    i++;
                    setTimeout(flipNext, 300);
                };
                flipNext();
            }).catch(() => {
                st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }
        function revealCard(poolId, index, rewardName) {
            const card = document.getElementById(`flip-${poolId}-${index}`);
            const up = document.getElementById(`flip-up-${poolId}-${index}`);
            if (up) up.innerHTML = `<span class="flip-name">${esc(rewardName || '奖励')}</span>`;
            if (card) card.classList.add('is-flipped');
        }

        /* ---------------- 老虎机 ---------------- */
        function buildSlot(poolId, items) {
            const names = items.length ? items.map(it => String(it.reward_name || '奖励')) : ['?'];
            const initial = names[0] || '?';
            const reels = [0, 1, 2].map(i =>
                `<div class="slot-reel" id="slot-reel-${poolId}-${i}">${esc(initial)}</div>`
            ).join('');
            return `<div class="slot-stage">${reels}</div><div class="stage-hint">三连一致好运加倍 · 点击下方开始</div>
                <div style="text-align:center;margin-top:12px;"><button type="button" class="play-btn" onclick="drawSlot(${poolId}, 1)">开始转动</button></div>`;
        }
        function startSlot(poolId) {
            const st = getState(poolId);
            const names = (lotteryState.meta[poolId] && lotteryState.meta[poolId].rewardNames) || ['?'];
            st._slotTimers = [0, 1, 2].map(i => {
                const el = document.getElementById(`slot-reel-${poolId}-${i}`);
                if (el) el.classList.add('is-spinning');
                return setInterval(() => {
                    if (el) el.textContent = names[Math.floor(Math.random() * names.length)] || '?';
                }, 70 + i * 20);
            });
        }
        function stopSlot(poolId) {
            const st = getState(poolId);
            if (st._slotTimers) {
                st._slotTimers.forEach(t => clearInterval(t));
                st._slotTimers = null;
            }
            [0, 1, 2].forEach(i => {
                const el = document.getElementById(`slot-reel-${poolId}-${i}`);
                if (el) el.classList.remove('is-spinning');
            });
        }
        function settleSlot(poolId, rewardName, onDone) {
            stopSlot(poolId);
            const name = rewardName || '奖励';
            [0, 1, 2].forEach((i, idx) => {
                setTimeout(() => {
                    const el = document.getElementById(`slot-reel-${poolId}-${i}`);
                    if (el) {
                        el.textContent = name;
                        el.classList.add('is-settled');
                        setTimeout(() => el.classList.remove('is-settled'), 500);
                    }
                    if (idx === 2 && typeof onDone === 'function') onDone();
                }, 260 + idx * 420);
            });
        }
        function drawSlot(poolId, times) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            startSlot(poolId);
            showTip('老虎机转动中...');
            requestDraw(poolId, times).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    stopSlot(poolId); st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                settleSlot(poolId, firstOf(result).reward_name, () => finishDraw(poolId, result));
            }).catch(() => {
                stopSlot(poolId); st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }

        /* ---------------- 刮刮乐 ---------------- */
        function buildScratch(poolId) {
            return `
                <div class="scratch-stage">
                    <div class="scratch-wrap">
                        <div class="scratch-prize" id="scratch-prize-${poolId}"><span class="scratch-prize-sub">点击下方按钮开始刮奖</span></div>
                        <canvas class="scratch-canvas" id="scratch-canvas-${poolId}"></canvas>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="button" class="play-btn" onclick="drawScratch(${poolId}, 1)">开始刮奖</button>
                        <button type="button" class="soft-btn" onclick="completeScratch(${poolId})">一键刮开</button>
                    </div>
                </div>`;
        }
        function initScratchBoard(poolId) {
            const st = getState(poolId);
            if (!st.pendingResult) { st.scratchArmed = false; }
            const canvas = document.getElementById(`scratch-canvas-${poolId}`);
            if (!canvas) return;
            const wrap = canvas.parentElement;
            requestAnimationFrame(() => {
                const rect = wrap.getBoundingClientRect();
                canvas.width = Math.max(1, Math.floor(rect.width));
                canvas.height = Math.max(1, Math.floor(rect.height));
                paintScratchCover(poolId);
                if (!st._scratchBound) { bindScratch(canvas, poolId); st._scratchBound = true; }
            });
        }
        function paintScratchCover(poolId) {
            const canvas = document.getElementById(`scratch-canvas-${poolId}`);
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.globalCompositeOperation = 'source-over';
            const g = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
            g.addColorStop(0, '#94a3b8');
            g.addColorStop(1, '#475569');
            ctx.fillStyle = g;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'rgba(255,255,255,.9)';
            ctx.font = 'bold 15px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('刮开涂层查看奖励', canvas.width / 2, canvas.height / 2);
            getState(poolId)._scratchMoves = 0;
        }
        function bindScratch(canvas, poolId) {
            const st = getState(poolId);
            let drawing = false;
            const point = (e) => {
                const r = canvas.getBoundingClientRect();
                return { x: (e.clientX - r.left) * (canvas.width / r.width), y: (e.clientY - r.top) * (canvas.height / r.height) };
            };
            const erase = (e) => {
                if (!st.scratchArmed) return;
                const ctx = canvas.getContext('2d');
                ctx.globalCompositeOperation = 'destination-out';
                const p = point(e);
                ctx.beginPath();
                ctx.arc(p.x, p.y, 22, 0, Math.PI * 2);
                ctx.fill();
                st._scratchMoves = (st._scratchMoves || 0) + 1;
                if (st._scratchMoves % 6 === 0) checkScratchProgress(canvas, poolId);
            };
            canvas.addEventListener('pointerdown', e => {
                drawing = true;
                if (canvas.setPointerCapture) canvas.setPointerCapture(e.pointerId);
                erase(e);
            });
            canvas.addEventListener('pointermove', e => { if (drawing) erase(e); });
            canvas.addEventListener('pointerup', () => { drawing = false; checkScratchProgress(canvas, poolId); });
            canvas.addEventListener('pointerleave', () => { drawing = false; });
            canvas.addEventListener('pointercancel', () => { drawing = false; });
        }
        function checkScratchProgress(canvas, poolId) {
            const ctx = canvas.getContext('2d');
            const w = canvas.width, h = canvas.height;
            if (!w || !h) return;
            let clear = 0, total = 0;
            const data = ctx.getImageData(0, 0, w, h).data;
            for (let y = 0; y < h; y += 8) {
                for (let x = 0; x < w; x += 8) {
                    total++;
                    if (data[(y * w + x) * 4 + 3] < 64) clear++;
                }
            }
            if (total && clear / total > 0.5 && getState(poolId).pendingResult) completeScratch(poolId);
        }
        function drawScratch(poolId, times) {
            const st = getState(poolId);
            if (st.locked) return;
            st.locked = true;
            st.scratchArmed = false;
            showTip('抽奖中，请稍候...');
            requestDraw(poolId, times).then(resp => {
                if (!resp || Number(resp.code) !== 9999) {
                    st.locked = false;
                    showTip('抽奖失败：' + (resp && resp.msg ? resp.msg : '未知错误'), 'error');
                    return;
                }
                const result = getResultData(resp).draw_result || {};
                const first = firstOf(result);
                const prize = document.getElementById(`scratch-prize-${poolId}`);
                if (prize) {
                    const sub = Array.isArray(result.results) ? `<span class="scratch-prize-sub">共 ${result.results.length} 项奖励</span>` : '';
                    prize.innerHTML = `<span class="scratch-prize-name">${esc(first.reward_name || '奖励')}</span>${sub}`;
                }
                st.pendingResult = result;
                st.scratchArmed = true;
                paintScratchCover(poolId);
                showTip('刮开涂层查看奖励');
            }).catch(() => {
                st.locked = false;
                showTip('抽奖失败：网络异常', 'error');
            });
        }
        function completeScratch(poolId) {
            const st = getState(poolId);
            if (!st.pendingResult) { showTip('请先点击「开始刮奖」', 'error'); return; }
            const canvas = document.getElementById(`scratch-canvas-${poolId}`);
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.globalCompositeOperation = 'source-over';
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            const result = st.pendingResult;
            st.pendingResult = null;
            st.scratchArmed = false;
            st.locked = false;
            finishDraw(poolId, result);
        }

        /* ---------------- 公共开奖与结算 ---------------- */
        function tenDraw(poolId) {
            switch (lotteryState.mode) {
                case 'wheel': return drawWheel(poolId, 10);
                case 'cards': return drawCardsTen(poolId);
                case 'slot': return drawSlot(poolId, 10);
                case 'scratch': return drawScratch(poolId, 10);
                default: return drawGrid(poolId, 10);
            }
        }
        function finishDraw(poolId, result) {
            const st = getState(poolId);
            st.locked = false;
            triggerParticles(poolId);
            if (Array.isArray(result.results)) {
                playWinSound(2);
                showMultiResult(poolId, result.results);
                showTip('十连抽完成，共 ' + result.results.length + ' 项奖励');
            } else {
                playWinSound(1);
                showSingleResult(poolId, result);
                showTip('恭喜获得：' + (result.reward_name || '未知奖励'));
            }
            load();
        }

        function describeReward(result) {
            const payload = result && result.reward_payload ? result.reward_payload : {};
            if ((result.reward_type || '') === 'ap') {
                return `<div class="result-sub">奖励 ${Number(payload.ap || 0)} AP 已到账</div>`;
            }
            return `<div class="result-sub">奖励已发放至账户</div>`;
        }
        function showSingleResult(poolId, result) {
            const rarity = findRarity(poolId, result.reward_name);
            openDrawModal(`
                <div class="result-rarity ${rarityClass(rarity)}">${esc(rarity)}</div>
                <div class="result-name">${esc(result.reward_name || '未知奖励')}</div>
                ${describeReward(result)}
            `);
        }
        function showMultiResult(poolId, results) {
            const chips = results.map((r, idx) => {
                const rarity = findRarity(poolId, r.reward_name);
                return `
                <div class="result-chip">
                    <span><span class="idx">#${idx + 1}</span> ${esc(r.reward_name || '未知奖励')}</span>
                    <span class="rarity-badge ${rarityClass(rarity)}">${esc(rarity)}</span>
                </div>`;
            }).join('');
            openDrawModal(`
                <div class="result-name" style="font-size:20px;">十连抽完成</div>
                <div class="result-sub">共获得 ${results.length} 项奖励</div>
                <div class="result-list">${chips}</div>
            `);
        }

        function renderLotteryGoods(goods) {
            const node = document.getElementById('lotteryGoodsList');
            if (!goods || !goods.length) { node.innerHTML = '<div class="empty-tip">暂无可购买抽奖券</div>'; return; }
            node.innerHTML = goods.map(g => {
                const stock = Number(g.stock);
                const stockText = (!isNaN(stock) && stock >= 0) ? `库存 ${stock}` : '无限库存';
                return `
                    <div class="shop-card">
                        <div class="name">${esc(g.name || '抽奖券')}</div>
                        <div class="desc">${esc(g.description || '')}</div>
                        <div class="desc" style="flex:0;">${stockText}</div>
                        <div class="foot">
                            <div class="price-tag">${Number(g.point_cost || 0)}<small> AP</small></div>
                            <button type="button" class="ten-btn" onclick="buyLotteryGoods(${Number(g.id || 0)})">购买</button>
                        </div>
                    </div>`;
            }).join('');
        }

        function renderOrdersList(orders) {
            const node = document.getElementById('ordersModalList');
            if (!orders || !orders.length) { node.innerHTML = '<div class="empty-tip">暂无订单</div>'; return; }
            node.innerHTML = orders.map(o => {
                const goodsName = o.goods_snapshot && o.goods_snapshot.name ? o.goods_snapshot.name : ('商品#' + o.goods_id);
                const fulfilled = o.delivery_status === 'fulfilled';
                return `
                    <div class="order-item">
                        <div class="row">
                            <div class="gname">${esc(goodsName)}</div>
                            <div class="time">${esc(o.created_at || '')}</div>
                        </div>
                        <div class="meta">订单号：${esc(o.order_no)}</div>
                        <div class="meta">数量 ${esc(o.quantity)} · 消费 ${esc(o.point_cost_total)} AP · 状态
                            <span style="font-weight:700;color:${fulfilled ? 'var(--primary-2)' : '#d97706'};">${esc(o.delivery_status || '-')}</span>
                        </div>
                    </div>`;
            }).join('');
        }

        function openOrdersModal() {
            renderOrdersList(latestOrders);
            document.getElementById('ordersModal').classList.add('show');
        }
        function closeOrdersModal() { document.getElementById('ordersModal').classList.remove('show'); }

        async function loadLotteryGoods() {
            const resp = await api('/point-mall/goods?scene=lottery');
            if (!resp || Number(resp.code) !== 9999) { renderLotteryGoods([]); return; }
            renderLotteryGoods(getResultData(resp).goods || []);
        }
        async function loadOrders() {
            const resp = await api('/point-mall/orders?page_count=30');
            if (!resp || Number(resp.code) !== 9999) { latestOrders = []; return; }
            latestOrders = getResultData(resp).orders || [];
        }
        async function buyLotteryGoods(goodsId) {
            const qtyText = window.prompt('请输入购买数量', '1');
            if (qtyText === null) return;
            const quantity = Number(qtyText || 0);
            if (!quantity || quantity <= 0) { showTip('购买数量不合法', 'error'); return; }
            const resp = await api('/point-mall/purchase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ goods_id: Number(goodsId), quantity: quantity })
            });
            if (!resp || Number(resp.code) !== 9999) { showTip(resp && resp.msg ? resp.msg : '购买失败', 'error'); return; }
            showTip('购买成功，抽奖券已到账');
            await loadOrders();
        }

        function afterRenderPools() {
            lotteryState.pools.forEach(p => {
                if (lotteryState.mode === 'scratch') initScratchBoard(p.id);
            });
        }

        function load() {
            api('/point-mall/lottery/overview').then(resp => {
                if (!resp || Number(resp.code) !== 9999) { showTip(resp && resp.msg ? resp.msg : '加载失败', 'error'); return; }
                const data = getResultData(resp);
                lotteryState.pools = data.pools || [];
                lotteryState.poolItems = data.pool_items || {};
                lotteryState.pity = data.pity || {};
                lotteryState.logs = data.logs || [];
                renderStats();
                renderPools();
                renderLogs();
                afterRenderPools();
            });
        }

        function openDrawModal(contentHtml) {
            document.getElementById('drawResultContent').innerHTML = contentHtml;
            document.getElementById('drawResultModal').classList.add('show');
        }
        function closeDrawModal() { document.getElementById('drawResultModal').classList.remove('show'); }

        function isSoundEnabled() {
            const node = document.getElementById('soundEnabled');
            return !!(node && node.checked);
        }
        function playWinSound(level) {
            if (!isSoundEnabled()) return;
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            const ctx = new AC();
            const now = ctx.currentTime;
            const notes = level > 1 ? [523.25, 659.25, 783.99] : [523.25, 659.25];
            notes.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, now + idx * 0.08);
                gain.gain.exponentialRampToValueAtTime(0.06, now + idx * 0.08 + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.08 + 0.16);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + idx * 0.08);
                osc.stop(now + idx * 0.08 + 0.18);
            });
        }
        function triggerParticles(poolId) {
            const wrap = document.getElementById(`lottery-fx-${poolId}`);
            if (!wrap) return;
            wrap.innerHTML = '';
            for (let i = 0; i < 20; i++) {
                const el = document.createElement('span');
                el.className = 'spark';
                el.style.left = `${45 + (Math.random() * 10)}%`;
                el.style.top = `${40 + (Math.random() * 16)}%`;
                el.style.setProperty('--tx', `${(Math.random() - 0.5) * 240}px`);
                el.style.setProperty('--ty', `${(Math.random() - 0.5) * 200}px`);
                wrap.appendChild(el);
            }
            setTimeout(() => { wrap.innerHTML = ''; }, 1150);
        }
        function showTip(message, type) {
            const node = document.getElementById('lotteryTip');
            if (!node) return;
            node.classList.remove('error');
            if (type === 'error') node.classList.add('error');
            node.textContent = message;
            node.classList.add('show');
            if (node._timer) clearTimeout(node._timer);
            node._timer = setTimeout(() => { node.classList.remove('show'); }, type === 'error' ? 2600 : 1900);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initPrefs();
            load();
            loadLotteryGoods();
            loadOrders();
        });
    </script>
@endsection
