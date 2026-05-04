@php
    $a11yI18n = [
        'toolbarOpen' => __('accessibility.toolbar_open'),
        'toolbarClose' => __('accessibility.toolbar_close'),
        'toolbarHeading' => __('accessibility.toolbar_heading'),
        'toolbarFontSize' => __('accessibility.toolbar_font_size'),
        'toolbarFontDefault' => __('accessibility.toolbar_font_default'),
        'toolbarFontMedium' => __('accessibility.toolbar_font_medium'),
        'toolbarFontLarge' => __('accessibility.toolbar_font_large'),
        'toolbarHighContrast' => __('accessibility.toolbar_high_contrast'),
        'toolbarUnderlineLinks' => __('accessibility.toolbar_underline_links'),
        'toolbarReduceMotion' => __('accessibility.toolbar_reduce_motion'),
        'toolbarReset' => __('accessibility.toolbar_reset'),
        'toolbarHideWidget' => __('accessibility.toolbar_hide_widget'),
        'toolbarHideConfirmTitle' => __('accessibility.toolbar_hide_confirm_title'),
        'toolbarHideConfirmBody' => __('accessibility.toolbar_hide_confirm_body'),
        'toolbarCancel' => __('accessibility.toolbar_cancel'),
        'toolbarDeclaration' => __('accessibility.toolbar_declaration'),
    ];
    $a11yDeclarationUrl = marketing_url('/accessibility');
@endphp
<script type="application/json" id="es-a11y-json" {!! nonce_attr() !!}>{!! json_encode(array_merge($a11yI18n, ['declarationUrl' => $a11yDeclarationUrl]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
<div
    id="es-a11y-widget-host"
    data-rtl="{{ is_rtl() ? '1' : '0' }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
></div>
