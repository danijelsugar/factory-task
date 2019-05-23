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

            $meals = $this->getMeals($request, $mr, $langId, $paginator);
            
            if (empty($meals->getItems())) {
                return new JsonResponse(
                    [
                        'message' => 'No meals',
                    ]
                );
            } else {
                if (isset($request->query->all()['with'])) {
                    if (strpos($request->query->all()['with'], 'category') !== true ) {
                        foreach ($meals as $meal) {
                            $categoryId = $meal['category'];
                            $category = $cr->findCatById($categoryId, $langId);
                        }
                    } else {
                        $category = '';
                    }

                    if (strpos($request->query->all()['with'], 'tag') !== false) {
                        foreach ($meals as $meal) {
                            $mealId = $meal['id'];
                            $tags = $tmr->mealTags($mealId);
                            $tag = $tr->tagsById($langId, $tags);
                        }
                    } else {
                        $tag= '';
                    }

                    if (strpos($request->query->all()['with'], 'ingredients') !== false) {
                        foreach ($meals as $meal) {
                            $mealId = $meal['id'];
                            $ingredients = $imr->mealIngredients($mealId);
                            $ingredient = $ir->ingredientsById($langId, $ingredients);
                        }
                    } else {
                        $ingredient = '';
                    }
                    
                    $meta = [
                        'currentPage' => $meals->getCurrentPageNumber(),
                        'totalItems' => $meals->getTotalItemCount(),
                        'itemsPerPage' => $meals->getItemNumberPerPage(),
                        'totalPages' => $meals->getPageCount(),
                    ];

                    foreach ($meals as $meal) {
                        $data[] = [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                                'category' => $category,
                                'tags' => $tag,
                                'ingredients' => $ingredient
                        ];
                    }
                    
                    $links = [
                        'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    ];
                } else {
                    

                    $meta = [
                        'currentPage' => $meals->getCurrentPageNumber(),
                        'totalItems' => $meals->getTotalItemCount(),
                        'itemsPerPage' => $meals->getItemNumberPerPage(),
                        'totalPages' => $meals->getPageCount(),
                    ];

                    foreach ($meals as $meal) {
                        $data[] = [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                        ];
                    }
                    
                    $links = [
                        'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    ];
                }
               
                return $this->returnMeals($meta, $data, $links);

            }

        } else {
            return new JsonResponse(
                [
                    'message' => 'Enter get parameters',
                ]
            );
        }

    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function validate($request)
    {
        $validArray = [];
        if (isset($request->query->all()['per_page'])) {
            if (!ctype_digit($request->query->all()['per_page'])) {
                $validPerPage = false;
                $validArray[] = $validPerPage;
            } else {
                $validPerPage = true;
                $validArray[] = $validPerPage;
            }
        }

        if (isset($request->query->all()['category'])) {
            $category = $request->query->all()['category'];
            if (ctype_digit($category) || $category === 'null' || $category === '!null') {
                $validCategory = true;
                $validArray[] = $validCategory;
            } else {
                $validCategory = false;
                $validArray[] = $validCategory;
            }
        }

        if (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            foreach ($tags as $tag) {
                if (ctype_digit($tag)) {
                    $validTags = true;
                    $validArray[] = $validTags;
                } else {
                    $validTags = false;
                    $validArray[] = $validTags;
                }
            }
        }

        if (isset($request->query->all()['lang'])) {
            if (is_string($request->query->all()['lang']) && !ctype_digit($request->query->all()['lang']) &&
                strlen($request->query->all()['lang']) == 2) {
                $validLang = true;
                $validArray[] = $validLang;
            } else {
                $validLang = false;
                $validArray[] = $validLang;
            }
        }

        if (isset($request->query->all()['diff_time'])) {
            if (!ctype_digit($request->query->all()['diff_time']) && $request->query->all()['diff_time'] <= 0) {
                $validDiffTime = false;
                $validArray[] = $validDiffTime;
            } else {
                $validDiffTime = true;
                $validArray[] = $validDiffTime;
            }
        }

        if (isset($request->query->all()['page'])) {
            if (!ctype_digit($request->query->all()['page'])) {
                $validPage = false;
                $validArray[] = $validPage;
            } else {
                $validPage = true;
                $validArray[] = $validPage;
            }
        }
        
        if (in_array(false, $validArray)) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * @param Reuqest $request
     * @param MealRepository $mr
     * @param lang
     */
    public function getMeals($request, $mr, $lang, $paginator)
    {
        
        if (isset($request->query->all()['category']) && isset($request->query->all()['tags']) && isset($request->query->all()['diff_time'])) {
            $category = $request->query->all()['category'];
            $diffTime = $request->query->all()['diff_time'];
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByCategoryAndTagsAndDiffTime($lang,$category,$diffTime,$tags);
            dump('1');
        } elseif (isset($request->query->all()['category']) && isset($request->query->all()['diff_time'])) {
            $category = $request->query->all()['category'];
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByCategoryAndDiffTime($lang, $category, $diffTime);
            dump('2');
        } elseif (isset($request->query->all()['category']) && isset($request->query->all()['tags'])) {
            $category = $request->query->all()['category'];
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByCategoryAndTags($lang, $category, $tags);
            dump('3');
        } elseif (isset($request->query->all()['category'])) {
            $category = $request->query->all()['category'];
            $meals = $mr->mealsByCategory($lang, $category);
            dump('4');
        } elseif (isset($request->query->all()['tags']) && isset($request->query->all()['diff_time'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByTagAndDiffTime($lang, $tags, $diffTime);
            dump('5');
        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($lang, $tags);
            dump('6');
        } elseif (isset($request->query->all()['diff_time'])) {
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByDiffTime($lang,$diffTime);
            dump('7');
        } else {
            $meals = $mr->meals($lang);
        }

        

        $meals = $this->paginate($meals,$request,$paginator);

        return $meals;
    }

    /**
     * @param $meals
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return PaginatorInterface
     */
    public function paginate($meals, $request, $paginator)
    {
        $meals = $paginator->paginate(
            $meals,
            $request->query->getInt('page', 1),
            $request->query->getInt('per_page', 5)
        );

        return $meals;
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
}
