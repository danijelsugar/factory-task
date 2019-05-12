<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LanguageRepository;
use App\Repository\MealRepository;
use App\Repository\TagMealRepository;
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
        TagMealRepository $trm,
        LanguageRepository $lr,
        PaginatorInterface $paginator
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
                $mealsWithCategoryNull = $mr->mealsWtihCategoryNull($langId);
                $mealsWithCategoryNull = $paginator->paginate(
                    $mealsWithCategoryNull,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                $meta = [
                    'currentPage' => $mealsWithCategoryNull->getCurrentPageNumber(),
                    'totalItems' => $mealsWithCategoryNull->getTotalItemCount(),
                    'itemsPerPage' => $mealsWithCategoryNull->getItemNumberPerPage(),
                    'totalPages' => $mealsWithCategoryNull->getPageCount(),
                ];
                foreach ($mealsWithCategoryNull as $meal) {
                    $data[] = [
                        'meal' => [
                            'id' => $meal['id'],
                            'title' => $meal['title'],
                            'description' => $meal['description'],
                            'status' => $meal['status'],
                            'category' => $meal['category'],
                        ],
                    ];
                }
                $links = [
                    'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                ];

            } elseif ($request->query->all()['category'] == '!null') {
                $mealsWithCategoryNotNull = $mr->mealsWithCategoyNotNull($langId);
                $mealsWithCategoryNotNull = $paginator->paginate(
                    $mealsWithCategoryNotNull,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                $meta = [
                    'currentPage' => $mealsWithCategoryNotNull->getCurrentPageNumber(),
                    'totalItems' => $mealsWithCategoryNotNull->getTotalItemCount(),
                    'itemsPerPage' => $mealsWithCategoryNotNull->getItemNumberPerPage(),
                    'totalPages' => $mealsWithCategoryNotNull->getPageCount(),
                ];
                foreach ($mealsWithCategoryNotNull as $meal) {
                    $data[] = [
                        'meal' => [
                            'id' => $meal['id'],
                            'title' => $meal['title'],
                            'description' => $meal['description'],
                            'status' => $meal['status'],
                        ],
                    ];
                }
                $links = [
                    'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                ];

            } else {
                $category = $request->query->all()['category'];
                $mealsByCategory = $mr->mealsByCategory($langId, $category);
                if (empty($mealsByCategory)) {
                    echo 'No meils with that category';
                    exit();
                } else {
                    $mealsByCategory = $paginator->paginate(
                        $mealsByCategory,
                        $request->query->getInt('page', 1),
                        $per_page
                    );
                    $meta = [
                        'currentPage' => $mealsByCategory->getCurrentPageNumber(),
                        'totalItems' => $mealsByCategory->getTotalItemCount(),
                        'itemsPerPage' => $mealsByCategory->getItemNumberPerPage(),
                        'totalPages' => $mealsByCategory->getPageCount(),
                    ];
                    foreach ($mealsByCategory as $meal) {
                        $data[] = [
                            'meal' => [
                                'id' => $meal['id'],
                                'title' => $meal['title'],
                                'description' => $meal['description'],
                                'status' => $meal['status'],
                            ],
                        ];
                    }
                    $links = [
                        'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    ];
                }
            }

        } elseif (isset($request->query->all()['tags'])) {
            $tags = explode(',', $request->query->all()['tags']);
            $mealsByTag = $mr->mealsByTag($langId, $tags);
            if (empty($mealsByTag)) {
                echo 'No meals with that tags';
                exit();
            } else {
                $mealsByTag = $paginator->paginate(
                    $mealsByTag,
                    $request->query->getInt('page', 1),
                    $per_page
                );
                $meta = [
                    'currentPage' => $mealsByTag->getCurrentPageNumber(),
                    'totalItems' => $mealsByTag->getTotalItemCount(),
                    'itemsPerPage' => $mealsByTag->getItemNumberPerPage(),
                    'totalPages' => $mealsByTag->getPageCount(),
                ];
                foreach ($mealsByTag as $meal) {
                    $data[] = [
                        'meal' => [
                            'id' => $meal['id'],
                            'title' => $meal['title'],
                            'description' => $meal['description'],
                            'status' => $meal['status'],
                        ],
                    ];
                }
                $links = [
                    'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                ];
            }

        } else {
            $meals = $mr->meals($langId);
            $meals = $paginator->paginate(
                $meals,
                $request->query->getInt('page', 1),
                $per_page
            );
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
                        'status' => $meal['status']
                ];
            }

            $links = [
                'self' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            ];
        }

        if (isset($request->query->all()['with'])) {
            if (strpos($request->query->all()['with'], 'category') !== false) {
               
            }
        }

        $response = new JsonResponse([
            'meta' => $meta,
            'data' => $data,
            'links' => $links,
        ]);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;

    }


}
