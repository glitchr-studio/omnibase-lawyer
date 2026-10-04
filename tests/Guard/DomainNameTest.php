<?php

namespace Base\Lawyer\Tests\Guard;

use Base\Lawyer\Guard\DomainName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * RIN art. 10.5: the domain name carries the lawyer's name or the firm's
 * denomination, in full or abbreviated, before or after the word "avocat";
 * a name evoking generically the title, a field of law or an activity of
 * the profession is forbidden.
 */
final class DomainNameTest extends TestCase
{
    private const NAMES = ['Cabinet Maréval', 'Me Hortense Maréval', 'Me Lucien d’Orgeval'];

    #[DataProvider('domains')]
    public function testADomainNameIsReadAgainstTheNames(string $host, string $expected): void
    {
        self::assertSame($expected, DomainName::assess($host, self::NAMES), $host);
    }

    public static function domains(): iterable
    {
        // The name, alone or with "avocat".
        yield ['mareval-avocat.fr', DomainName::OK];
        yield ['www.mareval-avocat.fr', DomainName::OK];
        yield ['avocat-mareval.fr', DomainName::OK];
        yield ['mareval.fr', DomainName::OK];
        yield ['cabinet-mareval.com', DomainName::OK];
        yield ['marevalavocats.fr', DomainName::OK];
        yield ['hortense-mareval.avocat.fr', DomainName::OK];
        yield ['https://MAREVAL-AVOCATS.fr/honoraires', DomainName::OK];
        yield ['orgeval-avocat.fr', DomainName::OK];

        // Generic: the title, a field of law, an activity - and no name.
        yield ['avocat-divorce.fr', DomainName::GENERIC];
        yield ['avocats.fr', DomainName::GENERIC];
        yield ['droit-du-travail.fr', DomainName::GENERIC];
        yield ['avocat-permis.com', DomainName::GENERIC];
        yield ['cabinet-juridique.fr', DomainName::GENERIC];
        yield ['defense-penale.fr', DomainName::GENERIC];

        // The name, and a field of law besides.
        yield ['mareval-divorce.fr', DomainName::MIXED];
        yield ['mareval-avocat-droit-travail.fr', DomainName::MIXED];

        // No name the site knows.
        yield ['dupont-avocat.fr', DomainName::UNNAMED];
        yield ['lexium.fr', DomainName::UNNAMED];

        // Nothing to assess.
        yield ['localhost', DomainName::LOCAL];
        yield ['localhost:8573', DomainName::LOCAL];
        yield ['127.0.0.1', DomainName::LOCAL];
        yield ['avocat.local', DomainName::LOCAL];
        yield ['', DomainName::LOCAL];
    }

    public function testTheInitialsOfADenominationAreAnAbbreviation(): void
    {
        $names = ['Dupont Martin & Associés'];

        self::assertSame(DomainName::OK, DomainName::assess('dma-avocats.fr', $names));
        self::assertSame(DomainName::OK, DomainName::assess('dupont-martin.fr', $names));
        self::assertSame(DomainName::UNNAMED, DomainName::assess('xyz-avocats.fr', $names));
    }

    public function testWithoutAnyNameKnownNothingPasses(): void
    {
        self::assertSame(DomainName::UNNAMED, DomainName::assess('mareval-avocat.fr', []));
        self::assertSame(DomainName::GENERIC, DomainName::assess('avocat-divorce.fr', []));
    }

    public function testAFieldOfLawInAFirmsNameDoesNotMakeItsName(): void
    {
        // "Cabinet Droit Social" has no name in it: its words are generic.
        self::assertSame(DomainName::GENERIC, DomainName::assess('droit-social.fr', ['Cabinet Droit Social']));
    }
}
