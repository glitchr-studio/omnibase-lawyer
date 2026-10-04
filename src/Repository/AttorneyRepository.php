<?php

namespace Base\Lawyer\Repository;

use Base\Lawyer\Entity\Attorney;
use Base\Office\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Attorney> */
class AttorneyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attorney::class);
    }

    public function findOneByMember(?Member $member): ?Attorney
    {
        return null === $member ? null : $this->findOneBy(['member' => $member]);
    }

    /** @return list<Attorney> those the site shows */
    public function findVisible(): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.member', 'm')->addSelect('m')
            ->andWhere('m.visible = true')->andWhere('m.active = true')
            ->orderBy('m.position', 'ASC')->addOrderBy('m.displayName', 'ASC')
            ->getQuery()->getResult();
    }
}
