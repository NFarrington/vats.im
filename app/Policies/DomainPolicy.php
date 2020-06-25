<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\Url;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class DomainPolicy
{
    use HandlesAuthorization;

    public function createUrl(User $user, Domain $domain)
    {
        return $domain->public || $user->organizations()->whereHas('domains', function (Builder $query) use ($domain) {
                $query->where('domain_id', $domain->id);
            })->exists();
    }
}
