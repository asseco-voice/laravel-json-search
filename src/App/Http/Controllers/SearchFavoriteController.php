<?php

namespace Voice\JsonSearch\App\Http\Controllers;

use App\Http\Controllers\Controller; // Stock Laravel controller class
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Voice\JsonSearch\App\SearchFavorite;

class SearchFavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(SearchFavorite::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $searchFavorite = SearchFavorite::create($request->all());

        return response()->json($searchFavorite);
    }

    /**
     * Display the specified resource.
     *
     * @param SearchFavorite $searchFavorite
     * @return JsonResponse
     */
    public function show(SearchFavorite $searchFavorite)
    {
        return response()->json($searchFavorite);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param SearchFavorite $searchFavorite
     * @return JsonResponse
     */
    public function update(Request $request, SearchFavorite $searchFavorite)
    {
        $isUpdated = $searchFavorite->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SearchFavorite $searchFavorite
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(SearchFavorite $searchFavorite)
    {
        $isDeleted = $searchFavorite->delete();

        return response()->json($isDeleted);
    }
}
