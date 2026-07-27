<?php

namespace App\Users\Enums;

enum WarmUpDefaultsScope: string
{
    case AllBlocks = 'all_blocks';
    case FirstBlock = 'first_block';
}
