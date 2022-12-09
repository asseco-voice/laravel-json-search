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
     * @param  SearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function index(SearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        return response()->json(
            Search::get(
                $modelName,
                $request->except(['append', 'scopes']),
                $request->get('append', []),
                $request->get('scopes', []),
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function update(SearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        return response()->json(Search::update($request, $modelName));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(SearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        Search::delete($request, $modelName);

        return response()->json();
    }

    protected function setLimit(SearchRequest $request): SearchRequest
    {
        $limit = $request->get('limit');

        if (config('asseco-search.default_limit') && (!$limit || $limit > 100)) {
            $request->merge(['limit' => 100]);
        }

        return $request;
    }
}
