<?php

namespace Spatie\LaravelCipherSweet\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class EncryptedUniqueRule implements ValidationRule
{
    /**
     * The ID that should be ignored.
     *
     */
    protected mixed $ignore = null;

    /**
     * The name of the ID column.
     *
     * @var string
     */
    protected $idColumn = 'id';

    public function __construct(
        protected string  $model,
        protected string  $indexName,
        protected ?string $column = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->column ??= $attribute;

        $this->checkModelHasEncryptedColumn();

        $count = $this->model::whereBlind(
            $this->column,
            $this->indexName,
            $value
        )
            ->when($this->ignore, function ($query) {
                $query->whereNot($this->idColumn, $this->ignore);
            })
            ->count();

        if ($count) {
            $fail(trans('validation.unique', [
                'attribute' => $this->column,
            ]));
        }
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @param mixed       $id
     * @param string|null $idColumn
     * @return $this
     */
    public function ignore(mixed $id, ?string $idColumn = null): static
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null                         $idColumn
     * @return $this
     */
    public function ignoreModel(Model $model, ?string $idColumn = null): static
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    private function checkModelHasEncryptedColumn(): void
    {
        if (! (new $this->model()) instanceof CipherSweetEncrypted) {
            throw new RuntimeException("The model {$this->model} must implement " . CipherSweetEncrypted::class);
        }
    }
}
