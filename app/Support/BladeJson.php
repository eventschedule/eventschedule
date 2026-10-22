<?php

namespace App\Support;

/**
 * The `@json` directive, compiled from the expression's own arguments.
 *
 * Laravel's compileJson() splits the expression on EVERY comma and keeps the first three pieces as
 * value, flags and depth. So `@json(old('name', $fallback))` compiled to
 * `json_encode(old('name', $fallback), 512)`: the value survived, the flags became 512
 * (JSON_PARTIAL_OUTPUT_ON_ERROR) and the default JSON_HEX_TAG escaping was gone. Inside a script
 * block, a `<!--<script` in a string then kept the block's own closing tag from ending it and
 * nothing on the page started. Some two hundred calls in the views had that shape.
 *
 * Here the expression is split only on commas outside brackets and strings, as PHP reads it, and
 * JSON_HEX_TAG is always added: no caller wants a raw `<` inside a script, and `<` is the
 * same character to JavaScript and to a JSON parser.
 */
class BladeJson
{
    public const DEFAULT_FLAGS = 'JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT';

    /** $expression arrives without its outer parentheses: BladeCompiler::callCustomDirective() strips them. */
    public static function compile(?string $expression): string
    {
        $parts = self::topLevelArguments(trim((string) $expression));

        $value = $parts[0] ?? 'null';
        $flags = isset($parts[1]) ? '('.$parts[1].') | JSON_HEX_TAG' : self::DEFAULT_FLAGS;
        $depth = $parts[2] ?? '512';

        return "<?php echo json_encode({$value}, {$flags}, {$depth}) ?>";
    }

    /**
     * The expression's arguments, split on the commas PHP would read as argument separators.
     *
     * @return list<string>
     */
    public static function topLevelArguments(string $expression): array
    {
        $parts = [];
        $current = '';
        $depth = 0;

        // The opening tag is the tokenizer's way in; it is not part of any argument.
        $tokens = token_get_all('<?php '.$expression);
        array_shift($tokens);

        foreach ($tokens as $token) {
            $text = is_array($token) ? $token[1] : $token;

            if (! is_array($token)) {
                if ($text === '(' || $text === '[' || $text === '{') {
                    $depth++;
                } elseif ($text === ')' || $text === ']' || $text === '}') {
                    $depth--;
                } elseif ($text === ',' && $depth === 0) {
                    $parts[] = trim($current);
                    $current = '';

                    continue;
                }
            } elseif ($token[0] === T_CURLY_OPEN || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
                // `{$` and `${` inside an interpolated string close with a plain `}`.
                $depth++;
            }

            $current .= $text;
        }

        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }
}
