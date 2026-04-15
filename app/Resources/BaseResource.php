<?php

namespace App\Resources;

class BaseResource {
    protected object $model;
    protected array $only = [];
    protected array $except = [];
    protected array $relations = [];

    public function __construct(object $model) {
        $this->model = $model;
    }

    public function only(array $fields): self {
        $this->only = $fields;
        return $this;
    }

    public function except(array $fields): self {
        $this->except = $fields;
        return $this;
    }

    public function withRelations(array $relations): self {
        $this->relations = $relations;
        return $this;
    }

    public function toArray(): array {
        // Convertir modelo a array
        $data = $this->model->toArray();

        // Aplicar filtros `only`
        if (!empty($this->only)) {
            $data = array_intersect_key($data, array_flip($this->only));
        }

        // Aplicar filtros `except`
        if (!empty($this->except)) {
            $data = array_diff_key($data, array_flip($this->except));
        }

        // Manejar relaciones anidadas con filtros
        foreach ($this->relations as $relation => $config) {
            if (!isset($this->model->$relation)) {
                continue;
            }

            $resourceClass = $config['resource'] ?? null;
            $only = $config['only'] ?? [];
            $except = $config['except'] ?? [];

            if (!$resourceClass) {
                continue;
            }

            $relatedModel = $this->model->$relation;

            if (is_iterable($relatedModel)) {
                $data[$relation] = array_map(fn($item) => 
                    (new $resourceClass($item))->only($only)->except($except)->toArray(), 
                    $relatedModel
                );
            } elseif (is_object($relatedModel)) {
                $data[$relation] = (new $resourceClass($relatedModel))->only($only)->except($except)->toArray();
            }
        }

        return $data;
    }
}
