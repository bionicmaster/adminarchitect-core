<?php

namespace Terranet\Administrator\Field;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Terranet\Administrator\Architect;
use Terranet\Administrator\Collection\Mutable;
use Terranet\Administrator\Field\Traits\HandlesRelation;
use Terranet\Administrator\Modules\Faked;
use Terranet\Administrator\Traits\Module\HasColumns;
use Terranet\Administrator\Field\KeyForHasMany;

class HasMany extends Field
{
    use HandlesRelation, HasColumns;
    const MODE_TAGS = 'tags';
    const MODE_CHECKBOXES = 'checkboxes';
    const MODE_MULTI_SELECT = 'multiselects';
    const MODE_CREATE_FORM = 'createforms';

    protected $titleField = 'name';
    protected $editMode = self::MODE_CREATE_FORM;
    protected $completeList = true;
    protected $except;
    protected $only;
    protected $withColumnsCallback;

    /** @var string */
    protected $icon = 'list-ul';

    /** @var null|\Closure */
    protected $query;

    public function modeCreateForm(): self
    {
        $this->editMode = static::MODE_CREATE_FORM;
        $this->completeList = false;

        return $this;
    }
    

    public function tagList(): self
    {
        $this->editMode = static::MODE_TAGS;
        $this->completeList = false;

        return $this;
    }

    public function checkBoxes(): self
    {
        $this->editMode = static::MODE_CHECKBOXES;
        return $this;
    }

    public function multiSelect(): self
    {
        $this->editMode = static::MODE_MULTI_SELECT;
        return $this;
    }
    
    

    public function useAsTitle(string $column): self
    {
        $this->titleField = $column;

        return $this;
    }

    protected function onEdit(): array
    {
        $relation = $this->relation();

        if (static::MODE_MULTI_SELECT === $this->editMode) {
            $values = $this->query ? call_user_func_array($this->query, [$relation->getRelated()->query()]) : $relation->getRelated()->all();
        
        } elseif (static::MODE_CREATE_FORM === $this->editMode) {

            $columnsCollection = $this->getColumnsCollection();
            $columnsCollection->each(function($item) { 
                // have to change at here cause here is after run grid
                $item->each(function($field) {
                    $splitFieldId = explode(".",  $field->id());
                    // not suppose to be -1 of size here, if -1 then got error
                    $column = $splitFieldId[sizeof($splitFieldId) - 1];
                    
                    if($column === $field->getModel()->getKeyName())
                    {
                        $field->setIncludeHidden(true);
                    }
                });
            });

            $values = $this->query ? call_user_func_array($this->query, [$relation->getRelated()->query()]) : $this->model->{$this->id};

        } elseif (static::MODE_CHECKBOXES === $this->editMode && $this->completeList) {
            $values = $this->query
                ? call_user_func_array($this->query, [$relation->getRelated()->query()])
                : $relation->getRelated()->all();
        } else {
            $values = $this->value();
        }

        return [
            'relation' => $relation,
            'searchable' => \get_class($relation->getRelated()),
            'values' => $values,
            'completeList' => $this->completeList,
            'titleField' => $this->titleField,
            'editMode' => $this->editMode,
            'columnsCollection' => $columnsCollection ?? new Collection,
        ];
    }

    /**
     * undocumented function
     *
     * @return void
     */
    public function getColumnsCollection(): ?Collection
    {
        $models = $this->model->{$this->id()};

        $columnCollection = new Collection;
        foreach($models as $model) {
            $columnCollection->push(
                $this->makeColumn($model)->each(function($field) use ($model) {
                    // must set name first, cause if set id first, the id to set the name will be replaced
                    $field->setName("{$this->id()}[{$model->id}][{$field->id()}]");
                    $field->setId("{$this->id()}.{$model->id}.{$field->id()}");
                    // field type not able to change here because of their design, when building grid they will refer to 
                    // detector to decide type, and there are nothing I can do
                    // u can only change when scaffold is built, which then it will appear as has many 
                })
            );
        }

        return $columnCollection;
    }

    /**
     * undocumented function
     *
     * @return void
     */
    protected function makeColumn($model): ?Mutable
    {
        return $this->relatedColumns($model)
                    ->each->setModel($model);
    }
    
    

    // this replace with the top makeColumn function to cater for each model
    //protected function getColumns(): ?Mutable
    //{
    //    $relation = $this->model->{$this->id()}();

    //    return $this->applyColumnsCallback(
    //        $this->relatedColumns($related = $relation->getRelated())
    //            ->each->setModel($this->model->{$this->id()}->first() ?: $related)
    //    );
    //}

    /**
     * @param $related
     * @return \Terranet\Administrator\Collection\Mutable
     */
    protected function relatedColumns($related): Mutable
    {
        return $this->collectColumns($related)
            // this hide the id
            // ->except(array_merge([$related->getKeyName()], $this->except ?? []))
            ->except($this->except ?? [])
            ->only($this->only);
    }

    /**
     * @param  \Closure  $callback
     * @return $this
     */
    //public function withColumns(\Closure $callback): self
    //{
    //    $this->withColumnsCallback = $callback;

    //    return $this;
    //}

    /**
     * Apply callback function to all columns, including those added during callback execution.
     *
     * @param  Mutable  $collection
     * @return mixed|Mutable
     */
    //protected function applyColumnsCallback(Mutable $collection)
    //{
    //    if ($this->withColumnsCallback) {
    //        $collection = call_user_func_array($this->withColumnsCallback, [$collection, $this->model]);
    //    }

    //    $this->assignModel(
    //        $collection,
    //        $this->model->{$this->id()}[0] ?: $this->model->{$this->id()}()->getRelated()
    //    );

    //    return $collection;
    //}

    /**
     * @param  Mutable  $collection
     * @param $model
     * @return mixed
     */
    //protected function assignModel(Mutable $collection, $model)
    //{
    //    return $collection->each->setModel($model);
    //}

    public function only(array $only): self
    {
        $this->only = $only;

        return $this;
    }

    public function except(array $except): self
    {
        $this->except = $except;

        return $this;
    }

    /**
     * @param \Closure $query
     *
     * @return $this
     */
    public function withQuery(\Closure $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string $icon
     *
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Model $model
     * @param string $direction
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sortBy(
        \Illuminate\Database\Eloquent\Builder $query,
        Model $model,
        string $direction
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->withCount($this->id())->orderBy("{$this->id()}_count", $direction);
    }


    /**
     * @return array
     */
    protected function onIndex(): array
    {
        $relation = $this->relation();
        $related = $relation->getRelated();

        // apply a query
        if ($this->query instanceof \Closure) {
            $relation = \call_user_func_array($this->query, [$relation]);
        }

        if ($module = Architect::resourceByEntity($related)) {
            $url = route('scaffold.index', [
                'module' => $module->url(),
                $related->getKeyName() => $related->getKey(),
                'viaResource' => is_a($this, BelongsToMany::class)
                    ? app('scaffold.module')->url()
                    : Str::singular(app('scaffold.module')->url()),
                'viaResourceId' => $this->model->getKey(),
            ]);
        }

        return [
            'icon' => $this->icon,
            'module' => $module,
            'count' => $relation->count(),
            'url' => $url ?? null,
        ];
    }

    /**
     * @return array
     * @throws \Terranet\Administrator\Exception
     *
     */
    protected function onView(): array
    {
        $relation = $this->relation();
        $related = $relation->getRelated();

        // apply a query
        if ($this->query instanceof \Closure) {
            $relation = \call_user_func_array($this->query, [$relation]);
        }

        if (!$module = $this->relationModule()) {
            // Build a runtime module
            $module = Faked::make($related);
        }
        $columns = $module->columns()->each->disableSorting();
        $actions = $module->actions();

        return [
            'module' => $module ?? null,
            'columns' => $columns ?? null,
            'actions' => $actions ?? null,
            'relation' => $relation ?? null,
            'items' => $relation ? $relation->getResults() : null,
        ];
    }
}
