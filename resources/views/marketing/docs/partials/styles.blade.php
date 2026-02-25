<style {!! nonce_attr() !!}>
    /* Doc-specific gradient text variants */
    .text-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #0EA5E9 50%, #4E81FA 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .dark .text-gradient-blue {
        background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #7dd3fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .text-gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #14b8a6 50%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .dark .text-gradient-emerald {
        background: linear-gradient(135deg, #34d399 0%, #2dd4bf 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Documentation Styles */
    .doc-section {
        padding-bottom: 2rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    .dark .doc-section {
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    .doc-section:last-of-type {
        border-bottom: none;
    }
    .doc-heading {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
        padding-top: 1rem;
    }
    .dark .doc-heading {
        color: white;
    }
    .doc-code-block {
        background: #f3f4f6;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.75rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .dark .doc-code-block {
        background: rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .doc-code-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(0, 0, 0, 0.05);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        font-size: 0.75rem;
        color: #6b7280;
    }
    .dark .doc-code-header {
        background: rgba(255, 255, 255, 0.05);
        border-bottom-color: rgba(255, 255, 255, 0.1);
        color: #9ca3af;
    }
    .doc-copy-btn {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        color: #6b7280;
        background: rgba(0, 0, 0, 0.05);
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .dark .doc-copy-btn {
        color: #9ca3af;
        background: rgba(255, 255, 255, 0.1);
    }
    .doc-copy-btn:hover {
        background: rgba(0, 0, 0, 0.1);
        color: #374151;
    }
    .dark .doc-copy-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }
    .doc-code-block pre {
        margin: 0;
        padding: 1rem;
        overflow-x: auto;
        font-size: 0.875rem;
        line-height: 1.6;
    }
    .doc-code-block code {
        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Monaco, Consolas, monospace;
        color: #374151;
    }
    .dark .doc-code-block code {
        color: #e5e7eb;
    }
    .code-comment { color: #9ca3af; }
    .dark .code-comment { color: #6b7280; }
    .code-keyword { color: #7c3aed; }
    .dark .code-keyword { color: #c084fc; }
    .code-string { color: #059669; }
    .dark .code-string { color: #86efac; }
    .code-variable { color: #db2777; }
    .dark .code-variable { color: #f472b6; }
    .code-value { color: #2563eb; }
    .dark .code-value { color: #60a5fa; }

    .doc-inline-code {
        background: rgba(0, 0, 0, 0.05);
        color: #db2777;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Monaco, Consolas, monospace;
    }
    .dark .doc-inline-code {
        background: rgba(255, 255, 255, 0.1);
        color: #f472b6;
    }

    .doc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .doc-table th {
        background: rgba(0, 0, 0, 0.03);
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #111827;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    .dark .doc-table th {
        background: rgba(255, 255, 255, 0.05);
        color: white;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    .doc-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        color: #4b5563;
    }
    .dark .doc-table td {
        border-bottom-color: rgba(255, 255, 255, 0.05);
        color: #d1d5db;
    }
    .doc-table tr:hover td {
        background: rgba(0, 0, 0, 0.02);
    }
    .dark .doc-table tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    .doc-list {
        list-style: none;
        padding-left: 0;
        color: #4b5563;
    }
    .dark .doc-list {
        color: #d1d5db;
    }
    .doc-list li {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 0.5rem;
    }
    .doc-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.625rem;
        width: 0.375rem;
        height: 0.375rem;
        background: #9ca3af;
        border-radius: 50%;
    }
    .dark .doc-list li::before {
        background: #6b7280;
    }
    .doc-list.doc-list-numbered {
        counter-reset: list-counter;
    }
    .doc-list.doc-list-numbered li {
        counter-increment: list-counter;
    }
    .doc-list.doc-list-numbered li::before {
        content: counter(list-counter) '.';
        background: none;
        color: #6b7280;
        font-weight: 600;
        top: 0;
        width: auto;
        height: auto;
    }
    .dark .doc-list.doc-list-numbered li::before {
        color: #9ca3af;
    }

    .doc-callout {
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .doc-callout-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .doc-callout-info {
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #1d4ed8;
    }
    .dark .doc-callout-info {
        color: #93c5fd;
    }
    .doc-callout-info .doc-callout-title {
        color: #2563eb;
    }
    .dark .doc-callout-info .doc-callout-title {
        color: #60a5fa;
    }
    .doc-callout-tip {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #047857;
    }
    .dark .doc-callout-tip {
        color: #6ee7b7;
    }
    .doc-callout-tip .doc-callout-title {
        color: #059669;
    }
    .dark .doc-callout-tip .doc-callout-title {
        color: #34d399;
    }
    .doc-callout-warning {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #b45309;
    }
    .dark .doc-callout-warning {
        color: #fcd34d;
    }
    .doc-callout-warning .doc-callout-title {
        color: #d97706;
    }
    .dark .doc-callout-warning .doc-callout-title {
        color: #fbbf24;
    }

    .doc-heading-copy {
        background: none;
        border: none;
        padding: 0.25rem;
        cursor: pointer;
        color: #9ca3af;
        opacity: 0;
        transition: opacity 0.2s, color 0.2s;
        flex-shrink: 0;
        line-height: 1;
    }
    .doc-heading:hover .doc-heading-copy {
        opacity: 1;
    }
    .doc-heading-copy:hover {
        color: #4E81FA;
    }
    .dark .doc-heading-copy:hover {
        color: #60a5fa;
    }

    .doc-nav-link.active {
        color: #111827;
        background: rgba(0, 0, 0, 0.05);
    }
    .dark .doc-nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .prose-dark {
        padding-bottom: 50vh;
    }

    .prose-dark a {
        color: #2563eb;
        text-decoration: underline;
    }
    .dark .prose-dark a {
        color: #60a5fa;
    }
    .prose-dark a:hover {
        color: #1d4ed8;
    }
    .dark .prose-dark a:hover {
        color: #93c5fd;
    }
</style>
