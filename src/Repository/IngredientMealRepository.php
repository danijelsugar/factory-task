<?php

namespace App\Repository;

use App\Entity\IngredientMeal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IngredientMeal|null find($id, $lockMode = null, $lockVersion = null)
 * @method IngredientMeal|null findOneBy(array $criteria, array $orderBy = null)
 * @method IngredientMeal[]    findAll()
 * @method IngredientMeal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientMealRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IngredientMeal::class);
    }

    // /**
    //  * @return IngredientMeal[] Returns an array of IngredientMeal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IngredientMeal
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
