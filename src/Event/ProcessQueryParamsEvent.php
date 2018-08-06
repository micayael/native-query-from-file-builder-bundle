<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ProcessQueryParamsEvent extends Event
{
    /**
     * @var string
     */
    private $snippetKey;

    /**
     * @var array
     */
    private $originalParams;

    /**
     * @var string
     */
    private $paramKey;

    /**
     * @var string
     */
    private $filterType;

    /**
     * @var string
     */
    private $originalFilter;

    /**
     * @var array
     */
    private $processedParams = [];

    /**
     * @var string
     */
    private $processedFilter;

    public function __construct(string $snippetKey, ?string $filterType, string $paramKey, array $originalParams, string $originalFilter)
    {
        $this->snippetKey = $snippetKey;
        $this->filterType = $filterType;

        $this->paramKey = $paramKey;

        $this->originalParams = $originalParams;
        $this->originalFilter = $originalFilter;

        $this->processedParams = $originalParams;
        $this->processedFilter = $originalFilter;

        switch ($filterType) {
            case 'like':
                $newValue = '%'.$this->getOriginalParamValue().'%';
                $this->replaceParamValue($newValue);
                break;
            case 'string_any':
                $newValue = '{'.implode(', ', array_map('strval', $this->getOriginalParamValue())).'}';
                $this->replaceParamValue($newValue);
                break;
            case 'numeric_any':
                $newValue = '{'.implode(', ', array_map('intval', $this->getOriginalParamValue())).'}::integer';
                $this->replaceParamValue($newValue);
                break;
            case 'in':
                $newFilter = '';
                $newParams = [];

                $i = 0;

                foreach ($this->getOriginalParamValue() as $value) {
                    $newFilter .= ':'.$paramKey.'_'.$i.',';
                    $newParams[$paramKey.'_'.$i] = $value;

                    ++$i;
                }

                if (strlen($newFilter) > 0) {
                    $newFilter = substr($newFilter, 0, strlen($newFilter) - 1);
                }

                $this->removeParams([$this->getParamKey()]);

                $processedParams = array_merge($this->processedParams, $newParams);
                $processedFilter = str_replace(':'.$paramKey, $newFilter, $originalFilter);

                $this->setProcessedParams($processedParams);
                $this->setProcessedFilter($processedFilter);

                break;
        }
    }

    /**
     * Return the value of the current param to be processed.
     */
    public function getOriginalParamValue()
    {
        return $this->originalParams[$this->paramKey];
    }

    /**
     * Returns params after they have been processed.
     */
    public function getProcessedParams(): array
    {
        return $this->processedParams;
    }

    /**
     * Set processed params.
     *
     * @param array $params
     */
    public function setProcessedParams(array $params): void
    {
        $this->processedParams = $params;
    }

    /**
     * Return the filter of the current param after it has been processed.
     */
    public function getProcessedFilter(): string
    {
        return $this->processedFilter;
    }

    /**
     * Replaces the value of the current filter.
     *
     * @param string $filter
     */
    public function setProcessedFilter(string $filter): void
    {
        $this->processedFilter = $filter;
    }

    /**
     * Remove a param for the list of processed params.
     *
     * @param array $paramKeys
     */
    public function removeParams(array $paramKeys): void
    {
        foreach ($paramKeys as $paramKey) {
            unset($this->processedParams[$paramKey]);
        }
    }

    /**
     * Replaces the value of the current param.
     *
     * @param $newValue
     */
    public function replaceParamValue($newValue): void
    {
        $this->addOrReplaceParamValue($this->paramKey, $newValue);
    }

    /**
     * Adds or replaces the value of a para defined by $paramKey.
     *
     * @param string $paramKey
     * @param $newValue
     */
    public function addOrReplaceParamValue(string $paramKey, $newValue): void
    {
        $this->processedParams[$paramKey] = $newValue;
    }

    /**
     * Return the type of the param to be processed.
     */
    public function getFilterType(): ?string
    {
        return $this->filterType;
    }

    /**
     * Return the key of the param to be processed.
     */
    public function getParamKey(): string
    {
        return $this->paramKey;
    }

    /**
     * Return the key of the snnipet query key: products.base.
     */
    public function getSnippetKey()
    {
        return $this->snippetKey;
    }

    /**
     * Return the original params and values passed to the bundle.
     */
    public function getOriginalParams(): array
    {
        return $this->originalParams;
    }

    public function getOriginalFilter(): string
    {
        return $this->originalFilter;
    }
}
