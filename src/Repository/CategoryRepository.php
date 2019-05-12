<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findCatById($id,$lang)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id,c.slug,ct.title')
            ->innerJoin('App\Entity\CategoryTranslation', 'ct', 'WITH', 'c.id=ct.category')
            ->where('c.id = :id')
            ->andWhere('ct.language=:lang')
            ->setParameter('lang', $lang)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
