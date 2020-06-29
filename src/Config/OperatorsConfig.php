<?php

namespace Voice\SearchQueryBuilder\Config;

use Illuminate\Support\Facades\Config;
use Voice\SearchQueryBuilder\Exceptions\SearchException;
use Voice\SearchQueryBuilder\SearchCallbacks\AbstractCallback;
use Voice\SearchQueryBuilder\Types\AbstractType;

class OperatorsConfig
{
    protected $config;

    public array $operatorCallbackMapping = [];
    public array $registeredTypes         = [];

    public function __construct()
    {
        $this->config = Config::get('asseco-voice.search.operators');

        $this->registerOperators();
        $this->registerTypes();
    }

    protected function registerOperators(): void
    {
        $operators = $this->getOperators();
        $callbacks = $this->registeredCallbacks();

        $this->operatorCallbackMapping = array_combine($operators, $callbacks);
    }

    public function getOperators()
    {
        /**
         * @var AbstractCallback $callback
         */
        return array_map(fn ($callback) => $callback::getCallbackOperator(), $this->registeredCallbacks());
    }

    /**
     * @param string $operator
     * @return string
     * @throws SearchException
     */
    public function getCallbackClassFromOperator(string $operator): string
    {
        if(!array_key_exists($operator, $this->operatorCallbackMapping)){
            throw new SearchException("[Search] No valid callback registered for '$operator' operator.");
        }

        return $this->operatorCallbackMapping[$operator];
    }

    protected function registerTypes(): void
    {
        $types = [];

        foreach ($this->config as $operator => $typeClasses) {
            foreach ($typeClasses as $typeClass) {
                $types[] = $typeClass;
            }
        }

        $uniqueTypes = array_unique($types);

        foreach ($uniqueTypes as $uniqueType) {
            /**
             * @var AbstractType $uniqueType
             */
            $this->registeredTypes[] = new $uniqueType();
        }
    }

    public function registeredCallbacks(): array
    {
        return array_keys($this->config);
    }

    /**
     * @param AbstractCallback $callback
     * @return array
     * @throws SearchException
     */
    public function getCallbackTypes(AbstractCallback $callback): array
    {
        $this->checkIfCallbackRegistered($callback);

        return $this->config[get_class($callback)];
    }

    /**
     * @param AbstractCallback $callback
     * @param string $laravelType
     * @return AbstractType
     * @throws SearchException
     */
    public function getCallbackType(AbstractCallback $callback, string $laravelType): AbstractType
    {
        $callbackClassName = get_class($callback);

        $this->checkIfCallbackRegistered($callbackClassName);

        $callbackTypes = $this->config[$callbackClassName];

        foreach ($callbackTypes as $callbackType) {
            /**
             * @var AbstractType $callbackType
             */
            if ($callbackType::getTypeName() === $laravelType) {
                return new $callbackType;
            }
        }

        $operator = $callback::getCallbackOperator();
        throw new SearchException("[Search] Type '$laravelType' doesn't support '$operator' operator.");
    }

    /**
     * @param string $callbackClassName
     * @throws SearchException
     */
    protected function checkIfCallbackRegistered(string $callbackClassName): void
    {
        if (!array_key_exists($callbackClassName, $this->config)) {
            throw new SearchException("[Search] Callback $callbackClassName is not registered.");
        }
    }
}
