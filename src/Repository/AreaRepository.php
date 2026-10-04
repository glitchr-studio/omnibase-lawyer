<?php

namespace Base\Lawyer\Repository;

use Base\Lawyer\Entity\Area;
use Base\Office\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Area> */
class AreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Area::class);
    }

    /** @return list<Area> */
    public function findActive(): array
    {
        return $this->findBy(['active' => true], ['position' => 'ASC', 'name' => 'ASC']);
    }

    public function findOneActiveBySlug(string $slug): ?Area
    {
        return $this->findOneBy(['slug' => $slug, 'active' => true]);
    }

    /** @return list<Area> the fields a member follows */
    public function findForMember(Member $member): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.members', 'm')->andWhere('m = :member')->setParameter('member', $member)
            ->andWhere('a.active = true')->orderBy('a.position', 'ASC')
            ->getQuery()->getResult();
    }
}
