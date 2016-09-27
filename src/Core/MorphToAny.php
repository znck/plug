<?php namespace Znck\Plug\Eloquent\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class MorphToAny extends Relation
{

    protected $relationKey;

    protected $relationClass;

    protected $table;

    protected $relation;

    protected $pivotColumns = [];

    protected $pivotUpdatedAt;

    protected $pivotCreatedAt;

    public function __construct(Builder $query, Model $parent, string $relation, string $table)
    {
        parent::__construct($query, $parent);

        $this->table = $table;
        $this->relation = $relation;
        $this->relationClass = $relation.'_type';
        $this->relationKey = $relation.'_id';
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $this->query->from($this->table);

        $this->query->where($this->getRelated()->getForeignKey(), $this->getRelated()->getKey());
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->from($this->table);

        $this->query->whereIn($this->getRelated()->getForeignKey(), $this->getKeys($models));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array $models
     * @param  string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {

    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string $relation
     * @return array
     */
    public function match(array $models, EloquentCollection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            if (isset($dictionary[$model->getKey()])) {
                $model->setRelation($relation, $this->getRelated()->newCollection($dictionary[$model->getKey()]));
            }
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    public function createdAt()
    {
        return $this->pivotCreatedAt ?? parent::createdAt();
    }

    public function updatedAt()
    {
        return $this->pivotUpdatedAt ?? parent::updatedAt();
    }

    public function associate(Model $model)
    {

    }

    protected function map(EloquentCollection $result, Collection $group)
    {
        $group = $group->reduce(
            function (Collection $result, $item) {
                $result[data_get($item, $this->relationKey)] = $item;
            },
            new Collection()
        );

        return $result->map(
            function (Model $item) use ($group) {
                $item->setRelation($this->relation, $this->getRelated()->newCollection($group[$item->getKey()] ?? []));
            }
        );
    }

    public function withPivot($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->pivotColumns = array_merge($this->pivotColumns, $columns);

        return $this;
    }

    public function withTimestamps($createdAt = null, $updatedAt = null)
    {
        $this->pivotCreatedAt = $createdAt;
        $this->pivotUpdatedAt = $updatedAt;

        $this->withPivot($this->createdAt(), $this->updatedAt());

        return $this;
    }

    public function get($columns = ['*'])
    {
        $builder = $this->getQuery()->applyScopes();

        $select = $this->getSelectColumns($columns);

        $results = $builder->select($select)->get();

        $results = $this->hydratePivot($results);

        return $results;
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int $perPage
     * @param  array $columns
     * @param  string $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $builder = $this->getQuery()->applyScopes();

        $select = $this->getSelectColumns($columns);

        $paginator = $builder->from($this->table)->select($select)->paginate($perPage, $columns, $pageName, $page);

        $this->hydratePivot($paginator->items());

        return $paginator;
    }

    protected function getSelectColumns(array $columns)
    {
        if ($columns == ['*']) {
            return [$this->table.'.*'];
        }

        $select = [];
        foreach (array_merge(
                     $columns,
                     [$this->relationKey, $this->relationClass, $this->related->getForeignKey()]
                 ) as $column) {
            $select[] = $this->table.'.'.$column;
        }

        return $select;
    }

    /**
     * @param $results
     * @return $this|static
     */
    protected function hydratePivot($results)
    {
        $results = (new Collection($results))->groupBy($this->relationClass);

        $reversedMorphMap = array_flip(self::morphMap());

        $results = $results->each(
            function (Collection $group, $key) use ($reversedMorphMap) {
                $class = $reversedMorphMap[$key] ?? $key;

                $result = (new $class)->whereIn($group->pluck($this->relationKey)->toArray())->get();

                $this->map($result, $group);
            }
        );

        return $results;
    }

    protected function buildDictionary(EloquentCollection $results)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->getRelated()->getForeignKey()}][] = $result;
        }

        return $dictionary;
    }
}
