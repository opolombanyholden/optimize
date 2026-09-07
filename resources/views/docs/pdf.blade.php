<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $guide['title'] }} — OptimiZe</title>
    <style>
        @page { size: A4 portrait; margin: 22mm 18mm 25mm 18mm; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.55;
            color: #333333;
        }
        /* dompdf ne gère pas @font-face facilement — DejaVu est built-in, suffisant */

        /* Page de garde */
        .pdf-cover {
            page-break-after: always;
            padding: 60mm 10mm 20mm;
        }
        .pdf-cover .eyebrow {
            font-size: 8pt; font-weight: bold; letter-spacing: 2pt;
            color: {{ $guide['accent'] }}; text-transform: uppercase; margin-bottom: 10mm;
        }
        .pdf-cover h1 {
            font-size: 26pt; font-weight: bold; line-height: 1.15; color: #171717;
            margin: 0 0 4mm;
        }
        .pdf-cover .sub {
            font-size: 12pt; font-style: italic; color: #737373; margin: 0 0 20mm;
        }
        .pdf-cover .meta { border-top: 1pt solid #E5E5E0; padding-top: 6mm; }
        .pdf-cover .meta table { width: 100%; border-collapse: collapse; }
        .pdf-cover .meta td { padding: 2mm 0; font-size: 9pt; vertical-align: top; }
        .pdf-cover .meta td.lbl { color: #737373; text-transform: uppercase; font-size: 7pt; letter-spacing: 1pt; font-weight: bold; width: 35%; }

        /* Content */
        .pdf-body h2.chapter {
            font-size: 18pt; color: #171717; margin: 12mm 0 2mm; padding-top: 5mm;
            border-top: 1pt solid #E5E5E0; page-break-before: always; page-break-after: avoid;
        }
        .pdf-body h2.chapter .chapter-num {
            display: block; font-size: 8pt; color: {{ $guide['accent'] }};
            letter-spacing: 1.5pt; margin-bottom: 2mm; font-weight: bold;
        }
        .pdf-body .chapter-lead {
            font-size: 11pt; font-style: italic; color: #737373;
            margin: 0 0 8mm; page-break-after: avoid;
        }
        .pdf-body h3 {
            font-size: 12pt; color: #171717; margin: 6mm 0 2mm; page-break-after: avoid;
        }
        .pdf-body h4 { font-size: 10pt; color: #171717; margin: 4mm 0 1mm; page-break-after: avoid; }
        .pdf-body p { margin: 0 0 3mm; }
        .pdf-body strong { color: #171717; font-weight: bold; }

        /* Callouts */
        .pdf-body .callout {
            padding: 3mm 4mm; border-left: 3pt solid #737373;
            background: #F5F5F0; margin: 4mm 0;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        .pdf-body .callout-label {
            display: block; font-size: 7pt; font-weight: bold; letter-spacing: 1pt;
            text-transform: uppercase; margin-bottom: 1mm;
        }
        .pdf-body .callout-tip { border-color: {{ $guide['accent'] }}; background: {{ $guide['accent_soft'] }}; }
        .pdf-body .callout-tip .callout-label { color: {{ $guide['accent'] }}; }
        .pdf-body .callout-warn { border-color: #DC2626; background: #FEE2E2; }
        .pdf-body .callout-warn .callout-label { color: #DC2626; }
        .pdf-body .callout-info { border-color: #0A66C2; background: #DBEAFE; }
        .pdf-body .callout-info .callout-label { color: #0A66C2; }
        .pdf-body .callout-ok { border-color: #059669; background: #D1FAE5; }
        .pdf-body .callout-ok .callout-label { color: #059669; }

        /* Steps — table pour dompdf */
        .pdf-body table.steps { border-collapse: collapse; margin: 4mm 0 6mm; width: 100%; }
        .pdf-body table.steps .step-num {
            width: 8mm; vertical-align: top; text-align: center;
            font-size: 9pt; font-weight: bold; color: {{ $guide['accent'] }};
            padding-top: 1mm;
        }
        .pdf-body table.steps .step-body { padding: 0 0 4mm 3mm; vertical-align: top; }
        .pdf-body table.steps .step-title { font-weight: bold; color: #171717; font-size: 10pt; margin-bottom: 1mm; display: block; }

        /* Screen annots */
        .pdf-body table.screen-annots { border-collapse: collapse; width: 100%; margin: 3mm 0 0; }
        .pdf-body table.screen-annots td { padding: 1.5mm 0; vertical-align: top; font-size: 9pt; }
        .pdf-body table.screen-annots .num {
            width: 8mm; text-align: center;
            font-weight: bold; color: #FFFFFF;
        }
        .pdf-body table.screen-annots .num span {
            display: inline-block; background: #171717; padding: 0.5mm 2mm; border-radius: 8mm; font-size: 8pt;
        }

        /* Tables data */
        .pdf-body table.data { width: 100%; border-collapse: collapse; margin: 4mm 0; font-size: 9pt; }
        .pdf-body table.data th {
            background: #F5F5F0; padding: 2mm 3mm; text-align: left;
            border-bottom: 1pt solid #171717; font-size: 7pt; letter-spacing: 1pt;
            text-transform: uppercase; color: #737373;
        }
        .pdf-body table.data td { padding: 2mm 3mm; border-bottom: 0.5pt solid #E5E5E0; vertical-align: top; }
        .pdf-body table.data code { font-family: monospace; font-size: 8.5pt; background: #F0EFEA; padding: 0.5mm 1mm; }

        /* Flow (workflow horizontal) */
        .pdf-body table.flow { border-collapse: collapse; width: 100%; margin: 4mm 0 6mm; }
        .pdf-body table.flow td {
            border: 0.5pt solid #E5E5E0; padding: 3mm; background: #FFFFFF;
            font-size: 9pt; vertical-align: top; width: 25%;
        }
        .pdf-body table.flow .flow-num { font-size: 7pt; color: {{ $guide['accent'] }}; font-weight: bold; letter-spacing: 1pt; }
        .pdf-body table.flow .flow-name { font-size: 9pt; font-weight: bold; color: #171717; margin-top: 1mm; }
        .pdf-body table.flow .flow-who { font-size: 8pt; color: #737373; margin-top: 1mm; }

        /* Checklist */
        .pdf-body ul.checklist { list-style: none; padding: 0; margin: 3mm 0; }
        .pdf-body ul.checklist li {
            padding: 1.5mm 0 1.5mm 7mm; position: relative;
            border-bottom: 0.5pt dotted #E5E5E0; font-size: 9.5pt;
        }
        .pdf-body ul.checklist li:before {
            content: "☐"; position: absolute; left: 0; top: 1.5mm;
            font-size: 11pt; color: #A3A29E;
        }

        /* Screen mockups — maquettes HTML+CSS, dompdf-friendly */
        .pdf-body .screen { margin: 4mm 0 6mm; page-break-inside: avoid; }
        .pdf-body .screen-frame {
            border: 0.5pt solid #E5E5E0; padding: 1.5mm; background: #FFFFFF;
        }
        .pdf-body .screen-caption {
            font-size: 8pt; color: #737373; text-align: center; margin-top: 2mm; font-style: italic;
        }
        .pdf-body .screen-caption strong { color: #171717; font-style: normal; }

        /* ==== Maquettes ==== */
        .pdf-body .mock { padding: 0; }
        .pdf-body .mock-title { font-size: 8pt; font-weight: bold; color: #64748B; letter-spacing: 1pt; text-transform: uppercase; }

        /* Login */
        .pdf-body .mock-login { background: #0F172A; padding: 8mm 5mm; }
        .pdf-body .mock-login-card { background: #FFFFFF; padding: 6mm; width: 65mm; margin: 0 auto; }
        .pdf-body .mock-login-brand { font-size: 15pt; font-weight: bold; text-align: center; color: #0F172A; margin: 0 0 1mm; }
        .pdf-body .mock-login-sub { font-size: 7pt; text-align: center; color: #64748B; margin: 0 0 5mm; }
        .pdf-body table.mock-form { width: 100%; border-collapse: collapse; }
        .pdf-body table.mock-form td { padding: 1.5mm 0; vertical-align: top; }
        .pdf-body table.mock-form td.mock-annot { width: 8mm; padding-top: 5mm; }
        .pdf-body .mock-annot span {
            display: inline-block; background: #171717; color: #FFFFFF;
            font-size: 8pt; font-weight: bold; text-align: center;
            padding: 0.5mm 2mm; border-radius: 5mm;
        }
        .pdf-body .mock-label { font-size: 7pt; font-weight: bold; color: #334155; letter-spacing: 0.5pt; margin-bottom: 1mm; }
        .pdf-body .mock-input { background: #F8FAFC; border: 0.5pt solid #E2E8F0; padding: 2mm 3mm; font-size: 9pt; color: #0F172A; }
        .pdf-body .mock-btn-primary { background: #0A66C2; color: #FFFFFF; padding: 3mm; text-align: center; font-weight: bold; font-size: 9pt; letter-spacing: 0.5pt; }
        .pdf-body .mock-forgot { font-size: 8pt; color: #94A3B8; text-align: center; margin-top: 3mm; }

        /* Workspace picker */
        .pdf-body .mock-picker { background: #FFFFFF; padding: 5mm; border-left: 3pt solid #7C3AED; }
        .pdf-body .mock-picker-title { font-size: 11pt; font-weight: bold; color: #171717; margin: 0 0 1mm; }
        .pdf-body .mock-picker-sub { font-size: 8pt; color: #64748B; margin: 0 0 4mm; }
        .pdf-body .mock-picker-section { font-size: 7pt; font-weight: bold; color: #64748B; letter-spacing: 1pt; margin: 4mm 0 2mm; }
        .pdf-body table.mock-grid { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        .pdf-body table.mock-grid td { padding: 1mm; vertical-align: top; width: 33.33%; }
        .pdf-body .mock-card { background: #FFFFFF; border: 0.5pt solid #E0DFDC; padding: 3mm; }
        .pdf-body .mock-card-featured { border: 1.5pt solid #D97706; }
        .pdf-body .mock-card-name { font-size: 9pt; font-weight: bold; color: #171717; }
        .pdf-body .mock-card-featured .mock-card-name { color: #B45309; }
        .pdf-body .mock-card-desc { font-size: 7pt; color: #64748B; margin-top: 1mm; }

        /* Dashboard */
        .pdf-body .mock-dash { background: #F8FAFC; padding: 3mm; }
        .pdf-body .mock-dash-hd {
            background: #FFFFFF; border: 0.5pt solid #E5E7EB;
            padding: 3mm 4mm; margin-bottom: 2mm;
        }
        .pdf-body .mock-dash-hd table { width: 100%; border-collapse: collapse; }
        .pdf-body .mock-dash-hd td { vertical-align: middle; }
        .pdf-body .mock-dash-hd .title { font-size: 10pt; font-weight: bold; color: #0F172A; }
        .pdf-body .mock-dash-hd .sub { font-size: 7pt; color: #64748B; margin-top: 0.5mm; }
        .pdf-body .mock-dash-hd .cta { background: #D97706; color: #FFFFFF; padding: 1.5mm 3mm; font-size: 8pt; font-weight: bold; text-align: center; }

        .pdf-body table.mock-alerts { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        .pdf-body table.mock-alerts td { padding: 1mm; vertical-align: top; width: 33.33%; }
        .pdf-body .mock-alert {
            background: #FFFFFF; border: 0.5pt solid #E5E7EB; border-left: 2pt solid #DC2626;
            padding: 2mm 3mm;
        }
        .pdf-body .mock-alert.warn { border-left-color: #D97706; }
        .pdf-body .mock-alert-t { font-size: 8pt; font-weight: bold; color: #171717; }
        .pdf-body .mock-alert-s { font-size: 7pt; color: #64748B; margin-top: 0.5mm; }

        .pdf-body table.mock-kpis { width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
        .pdf-body table.mock-kpis td { padding: 1mm; vertical-align: top; width: 25%; }
        .pdf-body .mock-kpi { background: #FFFFFF; border: 0.5pt solid #E5E7EB; padding: 2.5mm 3mm; }
        .pdf-body .mock-kpi-l { font-size: 6.5pt; font-weight: bold; color: #64748B; letter-spacing: 0.8pt; }
        .pdf-body .mock-kpi-v { font-size: 14pt; font-weight: bold; color: #0F172A; margin: 1mm 0; }
        .pdf-body .mock-kpi-v small { font-size: 8pt; font-weight: normal; color: #64748B; }
        .pdf-body .mock-kpi-t { display: inline-block; background: #ECFDF5; color: #059669; padding: 0.3mm 1.5mm; font-size: 7pt; font-weight: bold; }
        .pdf-body .mock-kpi-s { font-size: 7pt; color: #64748B; }

        .pdf-body table.mock-charts { width: 100%; border-collapse: collapse; }
        .pdf-body table.mock-charts td { padding: 1mm; vertical-align: top; width: 50%; }
        .pdf-body .mock-chart { background: #FFFFFF; border: 0.5pt solid #E5E7EB; padding: 3mm; }
        .pdf-body .mock-chart-t { font-size: 8pt; font-weight: bold; color: #171717; margin-bottom: 3mm; }
        .pdf-body table.mock-chart-bars { width: 100%; border-collapse: collapse; }
        .pdf-body table.mock-chart-bars td { vertical-align: bottom; padding: 0 1pt; text-align: center; }
        .pdf-body .mock-bar { background: #D97706; margin: 0 auto; width: 5mm; }
        .pdf-body .mock-bar-cur { background: #0A66C2; }
        .pdf-body .mock-bar-lbl { font-size: 6.5pt; color: #64748B; margin-top: 1mm; }
        .pdf-body table.mock-top { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .pdf-body table.mock-top td { padding: 1.2mm 1mm; border-bottom: 0.3pt solid #F1F5F9; }
        .pdf-body table.mock-top .rank {
            width: 5mm; text-align: center; font-weight: bold; color: #B45309;
            background: #FEF3C7; border-radius: 5mm; padding: 0.3mm 0;
        }
        .pdf-body table.mock-top .amt { text-align: right; font-weight: bold; color: #0F172A; }

        /* Popup rupture */
        .pdf-body .mock-popup { background: #FFFFFF; border: 0.5pt solid #FCA5A5; }
        .pdf-body .mock-popup-hd {
            background: #FEF2F2; padding: 3mm 4mm;
            border-bottom: 0.5pt solid #FCA5A5;
        }
        .pdf-body .mock-popup-hd .t { font-size: 11pt; font-weight: bold; color: #DC2626; }
        .pdf-body .mock-popup-hd .s { font-size: 7pt; color: #64748B; margin-top: 1mm; }
        .pdf-body table.mock-popup-body { width: 100%; border-collapse: collapse; }
        .pdf-body table.mock-popup-body th {
            background: #F1F5F9; padding: 1.5mm 3mm; text-align: left;
            font-size: 6.5pt; font-weight: bold; color: #334155; letter-spacing: 0.5pt;
        }
        .pdf-body table.mock-popup-body td {
            padding: 2mm 3mm; border-bottom: 0.3pt solid #F1F5F9;
            font-size: 8.5pt; color: #0F172A;
        }
        .pdf-body table.mock-popup-body td.qty { text-align: right; }
        .pdf-body table.mock-popup-body td.qty strong { color: #DC2626; }
        .pdf-body .badge-rupt {
            display: inline-block; background: #FEE2E2; color: #DC2626;
            font-size: 6pt; font-weight: bold; padding: 0.3mm 1.5mm; letter-spacing: 0.5pt;
        }
        .pdf-body .btn-reappro {
            display: inline-block; background: #DC2626; color: #FFFFFF;
            font-size: 7pt; font-weight: bold; padding: 1mm 2.5mm;
        }
        .pdf-body .mock-popup-ft {
            background: #FEF2F2; padding: 2.5mm 4mm; border-top: 0.5pt solid #FCA5A5;
        }
        .pdf-body .btn-cmd {
            background: #D97706; color: #FFFFFF; padding: 2mm 3mm;
            font-size: 8pt; font-weight: bold; display: inline-block;
        }

        /* Annotations */
        .pdf-body .screen-annots { border-collapse: collapse; width: 100%; margin: 3mm 0 0; }
        .pdf-body .screen-annots td { padding: 1.5mm 0; vertical-align: top; font-size: 9pt; }
        .pdf-body .screen-annots .num {
            width: 7mm; font-weight: bold; color: #FFFFFF; background: #171717;
            border-radius: 50%; text-align: center; font-size: 8pt; padding: 0.8mm 0;
        }

        /* Button refs — comme des tags dans le texte */
        .pdf-body .btn-ref {
            display: inline-block; padding: 0.3mm 1.5mm; font-size: 8.5pt; font-weight: bold;
            background: {{ $guide['accent'] }}; color: #FFFFFF; border-radius: 1mm;
        }
        .pdf-body .btn-ref.btn-neutral { background: #E5E5E0; color: #171717; }
        .pdf-body .btn-ref.btn-danger { background: #DC2626; color: #FFFFFF; }

        .pdf-body hr.divider { border: 0; border-top: 0.5pt solid #E5E5E0; margin: 8mm 0; }

        /* Footer info */
        .pdf-footer {
            margin-top: 15mm; padding-top: 5mm; border-top: 0.5pt solid #E5E5E0;
            text-align: center; font-size: 8pt; color: #737373;
        }
    </style>
</head>
<body>

<div class="pdf-cover">
    <div class="eyebrow">Guide utilisateur · OptimiZe ERP</div>
    <h1>{{ $guide['title'] }}</h1>
    <p class="sub">{{ $guide['subtitle'] }}</p>
    <div class="meta">
        <table>
            <tr><td class="lbl">Public visé</td><td>{{ $guide['audience'] }}</td></tr>
            <tr><td class="lbl">Durée de lecture</td><td>{{ $guide['duration'] }}</td></tr>
            <tr><td class="lbl">Version</td><td>{{ $guide['version'] ?? '1.0' }} — {{ $guide['updated'] ?? now()->format('Y-m-d') }}</td></tr>
            <tr><td class="lbl">Édité par</td><td>Yubile Technologie</td></tr>
        </table>
    </div>
</div>

<div class="pdf-body">
    @include($body)
</div>

<div class="pdf-footer">
    OptimiZe ERP · Yubile Technologie · Version {{ $guide['version'] ?? '1.0' }} · {{ $guide['updated'] ?? now()->format('Y-m-d') }}
</div>

</body>
</html>
