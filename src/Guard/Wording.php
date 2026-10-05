<?php

namespace Base\Lawyer\Guard;

use Base\Office\Guard\Wording as OfficeWording;

/**
 * Words a lawyer's communication must not carry: comparison with
 * colleagues, disparagement, the superlatives of advertising. The texts:
 * the lawyer's communication keeps to "la dignité, la loyauté, la
 * confraternité, la délicatesse ; la neutralité, l'impartialité et
 * l'objectivité" (règlement professionnel, art. 14.1), abstains from "toute
 * démarche à caractère publicitaire" (art. 14.2), and a personalised
 * solicitation or an online offer of services "ne peut pas inclure
 * d'éléments comparatifs ou dénigrants" (decree n° 73-1202 of 28 December
 * 1973 as amended by decree n° 2019-257 of 29 March 2019).
 *
 * A list of expressions is a help to the person who writes, not a judge:
 * what it finds is shown as a warning, and what it does not find is not
 * thereby allowed.
 *
 * The expressions themselves are omnibase/office's (Base\Office\Guard\Wording),
 * common to the regulated practices; the lawyers' own follow them.
 */
final class Wording
{
    /** What is the lawyers' alone, each a regular expression on the plain text. */
    public const PATTERNS = [
        'fonctions juridictionnelles' => '\b(?:ancien(?:ne)?\s+(?:magistrate?|juge|procureure?|conseill(?:er|ere)\s+prud)|juge\s+(?:consulaire|de\s+proximite|suppleant)|magistrate?\s+(?:honoraire|a\s+titre\s+temporaire))\b',
    ];

    /**
     * @param list<string> $extra expressions of the site's own, matched as they are written (case and accents apart)
     *
     * @return list<string> what was found, by its label
     */
    public static function scan(?string $text, array $extra = []): array
    {
        return OfficeWording::scan($text, $extra, self::PATTERNS);
    }

    public static function isClean(?string $text, array $extra = []): bool
    {
        return [] === self::scan($text, $extra);
    }

    /** Lowered, without accents nor tags, typographic apostrophes and spaces made plain. */
    public static function plain(string $text): string
    {
        return OfficeWording::plain($text);
    }
}
