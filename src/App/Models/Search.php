<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Models;

use Asseco\JsonSearch\App\Http\Requests\SearchRequest;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class Search
{
    /**
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function get(SearchRequest $request, string $modelName)
    {
        $model = self::extractModelClass($modelName);

        $query = $model->search($request->except(['append', 'scopes']));

        foreach ($request->get('scopes', []) as $scope) {
            $query->{$scope}();
        }

        $resolved = $query->get();

        return $resolved->append($request->get('append', []));
    }

    /**
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function update(SearchRequest $request, string $modelName)
    {
        $model = self::extractModelClass($modelName);

        $search = $model->search($request->except('update'));

        if (!$request->has('update')) {
            throw new Exception('Missing update parameters');
        }

        $search->update($request->update);

        return $search->get();
    }

    /**
     * @param SearchRequest $request
     * @param string        $modelName
     *
     * @throws Exception
     */
    public static function delete(SearchRequest $request, string $modelName)
    {
        $model = self::extractModelClass($modelName);
        $foundModels = $model->search($request->all())->get();

        // This can be executed as a single query, but then we are left without
        // deleted event triggers. If there is a better way, I'm all ears.
        foreach ($foundModels as $foundModel) {
            /**
             * @var $foundModel Model
             */
            $foundModel->delete();
        }
    }

    /**
     * @param string $modelName
     *
     * @throws Exception
     *
     * @return Model|Builder
     */
    protected static function extractModelClass(string $modelName)
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
