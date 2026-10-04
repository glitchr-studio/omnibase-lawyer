<?php

namespace Base\Lawyer;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The lawyers' regime, on omnibase/office: what a law firm's site owes to
 * article 10 of the Règlement intérieur national (RIN), written as checks
 * the back office runs - the domain name (10.5), specialisations from the
 * certified list only and three dominant fields at most (10.2), wording
 * free of comparison (10.2), the firm identified (10.2), the site and what
 * it says declared to the conseil de l'Ordre (10.2, 10.3, 10.5), the fees
 * explained and the fee agreement announced (11.1, 11.2), the consumer
 * mediator of the profession - and its own pages: the fields of practice,
 * the fees, the mediation, the client's space, the memo for the Ordre.
 * Paying online is for fees only, through omnibase/marketplace when it is
 * installed; the bundle itself takes no payment.
 *
 * Compliance built into the code does not replace the Ordre's control.
 */
class LawyerBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /** Modern layout: the class lives in src/, the bundle root is the package root. */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->setMapping($this->getPath().'/src/Entity', 'Base\Lawyer\Entity', 'App\Entity\Lawyer');
        $this->setMapping($this->getPath().'/src/Repository', 'Base\Lawyer\Repository', 'App\Repository\Lawyer');
    }
}
