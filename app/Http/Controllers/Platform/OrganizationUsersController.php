<?php

namespace App\Http\Controllers\Platform;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationUsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Models\Organization $organization
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $users = $organization->users->pluck('pivot.user_id');
        $attributes = $this->validate($request, [
            'id' => [
                'required',
                'integer',
                'exists:users',
                Rule::notIn($users->toArray()),
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::in([
                    OrganizationUser::ROLE_MANAGER,
                    OrganizationUser::ROLE_MEMBER,
                ]),
            ],
        ], [
            'id.exists' => 'That user has never logged in.',
            'id.not_in' => 'That user is already in this organization.',
        ]);

        $organization->users()->attach(
            $attributes['id'],
            ['role_id' => $attributes['role_id']]
        );

        return redirect()->route('platform.organizations.edit', $organization)
            ->with('success', 'User added.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \App\Models\Organization $organization
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Request $request, Organization $organization, User $user)
    {
        $this->authorize('update', $organization);

        if ($request->user()->id == $user->id) {
            return redirect()->route('platform.organizations.edit', $organization)
                ->with('error', 'You cannot remove yourself.');
        }

        $organization->users()->where('users.id', $user->id)->first()->pivot
            ->update(['deleted_at' => Carbon::now()]);

        return redirect()->route('platform.organizations.edit', $organization)
            ->with('success', 'User deleted.');
    }
}