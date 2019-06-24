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
                    
                    $data = $this->getMealsWith($request,$meals,$cr,$tmr,$tr,$imr,$ir,$langId);
                    
                    $meta = [
                        'currentPage' => $meals->getCurrentPageNumber(),
                        'totalItems' => $meals->getTotalItemCount(),
                        'itemsPerPage' => $meals->getItemNumberPerPage(),
                        'totalPages' => $meals->getPageCount(),
                    ];

                    $links = $this->returnLinks($meals, $request);
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
                    
                    $links = $this->returnLinks($meals, $request);
                    
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
            if (!ctype_digit($request->query->all()['diff_time']) || $request->query->all()['diff_time'] <= 0 ||
                is_float($request->query->all()['diff_time'])) {
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
        } elseif (isset($request->query->all()['category']) && isset($request->query->all()['diff_time'])) {
            $category = $request->query->all()['category'];
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByCategoryAndDiffTime($lang, $category, $diffTime);
        } elseif (isset($request->query->all()['category']) && isset($request->query->all()['tags'])) {
            $category = $request->query->all()['category'];
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByCategoryAndTags($lang, $category, $tags);
        } elseif (isset($request->query->all()['category'])) {
            $category = $request->query->all()['category'];
            $meals = $mr->mealsByCategory($lang, $category);
        } elseif (isset($request->query->all()['tags']) && isset($request->query->all()['diff_time'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByTagAndDiffTime($lang, $tags, $diffTime);
        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($lang, $tags);
        } elseif (isset($request->query->all()['diff_time'])) {
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByDiffTime($lang,$diffTime);
        } else {
            $meals = $mr->meals($lang);
        }

        $meals = $this->paginate($meals,$request,$paginator);

        return $meals;
    }

    /**
     * @param $meals
     * @param Request $request
     * @param CategoryRepository $cr
     * @param TagMealRepository $tmr
     * @param TagRepository $tr
     * @param IngredientMealRepository $imr
     * @param IngredientsRepository $ir
     * @param lang
     * @return $data
     */
    public function getMealsWith($request, $meals, $cr, $tmr, $tr, $imr, $ir, $langId)
    {
        if (strpos($request->query->all()['with'], 'category') !== false ) {
            $categoryWith = true;
        } else {
            $categoryWith = false;
        }

        if (strpos($request->query->all()['with'], 'tags') !== false) {
            $tagWith = true;
        } else {
            $tagWith= false;
        }
        if (strpos($request->query->all()['with'], 'ingredients') !== false) {
            $ingredientWith = true;
        } else {
            $ingredientWith = false;
        }
        
        

        foreach ($meals as $meal) {
            
            $categoryId = $meal['category'];
            $category = $cr->findCatById($categoryId, $langId);
            if (empty($category)) {
                $category = null;
            }
        
            $mealId = $meal['id'];
            $tags = $tmr->mealTags($mealId);
            $tag = $tr->tagsById($langId, $tags);
        
        
            $mealId = $meal['id'];
            $ingredients = $imr->mealIngredients($mealId);
            $ingredient = $ir->ingredientsById($langId, $ingredients);
            

            if ($categoryWith && $tagWith && $ingredientWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'category' => $category,
                    'tags' => $tag,
                    'ingredients' => $ingredient
                ];
            } elseif ($categoryWith && $tagWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'category' => $category,
                    'tags' => $tag,
                ];
            } elseif ($categoryWith && $ingredientWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'category' => $category,
                    'ingredients' => $ingredient
                ];
            } elseif ($tagWith && $ingredientWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'tags' => $tag,
                    'ingredients' => $ingredient
                ];
            } elseif ($categoryWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'category' => $category,
                ];
            } elseif ($tagWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'tags' => $tag,
                ];
            } elseif ($ingredientWith) {
                $data[] = [
                    'id' => $meal['id'],
                    'title' => $meal['title'],
                    'description' => $meal['description'],
                    'status' => $meal['status'],
                    'ingredients' => $ingredient
                ];
            }

            
        }

        return $data;
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

    /**
     * @param $meals
     * @return JsonResponse
     */
    public function returnLinks($meals,$request)
    {

        if ($meals->getCurrentPageNumber() < 2) {
            $prev = null;
        } else {
            $path = parse_url($request->getUri());
            $urlString = $path['query'];
            parse_str($urlString,$newString);
            $newString['page'] =  $meals->getCurrentPageNumber()-1;
            $prev = http_build_query($newString);
            $prev = $path['scheme'] . '://' . $path['host'] . '/?' . $prev;
        }

        if ($meals->getCurrentPageNumber() == $meals->getPageCount()) {
            $next = null;
        } else {
            $path = parse_url($request->getUri());
            $urlString = $path['query'];
            parse_str($urlString,$newString);
            $newString['page'] =  $meals->getCurrentPageNumber()+1;
            $next = http_build_query($newString);
            $next = $path['scheme'] . '://' . $path['host'] . '/?' . $next;
        }

        $links = [
            'prev' => $prev,
            'self' => $request->getUri(),
            'next' => $next
        ];
        return $links;
    }
}
