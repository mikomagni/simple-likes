<script setup>
import { Panel } from '@statamic/cms/ui';

defineProps({
    overview: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="simple-likes-widget">
        <h3 class="sl-title">{{ __('simple-likes::messages.overview_title') }}</h3>
        <Panel>
            <table class="data-table" data-table>
                <thead>
                    <tr>
                        <th><span class="sl-ml-1-mobile">{{ __('simple-likes::messages.metric') }}</span></th>
                        <th>{{ __('simple-likes::messages.count') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg fill="currentColor" viewBox="0 0 20 20" class="sl-icon sl-color-red">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.total_likes') }}</span>
                            </div>
                        </td>
                        <td><span class="pe-2">{{ overview.total_likes?.toLocaleString() ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="sl-icon sl-color-green">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                    <polyline points="17,11 19,13 23,9"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.real_interactions') }}</span>
                            </div>
                        </td>
                        <td>
                            {{ overview.interactions_count?.toLocaleString() ?? 0 }}
                            <span class="sl-color-green sl-ml-1 pe-2">({{ overview.interactions_percent ?? 0 }}%)</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="sl-icon sl-color-orange">
                                    <path d="m12 14 4-4"/>
                                    <path d="M3.34 19a10 10 0 1 1 17.32 0"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.boost_count') }}</span>
                            </div>
                        </td>
                        <td>
                            {{ overview.preset_count?.toLocaleString() ?? 0 }}
                            <span class="sl-color-orange sl-ml-1 pe-2">({{ overview.preset_percent ?? 0 }}%)</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="sl-icon sl-color-blue">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.user_likes') }}</span>
                            </div>
                        </td>
                        <td>
                            {{ overview.member_likes?.toLocaleString() ?? 0 }}
                            <span class="sl-color-blue sl-ml-1 pe-2">({{ overview.member_percent ?? 0 }}%)</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="sl-icon sl-color-purple">
                                    <path d="M2 20v-10c0-4.5 4.5-9 10-9s10 4.5 10 9v10l-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2z"/>
                                    <circle cx="8" cy="11" r="1"/>
                                    <circle cx="16" cy="11" r="1"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.anonymous_likes') }}</span>
                            </div>
                        </td>
                        <td>
                            {{ overview.anonymous_likes?.toLocaleString() ?? 0 }}
                            <span class="sl-color-purple sl-ml-1 pe-2">({{ overview.anonymous_percent ?? 0 }}%)</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="sl-icon">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12,6 12,12 16,14"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.today') }}</span>
                            </div>
                        </td>
                        <td><span class="pe-2">{{ overview.today_likes?.toLocaleString() ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="sl-icon">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>{{ __('simple-likes::messages.this_week') }}</span>
                            </div>
                        </td>
                        <td><span class="pe-2">{{ overview.week_likes?.toLocaleString() ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="sl-icon"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                <span>{{ __('simple-likes::messages.entries_with_likes') }}</span>
                            </div>
                        </td>
                        <td><span class="pe-2">{{ overview.total_entries?.toLocaleString() ?? 0 }}</span></td>
                    </tr>
                </tbody>
            </table>
        </Panel>
    </div>
</template>
