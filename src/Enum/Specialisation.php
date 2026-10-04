<?php

namespace Base\Lawyer\Enum;

/**
 * The mentions of specialisation a lawyer may hold: the list fixed by the
 * order of the garde des Sceaux of 28 December 2011, as the Conseil
 * national des barreaux publishes it (28 mentions). "Seul l'avocat
 * titulaire d'un ou de plusieurs certificats de spécialisation [...] peut
 * utiliser pour sa communication [...] les mots « spécialiste »,
 * « spécialisé », « spécialité » ou « spécialisation »" (RIN art. 10.2):
 * a specialisation is one of these cases, or it is not shown.
 */
enum Specialisation: string
{
    case ARBITRATION = 'arbitrage';
    case ASSOCIATIONS = 'associations-fondations';
    case INSURANCE = 'assurances';
    case BANKING = 'bancaire-boursier';
    case COMMERCIAL = 'commercial-affaires-concurrence';
    case CREDIT = 'credit-consommation';
    case BODILY_INJURY = 'dommage-corporel';
    case CHILDREN = 'enfants';
    case ENVIRONMENT = 'environnement';
    case FOREIGNERS = 'etrangers-nationalite';
    case FAMILY = 'famille-personnes-patrimoine';
    case TRUST = 'fiducie';
    case TAX = 'fiscal-douanier';
    case SECURITIES = 'garanties-suretes-execution';
    case REAL_ESTATE = 'immobilier';
    case INTERNATIONAL = 'international-union-europeenne';
    case DIGITAL = 'numerique-communications';
    case CRIMINAL = 'penal';
    case INTELLECTUAL_PROPERTY = 'propriete-intellectuelle';
    case DATA_PROTECTION = 'protection-donnees-personnelles';
    case PUBLIC = 'public';
    case RURAL = 'rural';
    case HEALTH = 'sante';
    case SOCIAL_SECURITY = 'securite-sociale-protection-sociale';
    case COMPANIES = 'societes';
    case SPORT = 'sport';
    case TRANSPORT = 'transports';
    case LABOUR = 'travail';

    /** An avocat holds and uses two mentions at most (Conseil national des barreaux). */
    public const MAX = 2;

    /** The mention as the order names it. */
    public function label(): string
    {
        return match ($this) {
            self::ARBITRATION => 'Droit de l’arbitrage',
            self::ASSOCIATIONS => 'Droit des associations et des fondations',
            self::INSURANCE => 'Droit des assurances',
            self::BANKING => 'Droit bancaire et boursier',
            self::COMMERCIAL => 'Droit commercial, des affaires et de la concurrence',
            self::CREDIT => 'Droit du crédit et de la consommation',
            self::BODILY_INJURY => 'Droit du dommage corporel',
            self::CHILDREN => 'Droit des enfants',
            self::ENVIRONMENT => 'Droit de l’environnement',
            self::FOREIGNERS => 'Droit des étrangers et de la nationalité',
            self::FAMILY => 'Droit de la famille, des personnes et de leur patrimoine',
            self::TRUST => 'Droit de la fiducie',
            self::TAX => 'Droit fiscal et droit douanier',
            self::SECURITIES => 'Droit des garanties, des sûretés et des mesures d’exécution',
            self::REAL_ESTATE => 'Droit immobilier',
            self::INTERNATIONAL => 'Droit international et de l’Union européenne',
            self::DIGITAL => 'Droit du numérique et des communications',
            self::CRIMINAL => 'Droit pénal',
            self::INTELLECTUAL_PROPERTY => 'Droit de la propriété intellectuelle',
            self::DATA_PROTECTION => 'Droit de la protection des données personnelles',
            self::PUBLIC => 'Droit public',
            self::RURAL => 'Droit rural',
            self::HEALTH => 'Droit de la santé',
            self::SOCIAL_SECURITY => 'Droit de la sécurité sociale et de la protection sociale',
            self::COMPANIES => 'Droit des sociétés',
            self::SPORT => 'Droit du sport',
            self::TRANSPORT => 'Droit des transports',
            self::LABOUR => 'Droit du travail',
        };
    }

    /** @return array<string, string> label => value, for a form */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }

        return $choices;
    }
}
