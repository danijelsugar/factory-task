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
            if (empty($meals)) {
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
                            $category[$categoryId] = $cr->findCatById($categoryId, $langId);
                        }
                    }

                    if (strpos($request->query->all()['with'], 'tag') !== false) {
                        foreach ($meals as $meal) {
                            $mealId = $meal['id'];
                            $tags = $tmr->mealTags($mealId);
                            $tag = $tr->tagsById($langId, $tags);
                            //dump($tag);
                        }
                    }
                } else {
                    $with = false;
                }
                //$mealsArray = $this->getMealDetails($meals,$with,$langId,$request);
               
                $meals = $paginator->paginate(
                    $meals,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                foreach ($meals as $meal) {
                    $data[] = [
                        'meal' => [
                            'id' => $meal['id'],
                            'title' => $meal['title'],
                            'description' => $meal['description'],
                            'status' => $meal['status'],
                            'category' => $category,
                            'tags' => $tag
                        ],
                    ];
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
                $valid = false;
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

    public function getMeals($request, $mr, $lang)
    {
        if (isset($request->query->all()['category']) && isset($request->query->all()['diff_time'])) {
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

        }

        if (isset($request->query->all()['tags']) && isset($request->query->all()['diff_time'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $diffTime = $request->query->all()['diff_time'];
            $meals = $mr->mealsByTagAndDiffTime($lang, $tags, $diffTime);

        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $meals = $mr->mealsByTag($lang, $tags);

        }

        return $meals;
    }

    public function getMealDetails($meals,$with,$lang,$request) 
    {
        if($with) {
            if (strpos($request->query->all()['with'], 'category')) {
               return 'ee';
            }
        }
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
