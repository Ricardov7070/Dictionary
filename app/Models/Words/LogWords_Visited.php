<?php

namespace App\Models\Words;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;


class LogWords_Visited extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $table = 'logwords_visited';

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

    
    public function wordHistory ($existingWord, $id_user): void {

        self::create([
            'users_id' => $id_user,
            'words_dictionary_id' => $existingWord[0]['id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
    }


    public function viewSelectedWords ($id_user, $limit, $search, $page, $order): LengthAwarePaginator {

        $cacheKey = "favorite_words_{$id_user}_search_{$search}_page_{$page}_order_{$order}_limit_{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($id_user, $limit, $search, $page, $order): LengthAwarePaginator {
           
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

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $transformedResults,
                $paginated->total(),
                $paginated->perPage(),
                $paginated->currentPage(),
                ['path' => $paginated->path(), 'query' => $paginated->getOptions()]
            );

        });

    }

}
