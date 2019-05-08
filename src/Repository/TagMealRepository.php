<?php

namespace App\Repository;

use App\Entity\TagMeal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TagMeal|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagMeal|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagMeal[]    findAll()
 * @method TagMeal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagMealRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TagMeal::class);
    }

    // /**
    //  * @return TagMeal[] Returns an array of TagMeal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TagMeal
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
