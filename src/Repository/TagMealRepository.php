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

    public function getMealByTags($tags)
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.tag),IDENTITY(m.meal)')
            ->where('m.tag IN (:tags)')
            ->setParameter('tags', $tags)
            ->getQuery()
            ->getResult();
    }
}
