<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Code de pointage — OPTIMIZE</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: #1e293b; margin: 0; padding: 2rem; text-align: center; }
        .title { font-size: 2rem; font-weight: 800; letter-spacing: 2px; margin: 0; color: #1e293b; }
        .subtitle { color: #64748b; font-size: 1rem; margin-top: .25rem; }
        .qr-frame {
            display: inline-block;
            border: 4px solid #1e293b;
            border-radius: 16px;
            padding: 1.5rem;
            background: #fff;
            margin: 2rem 0;
        }
        .qr-frame img { display: block; margin: 0 auto; }
        .instructions {
            max-width: 540px;
            margin: 1rem auto;
            background: #F1F5F9;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            text-align: left;
            font-size: 1rem;
        }
        .instructions h3 {
            margin-top: 0;
            color: #1e293b;
            font-size: 1.1rem;
            border-bottom: 2px solid #0EA5E9;
            padding-bottom: .35rem;
        }
        .instructions ol { padding-left: 1.4rem; margin: 1rem 0 0; }
        .instructions ol li { margin-bottom: .5rem; line-height: 1.5; }
        .footer { color: #94a3b8; font-size: .8rem; margin-top: 2rem; }
        .badge-points { display: inline-block; background: #0EA5E9; color: #fff; padding: .25rem .75rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .no-print { margin-top: 1rem; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <h1 class="title">POINTAGE</h1>
    <div class="subtitle">Scannez avec votre téléphone — pointage entrée / sortie</div>

    <div class="qr-frame">
        <img src="{{ route('rh.pointages.qr-generique.image') }}" alt="QR Code" width="360" height="360">
    </div>

    <div class="instructions">
        <h3>🛈 Mode d'emploi</h3>
        <ol>
            <li>Ouvrez votre <strong>appareil photo</strong> et pointez-le vers le QR ci-dessus.</li>
            <li>Touchez la notification qui apparaît pour ouvrir la page.</li>
            <li>Saisissez votre <strong>matricule</strong> (ex. EMP-042) et votre <strong>PIN à 4-6 chiffres</strong>.</li>
            <li>Validez : votre pointage <span class="badge-points">entrée</span> ou <span class="badge-points">sortie</span> est enregistré.</li>
        </ol>
        <p style="color:#dc2626;font-size:.9rem;margin-top:1rem;margin-bottom:0;">
            ⚠️ Votre PIN est strictement personnel. Ne le partagez avec personne.
            En cas d'oubli, contactez la DRH.
        </p>
    </div>

    <div class="footer">
        OPTIMIZE ERP — Yubile Technologie · Affiché le {{ now()->translatedFormat('d M Y') }}
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:.6rem 1.4rem;background:#0891B2;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:1rem;font-weight:600;">
            🖨️ Imprimer cette page
        </button>
    </div>
</body>
</html>
