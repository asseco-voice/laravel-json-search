<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Controllers;

use Asseco\JsonSearch\App\Models\SearchFavorite;
use Asseco\JsonSearch\Http\Requests\SearchFavoriteRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class SearchFavoriteController extends Controller
{
    public SearchFavorite $favorite;

    public function __construct()
    {
        $model = config('asseco-search.search_favorite_model');

        $this->favorite = new $model();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json($this->favorite::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SearchFavoriteRequest $request
     *
     * @return JsonResponse
     */
    public function store(SearchFavoriteRequest $request): JsonResponse
    {
        $searchFavorite = $this->favorite::query()->create($request->validated());

        return response()->json($searchFavorite->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param SearchFavorite $searchFavorite
     *
     * @return JsonResponse
     */
    public function show(SearchFavorite $searchFavorite): JsonResponse
    {
        return response()->json($searchFavorite);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SearchFavoriteRequest $request
     * @param SearchFavorite        $searchFavorite
     *
     * @return JsonResponse
     */
    public function update(SearchFavoriteRequest $request, SearchFavorite $searchFavorite): JsonResponse
    {
        $searchFavorite->update($request->validated());

        return response()->json($searchFavorite->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SearchFavorite $searchFavorite
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function destroy(SearchFavorite $searchFavorite): JsonResponse
    {
        $isDeleted = $searchFavorite->delete();

        return response()->json($isDeleted ? 'true' : 'false');
    }
}
