<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Entity\Attorney;
use Base\Lawyer\Enum\Specialisation;
use Base\Lawyer\Guard\SpecialistWords;
use Base\Lawyer\Repository\AreaRepository;
use Base\Lawyer\Repository\AttorneyRepository;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * Specialisations, as RIN art. 10.2 has them:
 *
 * - the words « spécialiste », « spécialisé », « spécialité »,
 *   « spécialisation » are for the holders of a certificate: a lawyer
 *   without one whose title, biography or dominant fields use them, or a
 *   field of practice that does when nobody in the firm holds a
 *   certificate, is to be corrected;
 * - a specialisation shown is one of the official list (the entity keeps
 *   nothing else), two at most;
 * - the dominant fields of activity are three at most.
 */
final class SpecialisationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly AttorneyRepository $attorneys, private readonly AreaRepository $areas)
    {
    }

    public function check(): ComplianceResult
    {
        $problems = [];
        $anySpecialist = false;
        foreach ($this->attorneys->findVisible() as $attorney) {
            $name = (string) $attorney;
            $anySpecialist = $anySpecialist || $attorney->isSpecialist();
            if (\count($attorney->getSpecialisationValues()) > Specialisation::MAX) {
                $problems[] = sprintf('%s : plus de %d mentions de spécialisation', $name, Specialisation::MAX);
            }
            if (\count($attorney->getDominantFields()) > Attorney::MAX_DOMINANT_FIELDS) {
                $problems[] = sprintf('%s : plus de %d domaines d’activités dominantes', $name, Attorney::MAX_DOMINANT_FIELDS);
            }
            if (!$attorney->isSpecialist()) {
                $member = $attorney->getMember();
                $words = SpecialistWords::find($member?->getTitle()."\n".$member?->getBiography()."\n".implode("\n", $attorney->getDominantFields()));
                if ([] !== $words) {
                    $problems[] = sprintf('%s : « %s » sans certificat de spécialisation', $name, implode(' », « ', $words));
                }
            }
            if (null !== $attorney->getQualification() && !$attorney->isSpecialist()) {
                $problems[] = sprintf('%s : une qualification spécifique sans mention de spécialisation', $name);
            }
        }
        if (!$anySpecialist) {
            foreach ($this->areas->findActive() as $area) {
                $words = SpecialistWords::find($area->getPublicText());
                if ([] !== $words) {
                    $problems[] = sprintf('%s : « %s » sans certificat de spécialisation au cabinet', $area->getName(), implode(' », « ', $words));
                }
            }
        }

        return [] === $problems
            ? ComplianceResult::ok('compliance.specialisation', 'lawyer')
            : new ComplianceResult('compliance.specialisation', ComplianceResult::MISSING, 'compliance.specialisation_advice', 'lawyer', ['found' => implode(' ; ', $problems)]);
    }
}
