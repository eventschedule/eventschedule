<style>
    .text-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .text-gradient-violet {
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 50%, #7c3aed 100%);
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
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
    .glass {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    /* Documentation Styles */
    .doc-section {
        padding-bottom: 2rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .doc-section:last-of-type {
        border-bottom: none;
    }
    .doc-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
        padding-top: 1rem;
    }
    .doc-code-block {
        background: rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .doc-code-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.75rem;
        color: #9ca3af;
    }
    .doc-copy-btn {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        color: #9ca3af;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .doc-copy-btn:hover {
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
        color: #e5e7eb;
    }
    .code-comment { color: #6b7280; }
    .code-keyword { color: #c084fc; }
    .code-string { color: #86efac; }
    .code-variable { color: #f472b6; }
    .code-value { color: #60a5fa; }

    .doc-inline-code {
        background: rgba(255, 255, 255, 0.1);
        color: #f472b6;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Monaco, Consolas, monospace;
    }

    .doc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .doc-table th {
        background: rgba(255, 255, 255, 0.05);
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: white;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .doc-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #d1d5db;
    }
    .doc-table tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    .doc-list {
        list-style: none;
        padding-left: 0;
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
        background: #6b7280;
        border-radius: 50%;
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
        color: #9ca3af;
        font-weight: 600;
        top: 0;
        width: auto;
        height: auto;
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
    }
    .doc-callout-info .doc-callout-title {
        color: #60a5fa;
    }
    .doc-callout-info {
        color: #93c5fd;
    }
    .doc-callout-tip {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .doc-callout-tip .doc-callout-title {
        color: #34d399;
    }
    .doc-callout-tip {
        color: #6ee7b7;
    }
    .doc-callout-warning {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .doc-callout-warning .doc-callout-title {
        color: #fbbf24;
    }
    .doc-callout-warning {
        color: #fcd34d;
    }

    .doc-nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .prose-dark a {
        color: #60a5fa;
        text-decoration: underline;
    }
    .prose-dark a:hover {
        color: #93c5fd;
    }
</style>
