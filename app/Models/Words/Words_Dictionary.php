<?php

namespace App\Models\Words;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;


class Words_Dictionary extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $table = 'words_dictionary';

    protected $fillable = [
        'words',
        'frequency',
        'deleted_at',
        'created_at',
        'updated_at'
    ];


    public function viewWords ($perPage, $search, $page, $order): LengthAwarePaginator {
     
        $cacheKey = "search_words_{$search}_page_{$page}_order_{$order}_perPage_{$perPage}";

        return Cache::remember($cacheKey, 3600, function () use ($perPage, $search, $page, $order): LengthAwarePaginator {
        
            $query = self::whereNull('deleted_at')->select('words');

            if ($search) {
                $query->where('words', 'like', '%' . $search . '%');
            }

            $query->orderBy('words', $order);

            return $query->paginate($perPage, ['*'], 'page', $page);
       
        });

    }


    public function checkWord ($word): array {

        $cacheKey = "check_word_{$word}";

        return Cache::remember($cacheKey, 3600, function () use ($word): array {
           
            return self::where('words', $word)
                ->whereNull('deleted_at')
                ->get()
                ->toArray();

        });

    }


    public function wordData ($word): JsonResponse {

        $cacheKey = "word_data_{$word}";

        $wordData = Cache::remember($cacheKey, 3600, function () use ($word): mixed {

            $url = "https://api.dictionaryapi.dev/api/v2/entries/en/{$word}";
            
            $response = Http::withOptions(['verify' => false])->get($url);

            return $response->json();

        });

        return response()->json($wordData);

    }

}
