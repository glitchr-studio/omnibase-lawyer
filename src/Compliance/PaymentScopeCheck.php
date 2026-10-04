<?php

namespace Base\Lawyer\Compliance;

use Base\Lawyer\Payment\FeePayment;
use Base\Office\Compliance\ComplianceCheckInterface;
use Base\Office\Compliance\ComplianceResult;

/**
 * Paying online is for fees only (RIN art. 11.5); clients' funds go to the
 * CARPA (art. 6.2). Off: nothing to check. On without omnibase/marketplace
 * or without a route: the link is not shown, and the back office says why.
 * On and available: a reminder of what the payment page may take.
 */
final class PaymentScopeCheck implements ComplianceCheckInterface
{
    public function __construct(private readonly FeePayment $payment)
    {
    }

    public function check(): ComplianceResult
    {
        if (!$this->payment->isEnabled()) {
            return ComplianceResult::ok('compliance.payment', 'lawyer');
        }
        if (!$this->payment->isAvailable()) {
            return new ComplianceResult('compliance.payment', ComplianceResult::WARNING, $this->payment->isInstalled() ? 'compliance.payment_route' : 'compliance.payment_missing', 'lawyer');
        }

        return new ComplianceResult('compliance.payment', ComplianceResult::OK, 'compliance.payment_scope', 'lawyer');
    }
}
