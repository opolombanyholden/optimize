<?php

namespace App\Support;

/**
 * Nettoyage HTML par liste blanche (signatures de mail…) : seules les balises de mise en
 * forme courantes sont conservées, sans script, gestionnaire d'événement ni URL javascript:.
 */
class HtmlSur
{
    private const BALISES = [
        'p' => ['style', 'align'], 'br' => [], 'div' => ['style', 'align'], 'span' => ['style'], 'strong' => ['style'], 'b' => ['style'],
        'em' => ['style'], 'i' => ['style'], 'u' => ['style'], 's' => ['style'], 'small' => ['style'], 'sup' => [], 'sub' => [],
        'ul' => ['style'], 'ol' => ['style'], 'li' => ['style'], 'h1' => ['style'], 'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'],
        'blockquote' => ['style'], 'hr' => ['style', 'width', 'size', 'color'],
        'a' => ['href', 'title', 'style'], 'img' => ['src', 'alt', 'width', 'height', 'style', 'border', 'align'],
        // Tableaux : mise en page usuelle des signatures (compatibles Outlook)
        'table' => ['style', 'width', 'border', 'cellpadding', 'cellspacing', 'align', 'bgcolor', 'role'],
        'thead' => [], 'tbody' => [], 'tr' => ['style', 'valign', 'bgcolor'],
        'td' => ['style', 'width', 'height', 'align', 'valign', 'colspan', 'rowspan', 'bgcolor'],
        'th' => ['style', 'width', 'align', 'valign', 'colspan', 'rowspan', 'bgcolor'],
    ];

    /** Propriétés CSS autorisées dans style="" (mise en forme uniquement, pas de positionnement). */
    private const CSS = [
        'color', 'background-color', 'background', 'font', 'font-family', 'font-weight', 'font-style', 'font-size', 'font-variant',
        'line-height', 'letter-spacing', 'text-decoration', 'text-align', 'text-transform', 'text-indent', 'white-space', 'vertical-align',
        'width', 'height', 'min-width', 'max-width', 'min-height', 'max-height', 'display',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'border', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-color', 'border-width', 'border-style',
        'border-radius', 'border-collapse', 'border-spacing', 'list-style', 'list-style-type', 'opacity',
    ];

    /** Valeurs d'attributs numériques / dimensions (width, height, cellpadding…). */
    private const ATTR_DIMENSION = ['width', 'height', 'border', 'cellpadding', 'cellspacing', 'colspan', 'rowspan', 'size'];

    public static function nettoyer(string $html): string
    {
        $html = trim($html);
        if ($html === '') return '';

        $doc = new \DOMDocument();
        $prec = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="racine">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prec);

        $racine = $doc->getElementById('racine');
        if (!$racine) return '';
        self::parcourir($racine);

        $res = '';
        foreach ($racine->childNodes as $n) $res .= $doc->saveHTML($n);
        return trim($res);
    }

    private static function parcourir(\DOMNode $noeud): void
    {
        foreach (iterator_to_array($noeud->childNodes) as $enfant) {
            if ($enfant instanceof \DOMComment || $enfant instanceof \DOMProcessingInstruction) {
                $noeud->removeChild($enfant);
                continue;
            }
            if (!$enfant instanceof \DOMElement) continue;

            $tag = strtolower($enfant->tagName);
            if (!array_key_exists($tag, self::BALISES)) {
                // Balise dangereuse : supprimée avec son contenu ; sinon on garde le texte
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'link', 'meta', 'base'], true)) {
                    $noeud->removeChild($enfant);
                } else {
                    self::parcourir($enfant);
                    while ($enfant->firstChild) $noeud->insertBefore($enfant->firstChild, $enfant);
                    $noeud->removeChild($enfant);
                }
                continue;
            }

            foreach (iterator_to_array($enfant->attributes) as $attr) {
                $nom = strtolower($attr->name);
                $val = trim($attr->value);
                $ok = in_array($nom, self::BALISES[$tag], true);
                if ($ok && $nom === 'href') $ok = (bool) preg_match('#^(https?:|mailto:|tel:)#i', $val);
                if ($ok && $nom === 'src')  $ok = (bool) preg_match('#^(https:|data:image/(png|jpe?g|gif|webp);base64,)#i', $val);
                if ($ok && in_array($nom, self::ATTR_DIMENSION, true)) $ok = (bool) preg_match('/^\d{1,4}%?$/', $val);
                if ($ok && in_array($nom, ['align', 'valign'], true)) $ok = (bool) preg_match('/^(left|right|center|top|middle|bottom|baseline|justify)$/i', $val);
                if ($ok && in_array($nom, ['bgcolor', 'color'], true)) $ok = (bool) preg_match('/^(#[0-9a-f]{3,8}|[a-z]{3,20})$/i', $val);
                if ($ok && $nom === 'role') $ok = $val === 'presentation';
                if ($ok && $nom === 'style') {
                    $val = self::css($val);
                    $ok = $val !== '';
                }
                $ok ? $enfant->setAttribute($nom, $val) : $enfant->removeAttribute($attr->name);
            }
            if ($tag === 'a') {
                $enfant->setAttribute('target', '_blank');
                $enfant->setAttribute('rel', 'noopener noreferrer');
            }
            self::parcourir($enfant);
        }
    }

    private static function css(string $style): string
    {
        $garde = [];
        foreach (explode(';', $style) as $decl) {
            [$prop, $val] = array_map('trim', explode(':', $decl, 2) + [1 => '']);
            $prop = strtolower($prop);
            if ($val === '' || !in_array($prop, self::CSS, true)) continue;
            if (preg_match('/url\s*\(|expression|javascript:|behavior|binding|@import|[<>"\\\\]/i', $val)) continue;
            $garde[] = "{$prop}: {$val}";
        }
        return implode('; ', $garde);
    }
}
