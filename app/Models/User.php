<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Company\Models\Company;
use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use RuntimeException;
use Spatie\Permission\Contracts\Role as SpatieRoleContract;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Auditable, HasApiTokens, HasFactory, Notifiable, UsesOrderedUuid, SoftDeletes;

    use HasRoles {
        assignRole as protected traitAssignRole;
        syncRoles as protected traitSyncRoles;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'national_id',
        'date_of_birth',
        'gender',
        'address_line1',
        'address_line2',
        'island',
        'atoll',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot(['role', 'is_owner', 'is_default'])
            ->withTimestamps();
    }

    public function defaultCompany()
    {
        return $this->companies()->wherePivot('is_default', true)->first();
    }

    public function assignRole(...$roles)
    {
        $this->ensureUniqueSuperadmin($roles);

        return $this->traitAssignRole(...$roles);
    }

    public function syncRoles(...$roles)
    {
        $this->ensureUniqueSuperadmin($roles);

        return $this->traitSyncRoles(...$roles);
    }

    private function ensureUniqueSuperadmin(array $rolesInput): void
    {
        $names = collect($rolesInput)
            ->flatten()
            ->map(function ($role) {
                if ($role instanceof SpatieRoleContract) {
                    return $role->name;
                }
                if (is_string($role)) {
                    return $role;
                }
                if (is_int($role)) {
                    return \App\Models\Role::find($role)?->name;
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();

        if (! in_array('superadmin', $names, true)) {
            return;
        }

        $existingSuper = static::role('superadmin')
            ->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))
            ->first();

        if ($existingSuper) {
            throw new RuntimeException('Only one superadmin is allowed.');
        }
    }
}
