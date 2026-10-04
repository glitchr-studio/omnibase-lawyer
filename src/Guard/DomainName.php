<?php

namespace Base\Lawyer\Guard;

/**
 * RIN art. 10.5: « Le nom de domaine doit comporter le nom de l'avocat ou
 * la dénomination du cabinet en totalité ou en abrégé, qui peut être suivi
 * ou précédé du mot « avocat ». L'utilisation de noms de domaine évoquant
 * de façon générique le titre d'avocat ou un titre pouvant prêter à
 * confusion, un domaine du droit ou une activité relevant de celles de
 * l'avocat, est interdite. »
 *
 * assess() reads a host against the names it may carry (the firm's
 * denomination, the lawyers' names):
 *
 *   OK       it carries a name, alone or with "avocat" / "avocats"
 *   GENERIC  it carries no name and evokes the title, a field of law or an
 *            activity: forbidden
 *   MIXED    it carries a name and a field of law or an activity besides
 *   UNNAMED  it carries no name the site knows
 *   LOCAL    a development host (localhost, an address, .local, .test):
 *            nothing to assess
 *
 * A reading aid: the conseil de l'Ordre, told of the domain names, decides.
 */
final class DomainName
{
    public const OK = 'ok';
    public const GENERIC = 'generic';
    public const MIXED = 'mixed';
    public const UNNAMED = 'unnamed';
    public const LOCAL = 'local';

    /** The word the rule allows before or after the name. */
    private const TITLE = ['avocat', 'avocats', 'avocate', 'avocates'];

    /** Words of structure that say nothing: part of a denomination, not a name. */
    private const NEUTRAL = ['cabinet', 'scp', 'selarl', 'selas', 'aarpi', 'sarl', 'associes', 'associe', 'associee', 'et', 'and', 'maitre', 'me', 'www', 'le', 'la', 'les', 'de', 'du', 'des'];

    /** What evokes generically the title, a field of law or an activity of the profession. */
    private const GENERIC_TERMS = [
        'droit', 'penale', 'sociale', 'fiscale', 'familial', 'familiale', 'juridique', 'juridiques', 'juriste', 'juristes', 'legal', 'law', 'lawyer', 'lawyers', 'justice', 'defense', 'conseil', 'conseils', 'barreau', 'tribunal', 'proces', 'contentieux', 'litige', 'litiges',
        'divorce', 'famille', 'succession', 'successions', 'penal', 'penaliste', 'travail', 'social', 'prudhommes', 'licenciement', 'immobilier', 'bail', 'baux', 'fiscal', 'fiscalite', 'affaires', 'societes', 'commercial',
        'permis', 'routier', 'etrangers', 'immigration', 'titre', 'sejour', 'dommage', 'corporel', 'accident', 'victimes', 'indemnisation', 'construction', 'urbanisme', 'public', 'sante', 'medical', 'assurance', 'assurances',
        'bancaire', 'credit', 'consommation', 'surendettement', 'recouvrement', 'propriete', 'intellectuelle', 'marques', 'brevets', 'numerique', 'donnees', 'rgpd', 'environnement', 'rural', 'sport', 'transport', 'transports',
        'arbitrage', 'mediation', 'mediateur', 'enfants', 'mineurs', 'fiducie', 'surete', 'suretes', 'execution', 'international', 'europeen',
    ];

    /**
     * @param list<string> $names the firm's denomination and the lawyers' names
     */
    public static function assess(?string $host, array $names): string
    {
        $host = mb_strtolower(trim((string) $host));
        $host = preg_replace('~^[a-z]+://~', '', $host) ?? $host;
        $host = preg_replace('~[/:?#].*$~', '', $host) ?? $host;
        if ('' === $host || 'localhost' === $host || false !== filter_var($host, \FILTER_VALIDATE_IP) || !str_contains($host, '.') || 1 === preg_match('/\.(local|localhost|test|internal|lan)$/', $host)) {
            return self::LOCAL;
        }

        // What is before the extension: "mareval-avocat" in mareval-avocat.fr, "cabinet.mareval" in www.cabinet.mareval.fr.
        $labels = explode('.', $host);
        array_pop($labels);
        $tokens = array_values(array_filter(preg_split('/[^a-z]+/', Wording::plain(implode('-', $labels))) ?: [], static fn (string $t) => '' !== $t && 'www' !== $t));
        if ([] === $tokens) {
            return self::UNNAMED;
        }
        $joined = implode('', $tokens);

        $known = self::nameTokens($names);
        $named = false;
        $rest = [];
        foreach ($tokens as $token) {
            if (\in_array($token, self::TITLE, true) || \in_array($token, self::NEUTRAL, true)) {
                continue;
            }
            if (self::carriesName($token, $known, $names)) {
                $named = true;
                continue;
            }
            $rest[] = $token;
        }
        // A name written without a separator: "marevalavocat".
        if (!$named) {
            $stripped = str_replace(self::TITLE, '', $joined);
            foreach ($known as $name) {
                if (mb_strlen($name) >= 4 && str_contains($stripped, $name)) {
                    $named = true;
                    $rest = array_values(array_filter($rest, static fn (string $t) => !str_contains($t, $name)));
                }
            }
        }

        $generic = array_values(array_filter($rest, static fn (string $t) => \in_array($t, self::GENERIC_TERMS, true)));
        // "avocat-divorce", "avocats", "droit-du-travail": no name at all.
        if (!$named) {
            return [] !== $generic || [] === $rest ? self::GENERIC : self::UNNAMED;
        }

        return [] !== $generic ? self::MIXED : self::OK;
    }

    /**
     * @param list<string> $names
     *
     * @return list<string> the words of the names that identify: no title, no word of structure
     */
    private static function nameTokens(array $names): array
    {
        $tokens = [];
        foreach ($names as $name) {
            foreach (preg_split('/[^a-z]+/', Wording::plain((string) $name)) ?: [] as $token) {
                if (mb_strlen($token) >= 3 && !\in_array($token, self::TITLE, true) && !\in_array($token, self::NEUTRAL, true) && !\in_array($token, self::GENERIC_TERMS, true)) {
                    $tokens[] = $token;
                }
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * A word of a name, or the initials of a denomination ("dma" for Dupont Martin Associés).
     *
     * @param list<string> $known
     * @param list<string> $names
     */
    private static function carriesName(string $token, array $known, array $names): bool
    {
        if (\in_array($token, $known, true)) {
            return true;
        }
        foreach ($names as $name) {
            $words = array_values(array_filter(preg_split('/[^a-z]+/', Wording::plain((string) $name)) ?: [], static fn (string $w) => '' !== $w && !\in_array($w, ['et', 'and', 'de', 'du', 'des', 'le', 'la', 'les', 'me', 'maitre'], true)));
            $initials = implode('', array_map(static fn (string $w) => $w[0], $words));
            if (mb_strlen($token) >= 2 && \count($words) >= 2 && $token === $initials) {
                return true;
            }
        }

        return false;
    }
}
