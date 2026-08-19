{{--
    Styling for owner/operator-authored markdown rendered as HTML (the
    `custom-content` class). Extracted from layouts/app.blade.php so the standalone
    legal-page layout can reuse it instead of keeping a second copy in sync.

    A nonced <style> block rather than Tailwind utilities on purpose: this is
    CSP-safe, ships with the existing build, and new arbitrary variants in a blade
    would need `npm run build` before they appeared in the compiled CSS.
--}}
<style {!! nonce_attr() !!}>
    /* Undo Tailwind preflight for markdown content */
    .custom-content ul, .custom-content ol { list-style: revert; margin: revert; padding: revert; }
    .custom-content a { color: #2563EB; text-decoration: revert; }
    .custom-content blockquote { margin: revert; }
    .custom-content hr { border: revert; height: revert; }
    .custom-content table, .custom-content th, .custom-content td { border: revert; padding: revert; }
    .custom-content img { display: revert; }
    .custom-content pre, .custom-content code { white-space: pre-wrap; font-family: revert; font-size: revert; }
    .custom-content strong, .custom-content b { font-weight: revert; }
    .custom-content em, .custom-content i { font-style: revert; }
    .custom-content sub { vertical-align: revert; font-size: revert; }
    .custom-content sup { vertical-align: revert; font-size: revert; }

    .dark .custom-content a { color: #60a5fa; }
    .dark .custom-content a:visited { color: #60a5fa; }
    .dark .custom-content a:hover { color: #93c5fd; }

    /* Heading sizes and spacing */
    .custom-content h1 { font-size: 1.8rem; font-weight: 700; margin: 0 0 0.25rem; }
    .custom-content h2 { font-size: 1.55rem; font-weight: 600; margin: 0 0 0.25rem; }
    .custom-content h3 { font-size: 1.3rem; font-weight: 600; margin: 0 0 0.25rem; }
    .custom-content h4, .custom-content h5, .custom-content h6 { font-size: 1.15rem; font-weight: 600; margin: 0 0 0.25rem; }

    .custom-content * + h1 { margin-top: 1rem; }
    .custom-content * + h2 { margin-top: 0.75rem; }
    .custom-content * + h3 { margin-top: 0.5rem; }
    .custom-content * + h4, .custom-content * + h5, .custom-content * + h6 { margin-top: 0.5rem; }

    /* Paragraph spacing */
    .custom-content p { margin: 0 0 0.5em; }
    .custom-content * + p { margin-top: 0.5em; }
</style>
