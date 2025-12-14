<h3 class="pl-0 mb-2 little-heading">Popular Entries ( <span class="simple-likes-presets-likes">Boost</span> | <span class="simple-likes-user-likes">Member</span> | <span class="simple-likes-guest-likes">Anonymous</span> )</h3>
<div class="p-0 mb-2 card">
    <table data-size="sm" class="data-table">
        <thead>
            <tr>
                <th colspan="2"><span>Entry</span></th>
                <th class="simple-likes-col-user simple-likes-mobile-hide simple-likes-desktop-show"><span>Collection</span></th>
                <th class="simple-likes-col-time"><span>Likes</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach($popularEntries as $entry)
            <tr>
                <td class="py-1 simple-likes-col-icon">
                    <div class="flex items-center justify-center w-4 h-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M19.479 8V2.5a2 2 0 0 0-2-2h-12a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8l3 3v-3h1a2 2 0 0 0 1.721-.982"></path><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M1.485 3.764A2 2 0 0 0 .479 5.5v16a2 2 0 0 0 2 2h8a2 2 0 0 0 1.712-.967M5.479 3.5h4m-2 4.5V3.5M15.7 7.221l-4.2-1.2 1.2 4.2 7.179 7.179a2.121 2.121 0 0 0 3-3zm3.279 9.279 3-3M12.7 10.221l3-3M12.479 3.5h4m-10 8h4m-4 3h6.5m-6.5 3h9"></path></svg>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-entry">
                    <div class="min-w-0">
                        @if($entry['cp_url'])
                            <a href="{{ $entry['cp_url'] }}" class="hover:text-blue-600 simple-likes-entry-title">
                                {{ $entry['title'] }}
                            </a>
                        @else
                            <span class="simple-likes-entry-title">{{ $entry['title'] }}</span>
                        @endif
                    </div>
                </td>
                <td class="py-1 simple-likes-col-user">
                    <span class="textCurrent simple-likes-mobile-hide simple-likes-desktop-show">{{ $entry['collection'] }}</span>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>
                        {{ $entry['total_likes'] }}
                        @if($entry['preset_count'] > 0 || $entry['member_likes'] > 0 || $entry['anonymous_likes'] > 0)
                            <span class="text-xs simple-likes-mobile-hide simple-likes-desktop-show" title="Preset | Member | Anonymous">
                                (<span class="simple-likes-presets-likes" title="Preset base count">{{ $entry['preset_count'] }}</span>|<span class="simple-likes-user-likes" title="Logged-in member likes">{{ $entry['member_likes'] }}</span>|<span class="simple-likes-guest-likes" title="Anonymous guest likes">{{ $entry['anonymous_likes'] }}</span>)
                            </span>
                        @endif
                        {{ $entry['total_likes'] == 1 ? 'Like' : 'Likes' }}
                    </code>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
