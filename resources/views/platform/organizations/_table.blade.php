@if($organizations->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Managers</th>
                <th>Members</th>
                <th>Created</th>
                <th></th>
                <th></th>
            </tr>
            @foreach($organizations as $organization)
                <tr>
                    <td>{{ $organization->id }}</td>
                    <td class="break-all">{{ $organization->name }}</td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->managers as $manager)
                                <li>
                                    <span title="{{ $manager->full_name }} ({{ $manager->id }})" class="text-limit">
                                        {{ $manager->full_name }} ({{ $manager->id }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>
                        <ul class="list-unstyled">
                            @forelse($organization->members as $member)
                                <li>
                                    <span title="{{ $member->full_name }} ({{ $member->id }})" class="text-limit">
                                        {{ $member->full_name }} ({{ $member->id }})
                                    </span>
                                </li>
                            @empty
                                <li>
                                    &mdash;
                                </li>
                            @endforelse
                        </ul>
                    </td>
                    <td>{{ hyphen_nobreak($organization->created_at) }}</td>
                    <td>
                        @can('update', $organization)
                            <a href="{{ route('platform.organizations.edit', $organization) }}">Edit</a>
                        @else
                            <s class="text-muted">Edit</s>
                        @endcan
                    </td>
                    <td>
                        @can('delete', $organization)
                            <delete-resource link-only
                                             route="{{ route('platform.organizations.destroy', $organization) }}"></delete-resource>
                        @else
                            <s class="text-muted">Delete</s>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-auto">{{ $organizations->links() }}</div>
@else
    <div class="card-body text-center">
        <span>Nothing to show.</span>
    </div>
@endif