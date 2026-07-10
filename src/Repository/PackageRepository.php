<?php

namespace App\Repository;

use App\Entity\Package;
use App\Entity\PackageSearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    //    /**
    //     * @return Package[] Returns an array of Package objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Package
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByFilter(PackageSearchFilter $filter): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin("p.category", "c")
            ->addSelect("c");

        $name = $filter->getName();
        if ($name) {
            $qb->andWhere("p.name LIKE :name")
                ->setParameter('name', '%' . $name . '%');
        }

        $category = $filter->getCategory();
        if($category) {
            $qb->andWhere("c.id = :category")
                ->setParameter('category', $category);
        }

        $minPrice = $filter->getMinPrice();
        if ($minPrice) {
            $qb->andWhere("p.price >= :minPrice")
                ->setParameter('minPrice', $minPrice);
        }

        $maxPrice = $filter->getMaxPrice();
        if ($maxPrice) {
            $qb->andWhere("p.price <= :maxPrice")
                ->setParameter('maxPrice', $maxPrice);
        }

        return $qb->getQuery()->getResult();
    }
}
