<?php

namespace Base\Lawyer\Repository;

use Base\Lawyer\Entity\Area;
use Doctrine\Persistence\ManagerRegistry;

/**
 * The finders are omnibase/office's (findActive(), findOneActiveBySlug(), findForMember()).
 *
 * @extends \Base\Office\Repository\AreaRepository<Area>
 */
class AreaRepository extends \Base\Office\Repository\AreaRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Area::class);
    }
}
