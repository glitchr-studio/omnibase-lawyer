<?php

namespace Base\Lawyer\Twig;

use Base\Lawyer\Entity\Attorney;
use Base\Lawyer\Payment\FeePayment;
use Base\Lawyer\Repository\AreaRepository;
use Base\Lawyer\Repository\AttorneyRepository;
use Base\Lawyer\Service\Record;
use Base\Office\Entity\Member;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The regime in templates: lawyer_record() (the firm, its bar, its fees as
 * typed in the settings), lawyer_attorney(member) (bar, oath, certified
 * specialisations, dominant fields), lawyer_areas(member|null),
 * lawyer_mediator(), lawyer_payment() (the hook for paying fees online),
 * lawyer_rules_url().
 */
class LawyerExtension extends AbstractExtension
{
    public function __construct(
        private readonly Record $record,
        private readonly AreaRepository $areas,
        private readonly AttorneyRepository $attorneys,
        private readonly FeePayment $payment,
        #[Autowire('%lawyer.mediator%')] private readonly array $mediator = [],
        #[Autowire('%lawyer.rules_url%')] private readonly ?string $rulesUrl = null,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lawyer_record', fn (): Record => $this->record),
            new TwigFunction('lawyer_attorney', fn (?Member $member): ?Attorney => $this->attorneys->findOneByMember($member)),
            new TwigFunction('lawyer_areas', fn (?Member $member = null): array => null === $member ? $this->areas->findActive() : $this->areas->findForMember($member)),
            new TwigFunction('lawyer_mediator', fn (): array => $this->mediator),
            new TwigFunction('lawyer_payment', fn (): FeePayment => $this->payment),
            new TwigFunction('lawyer_rules_url', fn (): ?string => $this->rulesUrl),
        ];
    }
}
