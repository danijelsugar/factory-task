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

        if (!empty($request->query->all())) {

            if (!$this->validate($request)) {
                return new JsonResponse(
                    [
                        'message' => 'Invalid request',
                    ]
                );
            } 
            
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
                    return new JsonResponse(
                        [
                            'message' => 'Language is not supported',
                        ]
                    );
                }
                $langId = $lang->getId();
            } else {
                $langCode = 'en';
                $lang = $lr->findOneBy(
                    [
                        'code' => $langCode,
                    ]
                );
                $langId = $lang->getId();
            }

            $meals = $this->getMeals($request, $mr, $langId);

        }

        /**
         * fliters meals by category
         */
        if (isset($request->query->all()['category'])) {
            //gets all meals with category null
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
                //all meals with category not null
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
            }
            //meals with given category
            else {
                $category = $request->query->all()['category'];
                $meals = $mr->mealsByCategory($langId, $category);
                if (empty($meals)) {
                    return new JsonResponse(
                        [
                            'message' => 'No meils with that category',
                        ]
                    );
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
            //returns meals with given tags(if multiple tags are send return meals that contain at least one tag)
        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($langId, $tags);
            if (empty($meals)) {
                return new JsonResponse([
                    'message' => 'No meals with that tags',
                ]);
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
        //if no tags or category are sent returns all meals
        else {
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

            if (strpos($request->query->all()['with'], 'tags') !== false) {
                foreach ($meals as $meal) {
                    $mealId = $meal['id'];
                    //dump($mealId);
                    $tags = $tmr->mealTags($mealId);
                    //dump($tags);
                    $tag[] = $tr->tagsById($langId, $tags);
                    //dump($tag);

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

        return $this->returnMeals($meta, $data, $links);

    }

    /**
     * @param $meta
     * @param $data
     * @param $links
     * @return JsonResponse
     */
    public function returnMeals($meta, $data, $links)
    {
        $response = new JsonResponse(
            [
                'meta' => $meta,
                'data' => $data,
                'links' => $links,
            ]
        );
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    public function getMeals($request, $mr, $lang)
    {
        if (isset($request->query->all()['category']) && isset($request->query->all()['diff_time'])) {
            $category = $request->query->all()['category'];
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByCategoryAndDiffTime($lang, $category, $diffTime);
            dump($meals);
        } elseif (isset($request->query->all()['category']) && isset($request->query->all()['tags'])) {
            $category = $request->query->all()['category'];
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByCategoryAndTags($lang,$category,$tags);
            dump($meals);
        } elseif (isset($request->query->all()['category'])) {
            $category = $request->query->all()['category'];
            $meals = $mr->mealsByCategory($lang, $category);
            dump($meals);
        }

        if ((isset($request->query->all()['tags']) && isset($request->query->all()['diff_time']))) {
            $tags = explode(',', $request->query->all()['tags']);
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByTagAndDiffTime($lang,$tags,$diffTime);
            dump($meals);
        }
    }

    public function validate($request)
    {

        if (isset($request->query->all()['per_page'])) {
            if (!ctype_digit($request->query->all()['per_page'])) {
                $valid = false;
            } else {
                $valid = true;
            }
        }

        if (isset($request->query->all()['category'])) {
            $category = $request->query->all()['category'];
            if (ctype_digit($category) || $category === 'null' || $category === '!null') {
                $valid = true;
            } else {
                $valid = false;
            }
        }

        if (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            foreach ($tags as $tag) {
                if (ctype_digit($tag)) {
                    $valid = true;
                } else {
                    $valid = false;
                }
            }
        }

        if (isset($request->query->all()['lang'])) {
            if (is_string($request->query->all()['lang']) && !ctype_digit($request->query->all()['lang']) &&
                strlen($request->query->all()['lang']) == 2) {
                $valid = true;
            } else {
                $valid = false;
            }
        }

        if (isset($request->query->all()['diff_time'])) {
            if (!ctype_digit($request->query->all()['diff_time']) && $request->query->all()['diff_time'] <= 0) {
                $valid= false;
            } else {
                $valid = true;
            }
        }

        if (isset($request->query->all()['page'])) {
            if (!ctype_digit($request->query->all()['page'])) {
                $valid = false;
            } else {
                $valid = true;
            }
        }

        if ($valid) {
            return true;
        } else {
            return false;
        }

    }
}
