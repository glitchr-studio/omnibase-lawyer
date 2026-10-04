<?php

namespace Base\Lawyer\Guard;

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
 */
final class Wording
{
    /** Each a regular expression on the text once lowered and stripped of its accents. */
    private const PATTERNS = [
        'le meilleur' => '\bmeilleur(?:e|s|es)?\s+(?:notaires?|etudes?|offices?|avocat(?:e|s|es)?|cabinets?|specialistes?|experts?|juristes?|services?|tarifs?|honoraires)\b',
        'meilleur que' => '\bmeilleur(?:e|s|es)?\s+qu',
        'numéro 1' => '\b(?:n[o°]\s?1|numero\s+(?:1|un))\b',
        'leader' => '\bleaders?\b',
        'moins cher' => '\bmoins\s+cher(?:e|s|es)?\b',
        'prix imbattable' => '\bimbattables?\b',
        'plus … que nos confrères' => '\bplus\s+\w+\s+que\s+(?:nos|les|vos|d\'autres|des)\s+(?:confreres|autres|notaires|etudes|offices|cabinets|avocats|concurrents)\b',
        'contrairement à' => '\bcontrairement\s+(?:a|aux)\s+(?:nos|d\'autres|certains|certaines|la plupart)\b',
        'nos concurrents' => '\b(?:nos|les|des|ses)\s+concurrents\b',
        'incomparable' => '\b(?:incomparables?|inegal(?:e|ee|es|ees)|sans\s+equivalent)\b',
        'satisfait ou remboursé' => '\bsatisfait(?:e|s|es)?\s+ou\s+rembourse',
        'résultat garanti' => '\b(?:resultats?\s+garantis?|garantie?\s+de\s+resultats?)\b',
        'fonctions juridictionnelles' => '\b(?:ancien(?:ne)?\s+(?:magistrate?|juge|procureure?|conseill(?:er|ere)\s+prud)|juge\s+(?:consulaire|de\s+proximite|suppleant)|magistrate?\s+(?:honoraire|a\s+titre\s+temporaire))\b',
        'offre spéciale' => '\b(?:offres?\s+speciales?|prix\s+casses?|remises?\s+exceptionnelles?|offres?\s+promotionnelles?)\b',
    ];

    /**
     * @param list<string> $extra expressions of the site's own, matched as they are written (case and accents apart)
     *
     * @return list<string> what was found, by its label
     */
    public static function scan(?string $text, array $extra = []): array
    {
        $plain = self::plain((string) $text);
        if ('' === $plain) {
            return [];
        }
        $found = [];
        foreach (self::PATTERNS as $label => $pattern) {
            if (1 === preg_match('/'.$pattern.'/u', $plain)) {
                $found[] = $label;
            }
        }
        foreach ($extra as $expression) {
            $needle = self::plain((string) $expression);
            if ('' !== $needle && 1 === preg_match('/(?<![\p{L}\p{N}])'.preg_quote($needle, '/').'(?![\p{L}\p{N}])/u', $plain)) {
                $found[] = (string) $expression;
            }
        }

        return array_values(array_unique($found));
    }

    public static function isClean(?string $text, array $extra = []): bool
    {
        return [] === self::scan($text, $extra);
    }

    /** Lowered, without accents nor tags, typographic apostrophes and spaces made plain. */
    public static function plain(string $text): string
    {
        $text = mb_strtolower(strip_tags($text));
        $text = strtr($text, ['’' => "'", '‘' => "'", "\u{00A0}" => ' ', "\u{202F}" => ' ', 'œ' => 'oe', 'æ' => 'ae']);
        $text = \Normalizer::normalize($text, \Normalizer::FORM_D) ?: $text;
        $text = preg_replace('/\p{Mn}+/u', '', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
