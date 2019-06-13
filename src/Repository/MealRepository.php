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

    public function mealsByDiffTime($lang,$diffTime)
    {
        $diffTime = date('Y-m-d', $diffTime);

        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->where('mt.language=:lang')
            ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
            ->setParameter('lang', $lang)
            ->setParameter('diffTime', $diffTime)
            ->getQuery()
            ->getResult();
    }

    public function mealsByCategory($lang, $category)
    {
        if ($category === 'null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category IS NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.status=:created')
                ->setParameter('lang', $lang)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        } elseif ($category === '!null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category IS NOT NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.status=:created')
                ->setParameter('lang', $lang)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        } else {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category = :category')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.status=:created')
                ->setParameter('lang', $lang)
                ->setParameter('category', $category)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        }

        return $meals;

    }

    public function mealsByCategoryAndDiffTime($lang, $category, $diffTime)
    {
        $diffTime = date('Y-m-d', $diffTime);

        if ($category === 'null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category IS NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->setParameter('lang', $lang)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        } elseif ($category === '!null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category IS NOT NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->setParameter('lang', $lang)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        } else {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,m.createdAt,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->where('m.category = :category')
                ->andWhere('mt.language=:lang')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->setParameter('lang', $lang)
                ->setParameter('category', $category)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        }

        return $meals;
    }

    public function mealsByCategoryAndTags($lang, $category, $tags)
    {
        $num = count($tags);
        if ($category === 'null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category IS NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.status=:created')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        } elseif ($category === '!null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category IS NOT NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.status=:created')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        } else {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category=:category')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.status=:created')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('category', $category)
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('created', 'created')
                ->getQuery()
                ->getResult();
        }

        return $meals;
    }

    public function mealsByCategoryAndTagsAndDiffTime($lang, $category, $diffTime, $tags)
    {
        $diffTime = date('Y-m-d', $diffTime);
        $num = count($tags);
        
        if ($category === 'null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category IS NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        } elseif ($category === '!null') {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category IS NOT NULL')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        } else {
            $meals = $this->createQueryBuilder('m')
                ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
                ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
                ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
                ->where('m.category=:category')
                ->andWhere('mt.language=:lang')
                ->andWhere('tm.tag IN (:tags)')
                ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
                ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
                ->having('COUNT(DISTINCT(tm.tag)) =:num')
                ->setParameter('category', $category)
                ->setParameter('lang', $lang)
                ->setParameter('tags', array_values($tags))
                ->setParameter('num', $num)
                ->setParameter('diffTime', $diffTime)
                ->getQuery()
                ->getResult();
        }

        return $meals;
    }

    public function mealsByTag($lang, $tags)
    {
        $num = count($tags);
        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
            ->where('tm.tag IN (:tag)')
            ->andWhere('mt.language=:lang')
            ->andWhere('m.status=:created')
            ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
            ->having('COUNT(DISTINCT(tm.tag)) =:num')
            ->setParameter('lang', $lang)
            ->setParameter('tag', array_values($tags))
            ->setParameter('num', $num)
            ->setParameter('created', 'created')
            ->getQuery()
            ->getResult();

    }

    public function mealsByTagAndDiffTime($lang, $tags, $diffTime)
    {
        $num = count($tags);
        $diffTime = date('Y-m-d', $diffTime);

        return $this->createQueryBuilder('m')
            ->select('m.id,mt.title,mt.description,m.status,IDENTITY(m.category) as category')
            ->innerJoin('App\Entity\MealTranslation', 'mt', 'WITH', 'm.id=mt.meal')
            ->innerJoin('App\Entity\TagMeal', 'tm', 'WITH', 'm.id=tm.meal')
            ->where('tm.tag IN (:tag)')
            ->andWhere('mt.language=:lang')
            ->andWhere('m.createdAt > :diffTime OR m.updatedAt > :diffTime OR m.deletedAt > :diffTime')
            ->groupBy('m.id,mt.title,mt.description,m.status,m.category')
            ->having('COUNT(DISTINCT(tm.tag)) =:num')
            ->setParameter('lang', $lang)
            ->setParameter('tag', array_values($tags))
            ->setParameter('num', $num)
            ->setParameter('diffTime', $diffTime)
            ->getQuery()
            ->getResult();
    }

}
