<?php

namespace Spatie\LaravelCipherSweet\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use RuntimeException;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class EncryptedExistsRule implements ValidationRule
{
    public function __construct(
        protected string $model,
        protected string $indexName,
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
        )->count();

        if ($count === 0) {
            $fail('validation.exists')->translate();
        }
    }

    /**
     * @throws RuntimeException
     */
    private function checkModelHasEncryptedColumn(): void
    {
        if (! is_subclass_of($this->model, CipherSweetEncrypted::class)) {
            throw new RuntimeException("The model {$this->model} must implement " . CipherSweetEncrypted::class);
        }
    }
}
