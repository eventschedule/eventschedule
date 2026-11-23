@php
    // The sign is in the schedule's language and direction; the toolbar above it is in the
    // editor's, because it is the editor who presses it.
    $viewerLocale = app()->getLocale();
    $viewerRtl = is_rtl();
    $signLocale = is_valid_language_code($role->language_code) ? $role->language_code : $viewerLocale;
    $signRtl = in_array($signLocale, ['ar', 'he'], true);
    $kind = $map->kind();
    try {
        app()->setLocale($signLocale);
        $headline = \App\Services\VendorMaps\VendorMapKinds::say($kind, 'sign_headline');
        $hereLabel = __('messages.vm_you_are_here');
        $scanLabel = __('messages.vm_sign_scan');
        $scanTitle = \App\Services\VendorMaps\VendorMapKinds::say($kind, 'scan_title');
        $scanBody = __('messages.vm_sign_scan_body');
        // The blocks' labels (what is sold here) in the sign's language too.
        $schematic = \App\Services\VendorMaps\VendorMapSchematic::build($map, $payload);
        // The stalls as large as the panel allows (about 150 mm of A4 is left for the map): a
        // market with a tall area gets smaller stalls rather than a map that runs off the sheet.
        $rowsOf = fn ($z) => intdiv(count($z['cells']), max(1, $z['cols']));
        $stackOf = fn ($zones) => array_sum(array_map(fn ($z) => $rowsOf($z) * 1.15 + 2.1, $zones));
        $units = 0;
        foreach ($schematic['sections'] as $sec) {
            $units += match ($sec['type']) {
                'full' => $stackOf([$sec['zone']]),
                'stack' => $stackOf($sec['zones']),
                default => max($stackOf($sec['a']), $stackOf($sec['b'])),
            };
        }
        $cellMm = round(max(4, min(15, 140 / max(1, $units + 2.5))), 2);
        $landmarkNames = [];
        foreach (\App\Models\VendorMapLandmark::KINDS as $landmarkKind) {
            $landmarkNames[$landmarkKind] = __('messages.vm_landmark_'.$landmarkKind);
        }
    } finally {
        app()->setLocale($viewerLocale);
    }

    $qr = \App\Utils\QrCodeUtils::dataUri($qrUrl ?? $mapUrl, 760, 0);
    $shortUrl = preg_replace('#^https?://#', '', $mapUrl);
    $imageUrl = route('vendor_map.sign_image', ['subdomain' => $role->subdomain]);
    $backUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'vendor-map']);

@endphp
<!DOCTYPE html>
<html lang="{{ $signLocale }}" dir="{{ $signRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="color-scheme" content="light">
    <title>{{ $role->name }}</title>
    <style {!! nonce_attr() !!}>
        @page { size: A4 portrait; margin: 0; }
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            background: #e5e7eb;
            color: #0f172a;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans", "Noto Sans Hebrew", Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            width: 210mm;
            max-width: calc(100% - 32px);
            margin: 16px auto 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .toolbar .spacer { flex: 1; }
        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 16px;
            border-radius: 10px;
            font: inherit;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .toolbar .back { padding-inline: 4px; color: #2563eb; background: none; }
        .toolbar .secondary { background: #fff; color: #0f172a; border: 1px solid #cbd5e1; }
        .toolbar .secondary:hover { background: #f8fafc; }
        .toolbar .primary { background: #4e81fa; color: #fff; border: 1px solid #4e81fa; }
        .toolbar .primary:hover { background: #3b6fe8; }
        .toolbar a:focus-visible, .toolbar button:focus-visible { outline: 2px solid #4e81fa; outline-offset: 2px; }
        .sheet-wrap { overflow-x: auto; padding: 16px 0 32px; }
        .sheet {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 14mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.12);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .name { margin: 0; font-size: 6mm; color: #475569; }
        .headline { margin: 1.5mm 0 6mm; font-size: 13mm; font-weight: 800; line-height: 1.1; }
        .map {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 6mm;
            border: 0.4mm solid #e2e8f0;
            border-radius: 5mm;
            background: #f6f8fb;
        }
        /* The blocks at print size (vendor-map/schematic-styles sets the rest). */
        .map .vms { --vms-bg: #f6f8fb; --vms-cell-h: {{ $cellMm }}mm; --vms-cell-font: {{ round($cellMm * 0.39, 2) }}mm; --vms-head-font: 4.8mm; --vms-meta-font: 4mm;
            --vms-gap: {{ round($cellMm * 0.15, 2) }}mm; --vms-zone-gap: {{ round(min(8, $cellMm * 0.55), 2) }}mm; --vms-pad: 3mm; --vms-radius: 3.2mm; --vms-cell-radius: 2mm; }
        /* A sign is printed once and read for months: every stall is drawn the same, never as
           empty or away on the day it was printed. */
        .map .vms .vms-c { border-style: solid; opacity: 1; background: #fff; }
        .map .vms-zh { margin-bottom: 2mm; }
        .map .vms-foot { padding-top: 4mm; border-top: 0.3mm dashed #cbd5e1; margin-top: 1mm; }
        .map .vms-here i { width: 4mm; height: 4mm; }
        /* The code in a solid band: white tile, the instruction beside it. */
        .band { display: flex; align-items: center; gap: 8mm; margin-top: 7mm; padding: 6mm; border-radius: 5mm; background: #1d4ed8; color: #fff; }
        .band .qr { flex: none; width: 52mm; height: 52mm; padding: 3mm; border-radius: 3mm; background: #fff; }
        .band .qr img { display: block; width: 100%; height: 100%; }
        .band h2 { margin: 0; font-size: 9mm; font-weight: 800; line-height: 1.1; }
        .band p { margin: 3mm 0 0; font-size: 5mm; font-weight: 500; line-height: 1.45; color: #dbeafe; }
        .band .url { margin-top: 4mm; font-size: 4.4mm; font-weight: 700; color: #fff; direction: ltr; unicode-bidi: isolate; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet-wrap { padding: 0; overflow: visible; }
            .sheet { margin: 0; box-shadow: none; }
        }
    </style>
    @include('vendor-map.schematic-styles')
</head>
<body>
    <nav class="toolbar" lang="{{ $viewerLocale }}" dir="{{ $viewerRtl ? 'rtl' : 'ltr' }}">
        <a class="back" href="{{ $backUrl }}">{{ __('messages.back') }}</a>
        <span class="spacer"></span>
        <a class="secondary" href="{{ $imageUrl }}" download>{{ __('messages.vm_sign_download') }}</a>
        <button type="button" class="primary" id="vm-sign-print">{{ __('messages.print') }}</button>
    </nav>

    <div class="sheet-wrap">
        <main class="sheet">
            <p class="name"><bdi>{{ $role->name }}</bdi></p>
            <h1 class="headline">{{ $headline }}</h1>

            <div class="map">
                @include('vendor-map.schematic', ['schematic' => $schematic, 'rtl' => $signRtl, 'hereLabel' => $hereLabel, 'showHere' => true])
            </div>

            <div class="band">
                <div class="qr"><img src="{{ $qr }}" alt="{{ $shortUrl }}" width="756" height="756"></div>
                <div>
                    <h2>{{ $scanTitle }}</h2>
                    <p>{{ $scanBody }}</p>
                    <p class="url">{{ $shortUrl }}</p>
                </div>
            </div>
        </main>
    </div>

    <script {!! nonce_attr() !!}>
        document.getElementById('vm-sign-print').addEventListener('click', function () { window.print(); });
        // ?print=1 opens the print dialog as the page arrives.
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.addEventListener('load', function () { window.print(); });
        }
    </script>
</body>
</html>
