<?php

namespace App\Repository;

use App\Entity\MealTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MealTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MealTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MealTranslation[]    findAll()
 * @method MealTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealTranslationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MealTranslation::class);
    }

    // /**
    //  * @return MealTranslation[] Returns an array of MealTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MealTranslation
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
