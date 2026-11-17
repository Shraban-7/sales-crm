<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportExportModule: string
{
    case Leads = 'leads';
    case Contacts = 'contacts';
    case Companies = 'companies';
    case Deals = 'deals';
}
