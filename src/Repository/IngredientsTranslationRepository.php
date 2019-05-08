<?php

namespace App\Repository;

use App\Entity\IngredientsTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IngredientsTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method IngredientsTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method IngredientsTranslation[]    findAll()
 * @method IngredientsTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientsTranslationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IngredientsTranslation::class);
    }

    // /**
    //  * @return IngredientsTranslation[] Returns an array of IngredientsTranslation objects
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
    public function findOneBySomeField($value): ?IngredientsTranslation
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
