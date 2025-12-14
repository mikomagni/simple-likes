<h3 class="pl-0 mb-2 little-heading">Top Users</h3>
<div class="p-0 mb-2 card">
    <table data-size="sm" class="data-table">
        <thead>
            <tr>
                <th class="simple-likes-col-entry-active"><span>User</span></th>
                <th class="simple-likes-col-time"><span>Likes</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($topUsers as $user)
            <tr>
                <td class="flex items-center py-1 simple-likes-col-entry-active">
                    <div class="flex items-center justify-center simple-likes-avatar-container-active simple-likes-mobile-hide simple-likes-desktop-show">
                        @if($user['edit_url'])
                            <a href="{{ $user['edit_url'] }}" class="hover:opacity-75" title="{{ $user['name'] }}">
                                @if($user['avatar_url'])
                                    <img src="{{ $user['avatar_url'] }}"
                                         alt="{{ $user['name'] }}"
                                         class="w-4 h-4 rounded-full"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-blue-500 rounded-full">
                                        {{ $user['avatar_initial'] ?? substr($user['name'], 0, 1) }}
                                    </div>
                                @else
                                    <div class="flex items-center justify-center w-4 h-4 text-tiny text-white bg-blue-500 rounded-full">
                                        {{ $user['avatar_initial'] ?? substr($user['name'], 0, 1) }}
                                    </div>
                                @endif
                            </a>
                        @else
                            @if($user['avatar_url'])
                                <img src="{{ $user['avatar_url'] }}"
                                     alt="{{ $user['name'] }}"
                                     class="w-4 h-4 rounded-full"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-blue-500 rounded-full">
                                    {{ $user['avatar_initial'] ?? substr($user['name'], 0, 1) }}
                                </div>
                            @else
                                <div class="flex items-center justify-center w-4 h-4 text-tiny text-white bg-blue-500 rounded-full">
                                    {{ $user['avatar_initial'] ?? substr($user['name'], 0, 1) }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="min-w-0 truncate">
                        @if($user['edit_url'])
                            <a href="{{ $user['edit_url'] }}" class="truncate hover:text-blue-600">
                                {{ $user['name'] }}
                            </a>
                        @else
                            <span class="truncate">{{ $user['name'] }}</span>
                        @endif
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ $user['likes_given'] }} {{ $user['likes_given'] == 1 ? 'like' : 'likes' }}</code>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
