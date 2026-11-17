<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportExportType: string
{
    case Import = 'import';
    case Export = 'export';
}
