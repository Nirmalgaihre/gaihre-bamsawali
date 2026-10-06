<?php

namespace App\Services;

class SafeHtml
{
    public static function clean(?string $html): string
    {
        if (! $html) {
            return '';
        }
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        self::sanitize($document);
        $result = $document->saveHTML();

        return preg_replace('/<\?xml.*?\?>/s', '', $result) ?? '';
    }

    private static function sanitize(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form', 'input'], true)) {
                    $node->removeChild($child);

                    continue;
                }
                self::sanitize($child);
                if (! in_array($tag, ['div', 'p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'blockquote', 'a'], true)) {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    } $node->removeChild($child);

                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attribute) {
                    if ($tag !== 'a' || $attribute->name !== 'href' || ! preg_match('~^(https?://|mailto:|/(?!/)|#)~i', trim($attribute->value))) {
                        $child->removeAttribute($attribute->name);
                    }
                }
                if ($tag === 'a') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            } elseif ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $node->removeChild($child);
            }
        }
    }
}
