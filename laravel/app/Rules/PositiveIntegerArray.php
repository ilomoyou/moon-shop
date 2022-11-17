<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PositiveIntegerArray implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        $passedFlag = true;
        foreach ($value as $item) {
            $passed = is_numeric($item) && is_int($item + 0) && ($item + 0) > 0;
            if (!$passed) {
                $passedFlag = false;
                break;
            }
        }
        return $passedFlag;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute' . trans('verification.positive_integer_array');
    }
}
