<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\IngredientMealRepository;
use App\Repository\IngredientsRepository;
use App\Repository\LanguageRepository;
use App\Repository\MealRepository;
use App\Repository\TagMealRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index(
        MealRepository $mr,
        Request $request,
        CategoryRepository $cr,
        TagMealRepository $tmr,
        LanguageRepository $lr,
        PaginatorInterface $paginator,
        TagRepository $tr,
        IngredientMealRepository $imr,
        IngredientsRepository $ir
    ) {
        if (isset($request->query->all()['per_page'])) {
            $per_page = $request->query->all()['per_page'];
        } else {
            $per_page = 5;
        }

        if (isset($request->query->all()['lang'])) {
            $langCode = $request->query->all()['lang'];
            $lang = $lr->findOneBy(
                [
                    'code' => $langCode,
                ]
            );
            if (empty($lang)) {
                $langCode = 'en';
                $lang = $lr->findOneBy(
                    [
                        'code' => $langCode,
                    ]
                );
                $langId = $lang->getId();
            } else {
                $langId = $lang->getId();
            }
            

        } else {
            $langCode = 'en';
            $lang = $lr->findOneBy(
                [
                    'code' => $langCode,
                ]
            );
            $langId = $lang->getId();
        }

        if (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($langId, $tags);
            //dump($tags);
        }
        /**
         * fliters meals by category
         */
        if (isset($request->query->all()['category'])) {
            if ($request->query->all()['category'] == 'null') {
                $meals = $mr->mealsWtihCategoryNull($langId);
                $meals = $paginator->paginate(
                    $meals,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                if (!isset($request->query->all()['with'])) {
                    foreach ($meals as $meal) {
                        $data[] = [
                            'meal' => [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                            ],
                        ];
                    }
                }
            } elseif ($request->query->all()['category'] == '!null') {
                $meals = $mr->mealsWithCategoyNotNull($langId);
                $meals = $paginator->paginate(
                    $meals,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                if (!isset($request->query->all()['with'])) {
                    foreach ($meals as $meal) {
                        $data[] = [
                            'meal' => [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                            ],
                        ];
                    }
                }
            } else {
                $category = $request->query->all()['category'];
                $meals = $mr->mealsByCategory($langId, $category);
                if (empty($meals)) {
                    echo 'No meils with that category';
                    exit();
                } else {
                    $meals = $paginator->paginate(
                        $meals,
                        $request->query->getInt('page', 1),
                        $per_page
                    );
                    if (!isset($request->query->all()['with'])) {
                        foreach ($meals as $meal) {
                            $data[] = [
                                'meal' => [
                                    'id' => $meal['id'],
                                    'title' => $meal['title'],
                                    'description' => $meal['description'],
                                    'status' => $meal['status'],
                                ],
                            ];
                        }
                    }
                }
            }

        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($langId, $tags);
            if (empty($meals)) {
                echo 'No meals with that tags';
                exit();
            } else {
                $meals = $paginator->paginate(
                    $meals,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                if (!isset($request->query->all()['with'])) {
                    foreach ($meals as $meal) {
                        $data[] = [
                            'meal' => [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                            ],
                        ];
                    }
                }
            }
        } else {
            $meals = $mr->meals($langId);
            $meals = $paginator->paginate(
                $meals,
                $request->query->getInt('page', 1),
                $per_page
            );
            if (!isset($request->query->all()['with'])) {
                foreach ($meals as $meal) {
                    $data[] = [
                        'meal' => [
                            'id' => $meal['id'],
                            'title' => $meal['title'],
                            'description' => $meal['description'],
                            'status' => $meal['status'],
                        ],
                    ];
                }
            }
            

        }

        if (isset($request->query->all()['with'])) {
            if (strpos($request->query->all()['with'], 'category') !== false) {
                foreach ($meals as $meal) {
                    $categoryId = $meal['category'];
                    $category = $cr->findCatById($categoryId, $langId);

                }
            } else {
                $category = [];
            }

            if (strpos($request->query->all()['with'], 'tag') !== false) {
                foreach ($meals as $meal) {
                    $mealId = $meal['id'];
                    $tags = $tmr->mealTags($mealId);
                    $tag = $tr->tagsById($langId, $tags);

                }
            } else {
                $tag = [];
            }

            if (strpos($request->query->all()['with'], 'ingredients') !== false) {
                foreach ($meals as $meal) {
                    $mealId = $meal['id'];
                    $ingredients = $imr->mealIngredients($mealId);
                    $ingredient = $ir->ingredientsById($langId, $ingredients);
                }
            } else {
                $ingredient = [];
            }
            foreach ($meals as $meal) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'category' => $category,
                    'tags' => $tag,
                    'ingredients' => $ingredient,
                ];
            }
        }

        $meta = [
            'currentPage' => $meals->getCurrentPageNumber(),
            'totalItems' => $meals->getTotalItemCount(),
            'itemsPerPage' => $meals->getItemNumberPerPage(),
            'totalPages' => $meals->getPageCount(),
        ];

        $links = [
            'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        ];

        $response = new JsonResponse([
            'meta' => $meta,
            'data' => $data,
            'links' => $links,
        ]);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;

    }

}
