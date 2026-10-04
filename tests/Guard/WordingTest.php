<?php

namespace Base\Lawyer\Tests\Guard;

use Base\Lawyer\Guard\Wording;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** RIN art. 10.2: no comparison, no disparagement, no reference to judicial functions. */
final class WordingTest extends TestCase
{
    #[DataProvider('forbidden')]
    public function testComparisonAndAdvertisingAreFound(string $text, string $label): void
    {
        self::assertContains($label, Wording::scan($text), $text);
    }

    public static function forbidden(): iterable
    {
        yield ['Le meilleur avocat du barreau.', 'le meilleur'];
        yield ['Ancien magistrat, il plaide depuis 2012.', 'fonctions juridictionnelles'];
        yield ['Avocate et juge consulaire.', 'fonctions juridictionnelles'];
        yield ['Nos tarifs sont MEILLEURS QUE ceux d’à côté', 'meilleur que'];
        yield ['Cabinet n°1 de la région', 'numéro 1'];
        yield ['Le cabinet numéro un du divorce', 'numéro 1'];
        yield ['Des honoraires moins chers.', 'moins cher'];
        yield ['Plus réactifs que nos confrères', 'plus … que nos confrères'];
        yield ['Contrairement à d’autres cabinets, nous répondons.', 'contrairement à'];
        yield ['Un savoir-faire <em>inégalé</em>', 'incomparable'];
        yield ['Résultat garanti', 'résultat garanti'];
        yield ['Offre spéciale de rentrée', 'offre spéciale'];
    }

    #[DataProvider('allowed')]
    public function testThePlainWordsOfTheTradeAreNot(string $text): void
    {
        self::assertSame([], Wording::scan($text), $text);
    }

    public static function allowed(): iterable
    {
        yield ['Vente en l’état futur d’achèvement et promotion immobilière.'];
        yield ['Nous vous répondons dans les meilleurs délais.'];
        yield ['Droit de la concurrence et de la distribution.'];
        yield ['Le cabinet assiste les salariés devant le conseil de prud’hommes.'];
        yield ['Le juge aux affaires familiales fixe la résidence des enfants.'];
        yield ['Le bail commercial est plus protecteur que le bail dérogatoire.'];
        yield [''];
    }

    public function testTheSitesOwnExpressionsAreAdded(): void
    {
        self::assertSame(['Cabinet de référence'], Wording::scan('Le cabinet de référence du Val.', ['Cabinet de référence']));
        self::assertSame([], Wording::scan('Les références du dossier.', ['Cabinet de référence']));
        self::assertTrue(Wording::isClean('Rien à signaler.'));
    }

    public function testEachExpressionIsReportedOnce(): void
    {
        self::assertSame(['numéro 1'], Wording::scan('N°1 ici, numéro 1 là.'));
    }
}
