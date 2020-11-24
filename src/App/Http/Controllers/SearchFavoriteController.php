<?php

declare(strict_types=1);

namespace Voice\JsonSearch\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Voice\JsonSearch\App\SearchFavorite;

class SearchFavoriteController extends Controller
{
    public SearchFavorite $favorite;

    public function __construct()
    {
        $model = config('asseco-search.search_favorite_model');
        $this->favorite = new $model;
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $searchFavorite = $this->favorite::query()->create($request->all());

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
     * @param Request $request
     * @param SearchFavorite $searchFavorite
     *
     * @return JsonResponse
     */
    public function update(Request $request, SearchFavorite $searchFavorite): JsonResponse
    {
        $searchFavorite->update($request->all());

        return response()->json($searchFavorite->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SearchFavorite $searchFavorite
     *
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function destroy(SearchFavorite $searchFavorite): JsonResponse
    {
        $isDeleted = $searchFavorite->delete();

        return response()->json($isDeleted ? 'true' : 'false');
    }
}
