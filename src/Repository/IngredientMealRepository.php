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

    public function mealIngredients($meal)
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.ingredient)')
            ->where('m.meal = :meal')
            ->setParameter('meal', $meal)
            ->getQuery()
            ->getResult();
    }
}
