<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Service\Record;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * « L'avocat qui ouvre ou modifie substantiellement un site Internet doit
 * en informer le conseil de l'Ordre sans délai et lui communiquer les noms
 * de domaine qui permettent d'y accéder » (RIN art. 10.5). The settings
 * keep which bar and the day the Ordre was told; the back office's memo
 * lists what to send.
 */
final class OrderDeclarationCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly Record $record)
    {
    }

    public function check(): ComplianceResult
    {
        if (null === $this->record->getBar()) {
            return new ComplianceResult('compliance.order', ComplianceResult::MISSING, 'compliance.order_bar_advice', 'lawyer');
        }
        $declared = $this->record->getDeclaredAt();
        if (null === $declared) {
            return new ComplianceResult('compliance.order', ComplianceResult::MISSING, 'compliance.order_advice', 'lawyer', ['bar' => $this->record->getBar()]);
        }
        if ($declared > new \DateTimeImmutable('tomorrow')) {
            return new ComplianceResult('compliance.order', ComplianceResult::WARNING, 'compliance.order_future', 'lawyer');
        }

        return ComplianceResult::ok('compliance.order', 'lawyer');
    }
}
