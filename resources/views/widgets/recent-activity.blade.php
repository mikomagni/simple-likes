<h3 class="pl-0 mb-2 little-heading">Recent Activity @if(isset($overview))({{ $overview['recent_activity'] }} {{ $overview['recent_activity'] == 1 ? 'like' : 'likes' }} today)@endif</h3>
<div class="p-0 mb-2 card">
    <table data-size="sm" class="data-table">
        <thead>
            <tr>
                <th colspan="2"><span>Entry</span></th>
                <th class="simple-likes-col-user simple-likes-mobile-hide simple-likes-desktop-show"><span>User</span></th>
                <th class="simple-likes-col-time"><span>When</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentActivity as $activity)
            <tr>
                <td class="py-1 simple-likes-col-icon">
                    <div class="flex items-center justify-center w-4 h-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M19.479 8V2.5a2 2 0 0 0-2-2h-12a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8l3 3v-3h1a2 2 0 0 0 1.721-.982"></path><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M1.485 3.764A2 2 0 0 0 .479 5.5v16a2 2 0 0 0 2 2h8a2 2 0 0 0 1.712-.967M5.479 3.5h4m-2 4.5V3.5M15.7 7.221l-4.2-1.2 1.2 4.2 7.179 7.179a2.121 2.121 0 0 0 3-3zm3.279 9.279 3-3M12.7 10.221l3-3M12.479 3.5h4m-10 8h4m-4 3h6.5m-6.5 3h9"></path></svg>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-entry">
                    <div class="min-w-0">
                        @if($activity['entry_cp_url'])
                            <a href="{{ $activity['entry_cp_url'] }}" class="hover:text-blue-600 simple-likes-entry-title">
                                {{ $activity['entry_title'] }}
                            </a>
                        @else
                            <span class="simple-likes-entry-title">{{ $activity['entry_title'] }}</span>
                        @endif
                    </div>
                </td>
                <td class="py-1 simple-likes-col-user">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center mr-2 simple-likes-avatar-container">
                            @if($activity['user_type'] === 'guest')
                                <div class="w-4 h-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 20v-10c0-4.5 4.5-9 10-9s10 4.5 10 9v10l-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2z"/>
                                        <circle cx="8" cy="11" r="1"/>
                                        <circle cx="16" cy="11" r="1"/>
                                    </svg>
                                </div>
                            @else
                                @if($activity['user_edit_url'])
                                    <a href="{{ $activity['user_edit_url'] }}" class="hover:opacity-75" title="{{ $activity['user_name'] }}">
                                        @if($activity['avatar_url'])
                                            <img src="{{ $activity['avatar_url'] }}"
                                                 alt="{{ $activity['user_name'] }}"
                                                 class="w-4 h-4 rounded-full"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-blue-500 rounded-full">
                                                {{ $activity['avatar_initial'] ?? substr($activity['user_name'], 0, 1) }}
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center w-4 h-4 text-tiny text-white bg-blue-500 rounded-full">
                                                {{ $activity['avatar_initial'] ?? substr($activity['user_name'], 0, 1) }}
                                            </div>
                                        @endif
                                    </a>
                                @else
                                    @if($activity['avatar_url'])
                                        <img src="{{ $activity['avatar_url'] }}"
                                             alt="{{ $activity['user_name'] }}"
                                             class="w-4 h-4 rounded-full"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="items-center justify-center hidden w-4 h-4 text-xs font-bold text-white bg-blue-500 rounded-full">
                                            {{ $activity['avatar_initial'] ?? substr($activity['user_name'], 0, 1) }}
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center w-4 h-4 text-tiny text-white bg-blue-500 rounded-full">
                                            {{ $activity['avatar_initial'] ?? substr($activity['user_name'], 0, 1) }}
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                        @if($activity['user_type'] === 'guest')
                            <span class="currentColor simple-likes-mobile-hide simple-likes-desktop-show">Guest</span>
                        @else
                            <div class="min-w-0 truncate simple-likes-mobile-hide simple-likes-desktop-show-block">
                                @if($activity['user_edit_url'])
                                    <a href="{{ $activity['user_edit_url'] }}" class="truncate hover:text-blue-600">
                                        {{ $activity['user_name'] }}
                                    </a>
                                @else
                                    <span class="truncate">{{ $activity['user_name'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>
                        <span class="simple-likes-mobile-hide simple-likes-desktop-show">{{ $activity['created_at']->diffForHumans() }}</span>
                        <span class="simple-likes-mobile-show simple-likes-desktop-hide">
                            @php
                                $timeString = $activity['created_at']->diffForHumans();
                                $shortTime = str_replace([
                                    ' seconds ago', ' minute ago',
                                    ' minutes ago', ' minute ago',
                                    ' hours ago', ' hour ago',
                                    ' days ago', ' day ago',
                                    ' weeks ago', ' week ago',
                                    ' months ago', ' month ago',
                                    ' years ago', ' year ago'
                                ], [
                                    ' sec', ' sec',
                                    ' min', ' min',
                                    ' hr', ' hr',
                                    ' day', ' day',
                                    ' wk', ' wk',
                                    ' mo', ' mo',
                                    ' yr', ' yr'
                                ], $timeString);
                            @endphp
                            {{ $shortTime }}
                        </span>
                    </code>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
