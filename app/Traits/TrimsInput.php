<?php

namespace App\Traits;

trait TrimsInput
{
    protected function prepareForValidation()
    {
        $input = $this->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }

        $this->merge($input);
    }
}