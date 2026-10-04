<?php

namespace Base\Lawyer\Tests\Compliance;

use Base\Lawyer\Compliance\DomainNameCheck;
use Base\Lawyer\Compliance\FeesInformationCheck;
use Base\Lawyer\Compliance\IdentificationCheck;
use Base\Lawyer\Compliance\MediatorCheck;
use Base\Lawyer\Compliance\OrderDeclarationCheck;
use Base\Lawyer\Compliance\SpecialisationCheck;
use Base\Lawyer\Compliance\WordingCheck;
use Base\Lawyer\Entity\Area;
use Base\Lawyer\Entity\Attorney;
use Base\Lawyer\Repository\AreaRepository;
use Base\Lawyer\Repository\AttorneyRepository;
use Base\Lawyer\Service\Domains;
use Base\Lawyer\Service\Record;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Entity\Member;
use Base\Office\Entity\Office;
use Base\Office\Repository\MemberRepository;
use Base\Office\Repository\OfficeRepository;
use Base\Service\SettingBagInterface;
use PHPUnit\Framework\TestCase;

/** Each rule of the regime as the back office's "Conformité" widget answers it. */
final class ChecksTest extends TestCase
{
    // ── RIN art. 10.5: the domain name ───────────────────────────────

    public function testTheDomainNameCarriesTheName(): void
    {
        self::assertTrue($this->domain(['mareval-avocat.fr'])->check()->isOk());

        $generic = $this->domain(['mareval-avocat.fr', 'avocat-divorce.fr'])->check();
        self::assertSame(ComplianceResult::MISSING, $generic->status);
        self::assertSame('avocat-divorce.fr', $generic->parameters['domains']);

        self::assertSame('compliance.domain_mixed', $this->domain(['mareval-divorce.fr'])->check()->advice);
        self::assertSame('compliance.domain_unnamed', $this->domain(['lexium.fr'])->check()->advice);
        self::assertSame('compliance.domain_local', $this->domain([], 'https://localhost:8573')->check()->advice);
        self::assertSame('compliance.domain_none', $this->domain([], null)->check()->advice);
    }

    public function testWithoutConfigurationTheDomainIsTheRoutersDefaultAddress(): void
    {
        self::assertTrue($this->domain([], 'https://www.mareval-avocat.fr')->check()->isOk());
    }

    // ── RIN art. 10.5, 10.2, 10.3: the Ordre is told ─────────────────

    public function testTheSiteMustBeDeclaredToTheOrdre(): void
    {
        self::assertSame('compliance.order_bar_advice', (new OrderDeclarationCheck($this->record([])))->check()->advice);
        $undated = (new OrderDeclarationCheck($this->record([Record::BAR => 'barreau de Port-Lazenne'])))->check();
        self::assertSame(ComplianceResult::MISSING, $undated->status);
        self::assertSame('barreau de Port-Lazenne', $undated->parameters['bar']);
        self::assertSame(ComplianceResult::WARNING, (new OrderDeclarationCheck($this->record([Record::BAR => 'b', Record::DECLARED_AT => '2999-01-01'])))->check()->status);
        self::assertTrue((new OrderDeclarationCheck($this->record([Record::BAR => 'b', Record::DECLARED_AT => '2026-09-14'])))->check()->isOk());
    }

    // ── RIN art. 10.2: identification ────────────────────────────────

    public function testTheFirmIsIdentifiedLocatedReachableWithItsBarAndItsStructure(): void
    {
        $office = (new Office('Cabinet Maréval', 'cabinet-mareval'))->setStreet('7 quai des Cordiers')->setCity('Port-Lazenne')->setPhone('02 61 91 00 30');
        $full = $this->record([Record::FIRM => 'Cabinet Maréval', Record::BAR => 'barreau de Port-Lazenne', Record::STRUCTURE => 'Exercice individuel']);

        self::assertTrue((new IdentificationCheck($full, $this->offices($office)))->check()->isOk(), 'no network: none is asked');

        $result = (new IdentificationCheck($this->record([]), $this->offices(new Office('x', 'x'))))->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertSame('name, address, contact, bar, structure', $result->parameters['missing']);
    }

    // ── RIN art. 10.2: specialisations ───────────────────────────────

    public function testTheWordSpecialistIsForTheHoldersOfACertificate(): void
    {
        $without = new Attorney((new Member('Me Lucien d’Orgeval'))->setBiography('Avocat spécialisé en droit pénal.'));
        $with = (new Attorney((new Member('Me Hortense Maréval'))->setBiography('Avocate spécialiste en droit du travail.')))->setSpecialisationValues(['travail']);

        $result = $this->specialisation([$without, $with])->check();
        self::assertSame(ComplianceResult::MISSING, $result->status);
        self::assertStringContainsString('Me Lucien d’Orgeval : « specialise » sans certificat', $result->parameters['found']);
        self::assertStringNotContainsString('Maréval', $result->parameters['found']);

        self::assertTrue($this->specialisation([$with])->check()->isOk());
    }

    public function testThreeDominantFieldsAndTwoMentionsAtMost(): void
    {
        $four = (new Attorney(new Member('Me Quatre')))->setDominantFields(['a', 'b', 'c', 'd']);
        self::assertStringContainsString('plus de 3 domaines', $this->specialisation([$four])->check()->parameters['found']);

        $three = (new Attorney(new Member('Me Trois')))->setDominantFields(['a', 'b', 'c']);
        self::assertTrue($this->specialisation([$three])->check()->isOk());

        $mentions = (new Attorney(new Member('Me Mentions')))->setSpecialisationValues(['travail', 'penal', 'fiscal-douanier']);
        self::assertStringContainsString('plus de 2 mentions', $this->specialisation([$mentions])->check()->parameters['found']);

        $qualified = (new Attorney(new Member('Me Qualifiée')))->setQualification('Droit du licenciement');
        self::assertStringContainsString('qualification spécifique sans mention', $this->specialisation([$qualified])->check()->parameters['found']);
    }

    public function testAFieldOfPracticeIsNotASpecialityWhenNobodyHoldsACertificate(): void
    {
        $area = (new Area('Droit du travail'))->setSummary('Notre spécialité : le licenciement.');
        $plain = new Attorney(new Member('Me Simple'));
        $certified = (new Attorney(new Member('Me Certifiée')))->setSpecialisationValues(['travail']);

        self::assertStringContainsString('Droit du travail : « specialite »', $this->specialisation([$plain], [$area])->check()->parameters['found']);
        self::assertTrue($this->specialisation([$plain, $certified], [$area])->check()->isOk());
    }

    // ── RIN art. 10.2: wording ───────────────────────────────────────

    public function testComparativeWordingAndJudicialFunctionsAreNamedWhereTheyAre(): void
    {
        $areas = $this->createStub(AreaRepository::class);
        $areas->method('findActive')->willReturn([(new Area('Famille'))->setSummary('Des honoraires moins chers.')]);
        $members = $this->createStub(MemberRepository::class);
        $members->method('findVisible')->willReturn([(new Member('Me Test'))->setBiography('Ancienne magistrate.')]);

        $result = (new WordingCheck($areas, $members, $this->record([])))->check();
        self::assertSame(ComplianceResult::WARNING, $result->status);
        self::assertStringContainsString('Famille : « moins cher »', $result->parameters['found']);
        self::assertStringContainsString('Me Test : « fonctions juridictionnelles »', $result->parameters['found']);
    }

    // ── RIN art. 11.1, 11.2: fees ────────────────────────────────────

    public function testTheFeesPageSaysHowFeesAreSet(): void
    {
        self::assertSame(ComplianceResult::MISSING, (new FeesInformationCheck($this->record([])))->check()->status);
        self::assertTrue((new FeesInformationCheck($this->record([Record::FEES => 'Au temps passé, 220 € HT de l’heure.'])))->check()->isOk());
    }

    public function testTheMediatorsCoordinatesAreGiven(): void
    {
        self::assertTrue((new MediatorCheck(['name' => 'Médiateur de la consommation de la profession d’avocat', 'address' => '180 boulevard Haussmann, 75008 Paris']))->check()->isOk());
        self::assertSame(ComplianceResult::WARNING, (new MediatorCheck(['name' => 'Médiateur']))->check()->status);
        self::assertSame(ComplianceResult::WARNING, (new MediatorCheck([]))->check()->status);
    }

    /** @param list<string> $domains */
    private function domain(array $domains, ?string $defaultUri = 'https://localhost'): DomainNameCheck
    {
        $attorneys = $this->createStub(AttorneyRepository::class);
        $attorneys->method('findVisible')->willReturn([new Attorney(new Member('Me Hortense Maréval'))]);

        return new DomainNameCheck(new Domains($this->record([Record::FIRM => 'Cabinet Maréval']), $attorneys, $domains, $defaultUri));
    }

    /**
     * @param list<Attorney> $attorneys
     * @param list<Area>     $areas
     */
    private function specialisation(array $attorneys, array $areas = []): SpecialisationCheck
    {
        $attorneyRepository = $this->createStub(AttorneyRepository::class);
        $attorneyRepository->method('findVisible')->willReturn($attorneys);
        $areaRepository = $this->createStub(AreaRepository::class);
        $areaRepository->method('findActive')->willReturn($areas);

        return new SpecialisationCheck($attorneyRepository, $areaRepository);
    }

    /** @param array<string, string> $values */
    private function record(array $values): Record
    {
        $settings = $this->createStub(SettingBagInterface::class);
        $settings->method('getScalar')->willReturnCallback(static fn ($path) => $values[$path] ?? null);

        return new Record($settings);
    }

    private function offices(Office ...$offices): OfficeRepository
    {
        $repository = $this->createStub(OfficeRepository::class);
        $repository->method('findOrdered')->willReturn($offices);
        $repository->method('findMain')->willReturn($offices[0] ?? null);

        return $repository;
    }
}
