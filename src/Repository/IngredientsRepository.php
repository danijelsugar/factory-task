<?php

namespace App\Repository;

use App\Entity\Ingredients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Ingredients|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredients|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredients[]    findAll()
 * @method Ingredients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ingredients::class);
    }

    public function ingredientsById($lang,$id)
    {
        return $this->createQueryBuilder('i')
            ->select('i.id,i.slug,it.title')
            ->innerJoin('App\Entity\IngredientsTranslation', 'it', 'WITH', 'i.id=it.ingredient')
            ->where('i.id IN (:id)')
            ->andWhere('it.language=:lang')
            ->setParameter('lang', $lang)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
