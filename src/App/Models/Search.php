<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Models;

use Asseco\JsonSearch\App\Http\Requests\SearchRequest;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Search
{
    /**
     * @param string $modelName
     * @param array $search
     * @param array $appends
     * @param array $scopes
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws Exception
     *
     */
    public static function get(string $modelName, array $search, ?array $appends = [], ?array $scopes = [])
    {
        $model = self::extractModelClass($modelName);
        $query = $model->search($search);

        self::attachScopes($scopes, $query);
        $resolved = $query->get();
        self::attachAppends($appends, $resolved);

        return $resolved;
    }

    protected static function attachScopes(?array $scopes, $query): void
    {
        foreach ($scopes as $scope) {
            $query->{$scope}();
        }
    }

    protected static function attachAppends(?array $appends, $collection): void
    {
        $modelAppends = [];

        foreach ($appends as $append) {

            $relationAppends = explode('.', $append);

            // Less than 2 means no '.' separator was used, so we're talking about
            // plain append to original model, not to a relation.
            if (count($relationAppends) < 2) {
                $modelAppends[] = $append;
                continue;
            }

            $append = array_pop($relationAppends);

            foreach ($collection as $model) {
                self::appendRelation($model, $relationAppends, $append);
            }
        }

        $collection->append($modelAppends);
    }

    protected static function appendRelation($model, array $relationModels, string $append): void
    {
        $relation = Str::camel(array_shift($relationModels));

        $resolved = $relation ? $model->{$relation} : $model;

        $resolved->append($append);
    }

    /**
     * @param SearchRequest $request
     * @param string $modelName
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws Exception
     *
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
     * @param string $modelName
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
     * @return mixed
     * @throws Exception
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
