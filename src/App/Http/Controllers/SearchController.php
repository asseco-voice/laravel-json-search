<?php

declare(strict_types=1);

namespace Voice\JsonSearch\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Voice\JsonQueryBuilder\Exceptions\SearchException;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string  $modelName
     *
     * @throws SearchException
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function index(Request $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);

        return Response::json($model::search($request->all())->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string  $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function update(Request $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);

        $search = $model::search($request->except('update'));

        if (!$request->has('update')) {
            throw new Exception('Missing update parameters');
        }

        $search->update($request->update);

        return Response::json($search->get());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string  $modelName
     *
     * @throws SearchException
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);
        $isDeleted = $model::search($request->all())->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }

    /**
     * @param string $modelName
     *
     * @throws Exception
     *
     * @return Model
     */
    protected function extractModelClass(string $modelName): Model
    {
        $mapping = Config::get('asseco-search.model_mapping');

        if(in_array($modelName, $mapping)){
            return new $mapping[$modelName];
        }

        $namespaces = Config::get('asseco-search.models_namespaces');

        $formattedModelName = Str::studly(Str::singular($modelName));

        foreach ($namespaces as $namespace) {
            $model = "$namespace\\$formattedModelName";
            if (class_exists($model)) {
                return new $model();
            }
        }

        throw new Exception("Model $model does not exist");
    }
}
