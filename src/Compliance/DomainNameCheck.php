<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Guard\DomainName;
use Base\Lawyer\Service\Domains;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * The domain name carries the lawyer's name or the firm's denomination,
 * and evokes generically neither the title, nor a field of law, nor an
 * activity of the profession (RIN art. 10.5).
 */
final class DomainNameCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Domains $domains)
    {
    }

    public function check(): ComplianceResult
    {
        $verdicts = $this->domains->verdicts();
        if ([] === $verdicts) {
            return new ComplianceResult('compliance.domain', ComplianceResult::WARNING, 'compliance.domain_none', 'lawyer');
        }
        $by = [];
        foreach ($verdicts as $domain => $verdict) {
            $by[$verdict][] = $domain;
        }
        if (isset($by[DomainName::GENERIC])) {
            return new ComplianceResult('compliance.domain', ComplianceResult::MISSING, 'compliance.domain_generic', 'lawyer', ['domains' => implode(', ', $by[DomainName::GENERIC])]);
        }
        foreach ([DomainName::MIXED => 'compliance.domain_mixed', DomainName::UNNAMED => 'compliance.domain_unnamed', DomainName::LOCAL => 'compliance.domain_local'] as $verdict => $advice) {
            if (isset($by[$verdict])) {
                return new ComplianceResult('compliance.domain', ComplianceResult::WARNING, $advice, 'lawyer', ['domains' => implode(', ', $by[$verdict])]);
            }
        }

        return ComplianceResult::ok('compliance.domain', 'lawyer');
    }
}
