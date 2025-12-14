<h3 class="pl-0 mb-2 little-heading">Simple Likes Overview</h3>
<div class="p-0 mb-2 card">
    <table data-size="sm" class="data-table">
        <thead>
            <tr>
                <th class="simple-likes-col-entry-active"><span>Metric</span></th>
                <th class="simple-likes-col-time"><span>Count</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 20 20" class="simple-likes-user-likes">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span title="Combined total: Preset + Real Interactions">Total Likes</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['total_likes']) }}</code>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="simple-likes-real-likes"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><polyline points="17,11 19,13 23,9"/></svg>
                        </div>
                        <span title="Actual clicks from real users (members + anonymous)">Real Interactions</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['interactions_count']) }}</code>
                    <span class="simple-likes-real-likes simple-likes-mobile-hide simple-likes-desktop-show ml-2">({{ $overview['interactions_percent'] }}%)</span>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="simple-likes-presets-likes"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                        </div>
                        <span title="Base count manually set in the field (seed/fake likes)">Boost Count</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['preset_count']) }}</code>
                    <span class="simple-likes-presets-likes simple-likes-mobile-hide simple-likes-desktop-show ml-2">({{ $overview['preset_percent'] }}%)</span>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="simple-likes-user-likes">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <span title="Likes from logged-in/authenticated users">User Likes</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['member_likes']) }}</code>
                    <span class="simple-likes-user-likes simple-likes-mobile-hide simple-likes-desktop-show ml-2">({{ $overview['member_percent'] }}%)</span>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="simple-likes-guest-likes">
                                <path d="M2 20v-10c0-4.5 4.5-9 10-9s10 4.5 10 9v10l-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2z"/>
                                <circle cx="8" cy="11" r="1"/>
                                <circle cx="16" cy="11" r="1"/>
                            </svg>
                        </div>
                        <span title="Likes from anonymous/guest visitors (tracked via IP)">Anonymous Likes</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['anonymous_likes']) }}</code>
                    <span class="simple-likes-guest-likes simple-likes-mobile-hide simple-likes-desktop-show ml-2">({{ $overview['anonymous_percent'] }}%)</span>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                        </div>
                        <span>Today</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['today_likes']) }}</code>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <span>This Week</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['week_likes']) }}</code>
                </td>
            </tr>
            <tr>
                <td class="py-1 simple-likes-col-entry-active">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-4 h-4 mr-3 simple-likes-mobile-hide simple-likes-desktop-show">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M19.479 8V2.5a2 2 0 0 0-2-2h-12a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8l3 3v-3h1a2 2 0 0 0 1.721-.982"></path><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M1.485 3.764A2 2 0 0 0 .479 5.5v16a2 2 0 0 0 2 2h8a2 2 0 0 0 1.712-.967M5.479 3.5h4m-2 4.5V3.5M15.7 7.221l-4.2-1.2 1.2 4.2 7.179 7.179a2.121 2.121 0 0 0 3-3zm3.279 9.279 3-3M12.7 10.221l3-3M12.479 3.5h4m-10 8h4m-4 3h6.5m-6.5 3h9"></path></svg>
                        </div>
                        <span>Entries with Likes</span>
                    </div>
                </td>
                <td class="py-1 simple-likes-col-time">
                    <code>{{ number_format($overview['total_entries']) }}</code>
                </td>
            </tr>
        </tbody>
    </table>
</div>
