<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Pointage — {{ $employee->noms }} {{ $employee->prenoms }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; color: #1e293b; margin: 0; padding: 2rem; text-align: center; }
        h1 { font-size: 1.5rem; margin: 0 0 .25rem 0; }
        .sub { color: #64748b; font-size: .9rem; margin-bottom: 2rem; }
        .qr-card { display: inline-block; border: 2px dashed #cbd5e1; padding: 1.5rem; border-radius: 12px; }
        .qr-card img { display: block; margin: 0 auto; }
        .employee-block { margin-top: 1rem; font-size: 1.1rem; }
        .employee-block .name { font-weight: 700; color: #1e293b; }
        .employee-block .matricule { color: #64748b; font-family: monospace; margin-top: .25rem; }
        .instructions { max-width: 480px; margin: 2rem auto; text-align: left; color: #475569; font-size: .9rem; line-height: 1.6; }
        .instructions h3 { color: #1e293b; font-size: .95rem; margin-bottom: .5rem; }
        .instructions ol { padding-left: 1.2rem; }
        .footer { margin-top: 2rem; color: #94a3b8; font-size: .75rem; }
        .no-print { margin-top: 1.5rem; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>Pointage par QR Code</h1>
    <div class="sub">OPTIMIZE ERP — Yubile Technologie</div>

    <div class="qr-card">
        <img src="{{ route('pointage.qr.image', $employee) }}" alt="QR Code" width="340" height="340">
        <div class="employee-block">
            <div class="name">{{ $employee->noms }} {{ $employee->prenoms }}</div>
            <div class="matricule">{{ $employee->matricule ?? '—' }}</div>
        </div>
    </div>

    <div class="instructions">
        <h3>Mode d'emploi</h3>
        <ol>
            <li>Ouvrez l'application appareil photo de votre smartphone.</li>
            <li>Pointez l'objectif sur ce QR code.</li>
            <li>Touchez la notification qui apparaît pour ouvrir la page.</li>
            <li>Cliquez sur « Confirmer mon pointage » pour enregistrer votre arrivée ou votre départ.</li>
        </ol>
        <p style="color:#dc2626;font-size:.85rem;">
            Ce QR est strictement personnel. Ne le partagez pas.
            En cas de perte, demandez sa régénération à la DRH.
        </p>
    </div>

    <div class="footer">
        Généré le {{ now()->translatedFormat('d M Y H:i') }} · URL : {{ $url }}
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:.6rem 1.2rem;background:#0891B2;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:1rem;">
            🖨️ Imprimer
        </button>
    </div>
</body>
</html>
