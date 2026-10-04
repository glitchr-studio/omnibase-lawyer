<?php

namespace Base\Lawyer\Tests\Entity;

use Base\Lawyer\Entity\Attorney;
use Base\Lawyer\Enum\Specialisation;
use PHPUnit\Framework\TestCase;

/** A specialisation is a mention of the official list, or it is not kept. */
final class AttorneyTest extends TestCase
{
    public function testTheOfficialListHasTwentyEightMentions(): void
    {
        self::assertCount(28, Specialisation::cases());
        self::assertCount(28, Specialisation::choices(), 'each with its own name');
        self::assertSame('Droit du travail', Specialisation::LABOUR->label());
        self::assertSame('Droit de la famille, des personnes et de leur patrimoine', Specialisation::FAMILY->label());
    }

    public function testWhatIsOutsideTheListIsDropped(): void
    {
        $attorney = (new Attorney())->setSpecialisationValues(['travail', 'droit du divorce express', 'travail', Specialisation::CRIMINAL]);

        self::assertSame(['travail', 'penal'], $attorney->getSpecialisationValues());
        self::assertSame([Specialisation::LABOUR, Specialisation::CRIMINAL], $attorney->getSpecialisations());
        self::assertTrue($attorney->isSpecialist());
        self::assertFalse((new Attorney())->setSpecialisationValues(['médiation'])->isSpecialist());
    }

    public function testTheDominantFieldsAreTypedOneALine(): void
    {
        $attorney = (new Attorney())->setDominantFieldsText("Droit du travail\n\n  Droit de la sécurité sociale \nDroit du travail");

        self::assertSame(['Droit du travail', 'Droit de la sécurité sociale'], $attorney->getDominantFields());
        self::assertSame("Droit du travail\nDroit de la sécurité sociale", $attorney->getDominantFieldsText());
        self::assertSame(3, Attorney::MAX_DOMINANT_FIELDS);
        self::assertSame(2, Specialisation::MAX);
    }
}
