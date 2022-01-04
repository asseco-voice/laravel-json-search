<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Controllers;

use Asseco\JsonSearch\App\Http\Requests\SearchRequest;
use Asseco\JsonSearch\App\Models\Search;
use Exception;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function index(SearchRequest $request, string $modelName): JsonResponse
    {
        return response()->json(
            Search::get(
                $modelName,
                $request->except(['append', 'scopes']),
                $request->get('append'),
                $request->get('scopes'),
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function update(SearchRequest $request, string $modelName): JsonResponse
    {
        return response()->json(Search::update($request, $modelName));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function destroy(SearchRequest $request, string $modelName): JsonResponse
    {
        Search::delete($request, $modelName);

        return response()->json();
    }
}
