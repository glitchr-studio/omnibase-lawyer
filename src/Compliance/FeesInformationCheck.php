<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * « L'avocat informe son client, dès sa saisine, des modalités de
 * détermination des honoraires » (RIN art. 11.1) and « conclut par écrit
 * avec son client une convention d'honoraires » (art. 11.2). The fees page
 * always says the second; the first is the firm's own words, typed in the
 * settings: how its fees are set.
 */
final class FeesInformationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record)
    {
    }

    public function check(): ComplianceResult
    {
        return null !== $this->record->getFees()
            ? ComplianceResult::ok('compliance.fees', 'lawyer')
            : new ComplianceResult('compliance.fees', ComplianceResult::MISSING, 'compliance.fees_advice', 'lawyer');
    }
}
