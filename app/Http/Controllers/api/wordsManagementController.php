<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Words\Words_Dictionary;
use App\Models\FavoriteWords\FavoriteWords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Words\LogWords_Visited;


class wordsManagementController extends Controller
{

    protected $modelWords;
    protected $modelFavorites;
    protected $modelLogWords;


// Método Construtor
    public function __construct (Words_Dictionary $modelWords, FavoriteWords $modelFavorites, LogWords_Visited $modelLogWords) {

        $this->modelWords = $modelWords;
        $this->modelFavorites = $modelFavorites;
        $this->modelLogWords = $modelLogWords;

    }


/**
 * @OA\Get(
 *     path="/api/entries/en?search=fire&limit=15&page=2&order=desc",
 *     summary="Realiza a visualização de todas as palavras presentes no dicionário.",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error when viewing dictionary words!"
 *     ),
 * )
 */
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


/**
 * @OA\Post(
 *     path="/api/entries/en/{id_user}/{word}/favorite",
 *     summary="Adiciona a lista de favoritos a palavra selecionada pelo o usuário",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=200,
 *         description="Added to favorites successfully!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Undefined Word!, The word has already been added to the favorites list!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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


/**
 * @OA\Post(
 *     path="/api/user/me/{id_user}/favorites?search=fire&limit=15&page=2&order=desc",
 *     summary="Realiza a visualização de todas as palavras adicionadas na lista de favoritos do usuário.",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error when listing favorite words!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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


/**
 * @OA\Delete(
 *     path="/api/entries/en/{id_user}/{word}/unfavorite",
 *     summary="Realiza a exclusão das palavras adicionadas na lista de favoritos do usuário.",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=204,
 *         description="Removed from favorites successfully!"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="The word is not included in the favorites list!, Undefined Word!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
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
                    ], 204);

                }

            }
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


/**
 * @OA\Post(
 *     path="/api/entries/en/{word}",
 *     summary="Retorna os dados da palavra pesquisada pelo o usuário.",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="No Definitions Found. Sorry pal, we couldn`t find definitions for the word you were looking for! You can try the search again at later time or head to the web instead."
 *     ),
 * )
 */
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

                $this->modelLogWords->wordHistory($existingWord, auth()->id());

                $dataSelectedWord = $this->modelWords->wordData($word);

                return response()->json([
                    'results' => $dataSelectedWord,
                ]);
        
            }
    
        } catch (\Throwable $th) {
        
            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);
    
        }   

    }


/**
 * @OA\Get(
 *     path="/api/user/me/{id_user}/history?search=fire&limit=15&page=2&order=desc",
 *     summary="Realiza a visualização do histórico de palavras pesquisadas pelo o usuário.",
 *     tags={"Gerenciamento de Palavras"},
 *     @OA\Response(
 *         response=500,
 *         description="An error occurred, try again!"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error when viewing the history of searched words!"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access."
 *     ),
 * )
 */
    public function viewSelectedRecords (Request $request, $id_user): JsonResponse {

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

            $paginatedHistory = $this->modelLogWords->viewSelectedWords($id_user, $limit, $search, $page, $order);

            return response()->json([
                'results' => $paginatedHistory->items(),
                'totalDocs' => $paginatedHistory->total(),
                'page' => $paginatedHistory->currentPage(),
                'totalPages' => $paginatedHistory->lastPage(),
                'hasNext' => $paginatedHistory->hasMorePages(),
                'hasPrev' => $paginatedHistory->currentPage() > 1,
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Error when viewing the history of searched words!',
                'errors' => $e->errors(),
            ], 400);

        } catch (\Throwable $th) {

            return response()->json([
                'error' => 'An error occurred, try again!',
            ], 500);

        }

    }

}
