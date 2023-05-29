<?php
declare(strict_types=1);
namespace Spatie\LaravelCipherSweet\Transformers;

use ParagonIE\CipherSweet\Contract\TransformationInterface;
use ParagonIE\ConstantTime\Binary;


class FirstXCharacters implements TransformationInterface
{

    public function __construct(private readonly int $characterCount)
    {}

    /**
     * Returns the first x characters
     *
     * @param string $input
     * @return string
     */
    public function __invoke(
        #[\SensitiveParameter]
        mixed $input,
    ): string {
        if (Binary::safeStrlen($input) < $this->characterCount) {
            return $input;
        }

        return substr($input,0, $this->characterCount);
    }
}
