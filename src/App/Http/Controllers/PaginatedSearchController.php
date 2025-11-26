<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Controllers;

use Asseco\JsonSearch\App\Http\Requests\PaginatedSearchRequest;
use Asseco\JsonSearch\App\Http\Resources\PaginatedResponse;
use Asseco\JsonSearch\App\Models\Search;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PaginatedSearchController extends Controller
{
    /**
     * Display a paginated listing of the resource.
     *
     * @param  PaginatedSearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function index(PaginatedSearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        $searchParams = $request->except(['append', 'scopes', 'page', 'limit']);
        $appends = $request->get('append', []);
        $scopes = $request->get('scopes', []);

        $paginationData = $this->handlePagination($request, $searchParams, $modelName, $appends, $scopes);

        $data = Search::get(
            $modelName,
            $paginationData['searchParams'],
            $appends,
            $scopes
        );

        $response = new PaginatedResponse(
            $data,
            $paginationData['totalCount'],
            $request
        );

        return response()->json($response->toArray());
    }

    /**
     * Update the specified resources with pagination support.
     *
     * @param  PaginatedSearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function update(PaginatedSearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        $searchParams = $this->applyPaginationToParams($request, $request->all());

        Search::update($request->merge($searchParams), $modelName);

        return response()->json(status: Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resources with pagination support.
     *
     * @param  PaginatedSearchRequest  $request
     * @param  string  $modelName
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(PaginatedSearchRequest $request, string $modelName): JsonResponse
    {
        $request = $this->setLimit($request);

        $searchParams = $this->applyPaginationToParams($request, $request->all());

        Search::delete($request->merge($searchParams), $modelName);

        return response()->json();
    }

    /**
     * Handle pagination logic including count query and caching.
     *
     * @param  PaginatedSearchRequest  $request
     * @param  array  $searchParams
     * @param  string  $modelName
     * @param  array  $appends
     * @param  array  $scopes
     * @return array
     *
     * @throws Exception
     */
    protected function handlePagination(
        PaginatedSearchRequest $request,
        array $searchParams,
        string $modelName,
        array $appends,
        array $scopes
    ): array {
        // Merge pagination values
        $searchParams = $this->applyPaginationToParams($request, $searchParams);

        // Count query
        $totalCount = $this->getTotalCount($searchParams, $modelName, $appends, $scopes);

        return [
            'searchParams' => $searchParams,
            'totalCount'   => $totalCount,
        ];
    }


    /**
     * Get total count of records matching the search criteria with caching.
     *
     * @param  array  $searchParams
     * @param  string  $modelName
     * @param  array  $appends
     * @param  array  $scopes
     * @return int
     *
     * @throws Exception
     */
    protected function getTotalCount(array $searchParams, string $modelName, array $appends, array $scopes): int
    {
        $queryForHash = Arr::except($searchParams, ['count', 'limit', 'offset', 'append', 'relations', 'order_by']);

        $hash = md5(json_encode($queryForHash) . $modelName);
        $cacheKey = "json_search_paginated_count_{$hash}";

        return Cache::remember($cacheKey, 600, function () use ($searchParams, $modelName) {
            $countParams = Arr::except($searchParams, ['limit', 'offset']);
            $countParams['count'] = true;

            $resultArray = Search::get($modelName, $countParams, [], [])->toArray();

            return (int) Arr::get($resultArray, '0.count', 0);
        });
    }

    /**
     * Merge search params
     *
     * @param  PaginatedSearchRequest $request
     * @param  array $params
     * @return array
     *
     * @throws Exception
     */
    protected function applyPaginationToParams(PaginatedSearchRequest $request, array $params): array
    {
        return array_merge($params, $this->convertPageToOffset($request));
    }

    /**
     * Convert page to offset and limit
     *
     * @param  PaginatedSearchRequest $request
     * @return array
     *
     * @throws Exception
     */
    protected function convertPageToOffset(PaginatedSearchRequest $request): array
    {
        $page  = max(1, (int)$request->get('page', 1));
        $limit = (int)$request->get('limit', 25);

        return [
            'offset' => ($page - 1) * $limit,
            'limit'  => $limit,
        ];
    }


    /**
     * Set default limit if not provided or exceeds max limit.
     *
     * @param  PaginatedSearchRequest  $request
     * @return PaginatedSearchRequest
     */
    protected function setLimit(PaginatedSearchRequest $request): PaginatedSearchRequest
    {
        $limit = $request->get('limit');
        $maxLimit = config('asseco-search.default_limit');

        if ($maxLimit && (!$limit || $limit > $maxLimit)) {
            $request->merge(['limit' => $maxLimit]);
        }

        return $request;
    }
}
