<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string  $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function index(Request $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);

        return response()->json($model::search($request->all())->get());
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

        return response()->json($search->get());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string  $modelName
     *
     * @throws Exception
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, string $modelName): JsonResponse
    {
        $model = $this->extractModelClass($modelName);
        $foundModels = $model::search($request->all())->get();

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
     * @throws Exception
     *
     * @return Model
     */
    protected function extractModelClass(string $modelName): Model
    {
        $mapping = config('asseco-search.model_mapping');

        if (array_key_exists($modelName, $mapping)) {
            return new $mapping[$modelName]();
        }

        $namespaces = config('asseco-search.models_namespaces');

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
