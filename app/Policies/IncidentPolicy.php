<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Incident;
use Illuminate\Auth\Access\Response;

class IncidentPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function forceDelete(User $user): Response
    {

        return $user->role === 'company'
            ? Response::denyAsNotFound()
            : Response::allow();
    }
}
