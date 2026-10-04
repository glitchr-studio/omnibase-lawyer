<?php

namespace Base\Lawyer\Guard;

/**
 * RIN art. 10.2: « Seul l'avocat titulaire d'un ou de plusieurs certificats
 * de spécialisation [...] régulièrement obtenus et non invalidés peut
 * utiliser pour sa communication, quel qu'en soit le support, les mots
 * « spécialiste », « spécialisé », « spécialité » ou « spécialisation ». »
 *
 * Finds those four words (and their feminine and plural forms) in a text.
 */
final class SpecialistWords
{
    /** @return list<string> the words found, as written */
    public static function find(?string $text): array
    {
        $plain = Wording::plain((string) $text);
        if ('' === $plain || 0 === preg_match_all('/\b(specialistes?|specialise(?:e|s|es)?|specialites?|specialisations?)\b/u', $plain, $matches)) {
            return [];
        }

        return array_values(array_unique($matches[1]));
    }

    public static function uses(?string $text): bool
    {
        return [] !== self::find($text);
    }
}
