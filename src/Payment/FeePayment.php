<?php

namespace Base\Lawyer\Payment;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The hook towards paying a fee note online. The bundle takes no payment
 * itself: when omnibase/marketplace is installed and the application has
 * turned lawyer.payment.enabled on and named its route, the fees page
 * links to it - for fees only (« Les honoraires sont payés [...] notamment
 * en espèces, par chèque, par virement, par billet à ordre et par carte
 * bancaire », RIN art. 11.5). Clients' funds never go through it: « L'avocat
 * qui manie les fonds, effets ou valeurs de manière accessoire à une
 * opération juridique ou judiciaire doit les déposer sans délai à la
 * CARPA » (RIN art. 6.2).
 */
class FeePayment
{
    public const MARKETPLACE = 'Base\\Marketplace\\MarketplaceBundle';

    public function __construct(
        #[Autowire('%lawyer.payment.enabled%')] private readonly bool $enabled = false,
        #[Autowire('%lawyer.payment.route%')] private readonly ?string $route = null,
        private readonly ?bool $installed = null,
    ) {
    }

    /** Whether omnibase/marketplace is there to take the payment. */
    public function isInstalled(): bool
    {
        return $this->installed ?? class_exists(self::MARKETPLACE);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /** Shown on the fees page only when all three hold. */
    public function isAvailable(): bool
    {
        return $this->enabled && $this->isInstalled() && null !== $this->route && '' !== $this->route;
    }

    public function getRoute(): ?string
    {
        return $this->isAvailable() ? $this->route : null;
    }
}
