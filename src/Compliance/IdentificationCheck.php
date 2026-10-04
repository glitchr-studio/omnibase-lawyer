<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Base\Office\Repository\OfficeRepository;

/**
 * « L'avocat doit, dans toute communication, [...] faire état de sa qualité
 * et permettre, quel que soit le support utilisé, de l'identifier, de le
 * localiser, de le joindre, de connaître le barreau auquel il est inscrit,
 * la structure d'exercice à laquelle il appartient et, le cas échéant, le
 * réseau dont il est membre » (RIN art. 10.2). The legal notice prints
 * them; the network is asked only of who has one.
 */
final class IdentificationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record, private readonly OfficeRepository $offices)
    {
    }

    public function check(): ComplianceResult
    {
        $office = $this->offices->findMain();
        $missing = [];
        if (null === $this->record->getFirm()) {
            $missing[] = 'name';
        }
        if (null === $office || null === $office->getStreet() || null === $office->getCity()) {
            $missing[] = 'address';
        }
        if (null === $office || (null === $office->getPhone() && null === $office->getEmail())) {
            $missing[] = 'contact';
        }
        if (null === $this->record->getBar()) {
            $missing[] = 'bar';
        }
        if (null === $this->record->getStructure()) {
            $missing[] = 'structure';
        }

        return [] === $missing
            ? ComplianceResult::ok('compliance.identification', 'lawyer')
            : new ComplianceResult('compliance.identification', ComplianceResult::MISSING, 'compliance.identification_advice', 'lawyer', ['missing' => implode(', ', $missing)]);
    }
}
