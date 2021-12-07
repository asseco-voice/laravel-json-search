<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Controllers;

use Asseco\JsonSearch\App\Http\Requests\SearchRequest;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param SearchRequest $request
     * @param string $modelName
     *
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function index(SearchRequest $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);

        foreach ($request->get('scopes', []) as $scope) {
            $model->{$scope}();
        }

        $resolved = $model->search($request->except('append'))->get();

        return response()->json(
            $resolved->append($request->get('append', []))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SearchRequest $request
     * @param string $modelName
     *
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function update(SearchRequest $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);

        $search = $model->search($request->except('update'));

        if (!$request->has('update')) {
            throw new Exception('Missing update parameters');
        }

        $search->update($request->update);

        return response()->json($search->get());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SearchRequest $request
     * @param string $modelName
     *
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function destroy(SearchRequest $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);
        $foundModels = $model->search($request->all())->get();

        // This can be executed as a single query, but then we are left without
        // deleted event triggers. If there is a better way, I'm all ears.
        foreach ($foundModels as $foundModel) {
            /**
             * @var $foundModel Model
             */
            $foundModel->delete();
        }

        return response()->json();
    }

    /**
     * @param string $modelName
     *
     * @return Model|Builder
     * @throws Exception
     *
     */
    protected function extractModelClass(string $modelName)
    {
        $mapping = config('asseco-search.model_mapping');

        if (array_key_exists($modelName, $mapping)) {
            $model = $mapping[$modelName];

            return is_callable($model) ? $model() : new $model();
        }

        $namespaces = config('asseco-search.models_namespaces');

        $formattedModelName = Str::studly(Str::singular($modelName));

        foreach ($namespaces as $namespace) {
            $model = "$namespace\\$formattedModelName";
            if (class_exists($model)) {
                return new $model();
            }
        }

        throw new Exception("Model $modelName not found. Check the configuration for namespace mappings.");
    }
}
