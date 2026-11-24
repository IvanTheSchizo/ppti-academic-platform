<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Normalize a scalar or array filter query parameter into a list of non-empty strings.
     *
     * @return list<string>
     */
    protected function multiFilterValues(Request $request, string $key): array
    {
        return array_values(array_map(
            'strval',
            array_filter(
                (array) $request->input($key, []),
                fn ($value) => is_scalar($value) && trim((string) $value) !== ''
            )
        ));
    }
}
