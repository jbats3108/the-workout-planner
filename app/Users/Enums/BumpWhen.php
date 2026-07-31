<?php

namespace App\Users\Enums;

enum BumpWhen: string
{
    case AnySet = 'any_set';
    case LastAtTopWeight = 'last_at_top_weight';
}
