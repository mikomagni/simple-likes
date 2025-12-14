<?php

namespace Mikomagni\SimpleLikes\Modifiers;

use Statamic\Modifiers\Modifier;

class NumberFormat extends Modifier
{
    /**
     * Format a number with grouped thousands
     *
     * @param mixed $value
     * @param array $params
     * @param array $context
     * @return string
     */
    public function index($value, $params, $context)
    {
        $decimals = $params[0] ?? 0;
        $decimalSeparator = $params[1] ?? '.';
        $thousandsSeparator = $params[2] ?? ',';
        
        return number_format((float) $value, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}