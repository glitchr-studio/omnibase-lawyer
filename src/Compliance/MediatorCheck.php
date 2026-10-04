<?php

namespace Base\Lawyer\Compliance;

use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The consumer mediator of the lawyers' profession is named on the site,
 * with how to reach it: the page /mediation and the legal notice print
 * lawyer.mediator. A warning when the configuration has emptied it.
 */
final class MediatorCheck implements ComplianceCheckInterface
{
    /** @param array{name?: ?string, address?: ?string, email?: ?string, url?: ?string} $mediator */
    public function __construct(#[Autowire('%lawyer.mediator%')] private readonly array $mediator = [])
    {
    }

    public function check(): ComplianceResult
    {
        $name = trim((string) ($this->mediator['name'] ?? ''));
        $reach = trim((string) ($this->mediator['address'] ?? '')).trim((string) ($this->mediator['url'] ?? '')).trim((string) ($this->mediator['email'] ?? ''));

        return '' !== $name && '' !== $reach
            ? ComplianceResult::ok('compliance.mediator', 'lawyer')
            : new ComplianceResult('compliance.mediator', ComplianceResult::WARNING, 'compliance.mediator_advice', 'lawyer');
    }
}
