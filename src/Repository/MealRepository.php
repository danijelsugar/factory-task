<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Meal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meal[]    findAll()
 * @method Meal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MealRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    public function retrunAll()
    {
        return $this->createQueryBuilder('m')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }

    public function meals($lang)
    {
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->where('mt.language=:lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getResult();
    }

    public function mealsWtihCategoryNull($lang)
    {
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->where('m.category IS NULL')
            ->andWhere('mt.language=:lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getResult();
    }

    public function mealsWithCategoyNotNull($lang)
    {
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->where('m.category IS NOT NULL')
            ->andWhere('mt.language=:lang')
            ->setParameter('lang', $lang)
            ->getQuery()
            ->getResult();
    }

    public function mealsByCategory($lang, $category)
    {
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->where('m.category = :category')
            ->andWhere('mt.language=:lang')
            ->setParameter('lang', $lang)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    public function mealsByTag($lang,$tags)
    {
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
            ->where('tm.tag IN (:tag)')
            ->andWhere('mt.language=:lang')
            ->setParameter('lang', $lang)
            ->setParameter('tag', array_values($tags))
            ->getQuery()
            ->getResult();

    }

}
