<?php

declare(strict_types=1);

namespace App\Traits;

use Throwable;

trait HasRepositoryFallback
{
    /**
     * @template TRepository
     * @template TResult
     *
     * @param  iterable<TRepository>  $repositories
     * @param  callable(TRepository): TResult  $callback
     * @param  callable(TResult): bool  $isValidResult
     * @return TResult|null
     *
     * @throws Throwable
     */
    private function fallback(
        iterable $repositories,
        callable $callback,
        callable $isValidResult,
    ): mixed {
        $lastException = null;

        foreach ($repositories as $repository) {
            try {
                $result = $callback($repository);

                if ($isValidResult($result)) {
                    return $result;
                }
            } catch (Throwable $e) {
                $lastException = $e;

                report($e);
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        return null;
    }
}
