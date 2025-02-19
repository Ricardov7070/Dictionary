<?php

namespace App\Models\FavoriteWords;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Words\Words_Dictionary;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;


class FavoriteWords extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $table = 'favorite_words';

    protected $fillable = [
        'users_id',
        'words_dictionary_id',
        'deleted_at',
        'created_at',
        'updated_at'
    ];


    public function words_dictionary (): BelongsTo {

        return $this->belongsTo(Words_Dictionary::class, 'words_dictionary_id', 'id');
    
    }


    public function checkWordStatus ($existingWord, $id_user): array {
        
        $cacheKey = "word_status_{$id_user}_{$existingWord[0]['id']}";

        $cacheTime = 10;

        return Cache::remember($cacheKey, $cacheTime, function () use ($existingWord, $id_user): array {

            return self::where('users_id', $id_user)
                        ->where('words_dictionary_id', $existingWord[0]['id'])
                        ->whereNull('deleted_at')
                        ->get()
                        ->toArray();

        });

    }


    public function favoriteWords ($existingWord, $id_user): array {

        $favorite = self::create([
            'users_id' => $id_user,
            'words_dictionary_id' => $existingWord[0]['id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return [
            'word' => $existingWord[0]['words'],
            'updated_at' => $favorite->created_at->format('d/m/Y'),
        ];

    }


    public function removeWord ($validation): void {

        $favorite = self::findOrFail($validation[0]['id']);

        $favorite->update([
            'deleted_at' => now(),
        ]);

    }

    
    public function viewFavoriteWords ($id_user, $limit, $search, $page, $order): LengthAwarePaginator {

        $cacheKey = "favorite_words_{$id_user}_{$limit}_{$search}_{$page}_{$order}";

        $cacheTime = 10;

        return Cache::remember($cacheKey, $cacheTime, function () use ($id_user, $limit, $search, $page, $order): LengthAwarePaginator {
   
            $query = self::where('users_id', $id_user)
                ->whereNull('deleted_at')
                ->whereHas('words_dictionary', function ($query): void {
                    $query->whereNull('deleted_at');
                })
                ->with(['words_dictionary' => function ($query): void {
                    $query->select('id', 'words');
                }]);

            if ($search) {

                $query->whereHas('words_dictionary', function ($subQuery) use ($search): void {
                    $subQuery->where('words', 'like', '%' . $search . '%');
                });

            }

            $query->orderBy('updated_at', $order);

            $paginated = $query->paginate($limit, ['*'], 'page', $page);

            $transformedResults = $paginated->getCollection()->map(function ($favorite): array {
              
                return [
                    'word' => $favorite->words_dictionary->words,
                    'added' => Carbon::parse($favorite->updated_at)->format('d/m/Y'),
                ];

            });

            return new LengthAwarePaginator(
                $transformedResults,
                $paginated->total(),
                $paginated->perPage(),
                $paginated->currentPage(),
                ['path' => $paginated->path(), 'query' => $paginated->getOptions()]
            );

        });

    }

}
