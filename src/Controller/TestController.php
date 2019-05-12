<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\IngredientMeal;
use App\Entity\Ingredients;
use App\Entity\IngredientsTranslation;
use App\Entity\Meal;
use App\Entity\MealTranslation;
use App\Entity\Tag;
use App\Entity\TagMeal;
use App\Entity\TagTranslation;
use App\Repository\CategoryRepository;
use App\Repository\IngredientsRepository;
use App\Repository\LanguageRepository;
use App\Repository\MealRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function index(EntityManagerInterface $em, CategoryRepository $ct, LanguageRepository $lr)
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $category = new Category();

            $category->setSlug($faker->word);
            $em->persist($category);
            $em->flush();

            $categoryId = $category->getId();
            $cat = $ct->findOneBy(
                [
                    'id' => $categoryId,
                ]
            );

            $language = $lr->findAll();

            foreach ($language as $l) {
                $categoryTranslation = new CategoryTranslation();
                $categoryTranslation->setCategory($cat);
                $categoryTranslation->setLanguage($l);
                $categoryTranslation->setTitle($faker->word);
                $em->persist($categoryTranslation);
            }
            $em->flush();
        }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    /**
     * @Route("/tag", name="tag")
     */
    public function tag(EntityManagerInterface $em, TagRepository $tr, LanguageRepository $lr)
    {

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $tag = new Tag();

            $tag->setSlug($faker->word);
            $em->persist($tag);
            $em->flush();

            $tagId = $tag->getId();
            $t = $tr->findOneBy([
                'id' => $tagId,
            ]);

            $language = $lr->findAll();

            foreach ($language as $l) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setTag($t);
                $tagTranslation->setLanguage($l);
                $tagTranslation->setTitle($faker->word);
                $em->persist($tagTranslation);
            }
            $em->flush();
        }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    /**
     * @Route("/ingredient", name="ingredient")
     */
    public function ingredient(EntityManagerInterface $em, IngredientsRepository $it, LanguageRepository $lr)
    {

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $ingredient = new Ingredients();

            $ingredient->setSlug($faker->word);
            $em->persist($ingredient);
            $em->flush();
            $ingredientId = $ingredient->getId();

            $in = $it->findOneBy(
                [
                    'id' => $ingredientId,
                ]
            );

            $language = $lr->findAll();

            foreach ($language as $l) {
                $ingredientTranslation = new IngredientsTranslation();
                $ingredientTranslation->setIngredient($in);
                $ingredientTranslation->setLanguage($l);
                $ingredientTranslation->setTitle($faker->word);
                $em->persist($ingredientTranslation);
            }
            $em->flush();
        }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    /**
     * @Route("/meal", name="meal")
     */
    public function meal(EntityManagerInterface $em, CategoryRepository $cp, MealRepository $mt, LanguageRepository $lr)
    {
        $faker = Faker\Factory::create();
        for ($i = 0; $i < 10; $i++) {
            /**
             * selects random status from array
             */
            $status = ['created', 'deleted', 'updated'];
            $key = array_rand($status);

            /**
             * finds random category id then pass category object to meal
             */
            $categories = $cp->findAll();
            $category = array_rand($categories) + 1;

            $category = $cp->findOneBy(
                [
                    'id' => $category,
                ]
            );

            $meal = new Meal();
            $meal->setStatus($status[$key]);
            $meal->setCategory($category);
            $em->persist($meal);
            $em->flush();
            $mealId = $meal->getId();

            $m = $mt->findOneBy(
                [
                    'id' => $mealId,
                ]
            );

            $language = $lr->findAll();

            foreach ($language as $l) {
                $mealTranslation = new MealTranslation();
                $mealTranslation->setMeal($m);
                $mealTranslation->setLanguage($l);
                $mealTranslation->setTitle($faker->word);
                $mealTranslation->setDescription($faker->sentence);
                $em->persist($mealTranslation);
            }
            $em->flush();
        }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    /**
     * @Route("/tag-meal", name="tag-meal")
     */
    public function tagMeal(EntityManagerInterface $em, MealRepository $mr, TagRepository $tr)
    {

        $meals = $mr->findAll();
        $tags = $tr->findAll();

        foreach ($meals as $m) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $tag = array_rand($tags);
                $tag = $tags[$tag];
                $tagMeal = new TagMeal();
                $tagMeal->setMeal($m);
                $tagMeal->setTag($tag);
                $em->persist($tagMeal);
            }

        }
        $em->flush();

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);

    }

    /**
     * @Route("/ingredient-meal", name="ingredient-meal")
     */
    public function ingredientMeal(EntityManagerInterface $em, MealRepository $mr, IngredientsRepository $ir)
    {

        $meals = $mr->findAll();
        $ingredients = $ir->findAll();

        foreach ($meals as $m) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $ingredient = array_rand($ingredients);
                $ingredient = $ingredients[$ingredient];
                $ingredientMeal = new IngredientMeal();
                $ingredientMeal->setMeal($m);
                $ingredientMeal->setIngredient($ingredient);
                $em->persist($ingredientMeal);
            }
        }
        $em->flush();

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
