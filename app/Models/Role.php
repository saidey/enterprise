<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable, UsesOrderedUuid;
}
