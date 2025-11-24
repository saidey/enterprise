<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use Auditable, UsesOrderedUuid;
}
