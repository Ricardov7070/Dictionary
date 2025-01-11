<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Words\Words_Dictionary;
use App\Models\FavoriteWords\FavoriteWords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class wordsManagementController extends Controller
{

    protected $modelWords;
    protected $modelFavorites;


    public function __construct (Words_Dictionary $modelWords, FavoriteWords $modelFavorites) {

        $this->modelWords = $modelWords;
        $this->modelFavorites = $modelFavorites;

    }


    public function wordIndex (Request $request): JsonResponse {

        try {

            $search = $request->query('search', null); 
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 5);
            $order = $request->query('order', 'asc');

            $query = $this->modelWords->newQuery();

            if ($search) {
                
                $query->where('words', 'like', '%' . $search . '%');

            }

            $paginatedWords = $this->modelWords->viewWords($limit, $search, $page, $order);

            return response()->json([
                'results' => collect($paginatedWords->items())->pluck('words'),
                'totalDocs' => $paginatedWords->total(),
                'page' => $paginatedWords->currentPage(),
                'totalPages' => $paginatedWords->lastPage(),
                'hasNext' => $paginatedWords->hasMorePages(),
                'hasPrev' => $paginatedWords->currentPage() > 1,
            ]);    
    
        } catch (ValidationException $e) {
      
            return response()->json([
                'message' => 'Error when viewing dictionary words!',
                'errors' => $e->errors(),
            ], 400);
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function favoriteRecord ($id_user, $word): JsonResponse {

        try {

            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $existingWord = $this->modelWords->checkWord($word);

            if (empty($existingWord)) {
            
                return response()->json([
                    'message' => 'Undefined Word!',
                ], 400);

            } else {

                $validation = $this->modelFavorites->checkWordStatus($existingWord, $id_user);

                if (!empty($validation)) {

                    return response()->json([
                        'message' => 'The word has already been added to the favorites list!'
                    ], 400);

                } else {

                    $word = $this->modelFavorites->favoriteWords($existingWord, $id_user);

                    return response()->json([
                        'success' => 'Added to favorites successfully!',
                        'favorite' => $word,
                    ], 200);

                }

            }
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function viewFavoriteRecords (Request $request, $id_user): JsonResponse {

        try {
     
            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $search = $request->query('search', null);
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 5);
            $order = $request->query('order', 'asc');

            $paginatedFavorites = $this->modelFavorites->viewFavoriteWords($id_user, $limit, $search, $page, $order);

            return response()->json([
                'results' => $paginatedFavorites->items(),
                'totalDocs' => $paginatedFavorites->total(),
                'page' => $paginatedFavorites->currentPage(),
                'totalPages' => $paginatedFavorites->lastPage(),
                'hasNext' => $paginatedFavorites->hasMorePages(),
                'hasPrev' => $paginatedFavorites->currentPage() > 1,
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Error when listing favorite words!',
                'errors' => $e->errors(),
            ], 400);

        } catch (\Throwable $th) {

            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);

        }

    }


    public function removeFavorite ($id_user, $word): JsonResponse {

        try {

            if (auth()->id() !== (int) $id_user) {

                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);

            }

            $existingWord = $this->modelWords->checkWord($word);

            if (empty($existingWord)) {
            
                return response()->json([
                    'message' => 'Undefined Word!',
                ], 400);

            } else {

                $validation = $this->modelFavorites->checkWordStatus($existingWord, $id_user);

                if (empty($validation)) {

                    return response()->json([
                        'message' => 'The word is not included in the favorites list!'
                    ], 400);

                } else {

                    $this->modelFavorites->removeWord($validation);

                    return response()->json([
                        'success' => 'Removed from favorites successfully!',
                    ], 201);

                }

            }
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


    public function selectSpecificWord ($word): JsonResponse {

        try {

            $existingWord = $this->modelWords->checkWord($word);

            if (empty($existingWord)) {
            
                return response()->json([
                    'title' => 'No Definitions Found',
                    'message' => 'Sorry pal, we couldn`t find definitions for the word you were looking for!',
                    'resolution' => 'You can try the search again at later time or head to the web instead.'
                ], 400);

            } else {

                $dataSelectedWord = $this->modelWords->wordData($word);

                dd($dataSelectedWord);

            }
    
        } catch (\Throwable $th) {

            dd($th);
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }

}
