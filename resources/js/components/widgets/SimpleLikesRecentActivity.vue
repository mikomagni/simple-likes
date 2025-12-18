<script setup>
import { Panel } from '@statamic/cms/ui';

defineProps({
    activities: {
        type: Array,
        required: true
    },
    totalCount: {
        type: Number,
        default: 0
    }
});

function formatTimeAgo(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return 'now';
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
    return Math.floor(seconds / 86400) + 'd';
}
</script>

<template>
    <div class="simple-likes-widget three-columns">
        <h3 class="sl-title">Recent Activity ({{ totalCount }} today)</h3>
        <Panel>
            <div v-if="!activities.length" class="sl-empty">
                No activity in the last 24 hours
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>Entry</th>
                        <th>User</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(activity, index) in activities" :key="index">
                        <td>
                            <a v-if="activity.entry_cp_url" :href="activity.entry_cp_url">
                                {{ activity.entry_title }}
                            </a>
                            <span v-else>{{ activity.entry_title || 'Unknown' }}</span>
                        </td>
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <template v-if="activity.user_type === 'authenticated'">
                                    <a v-if="activity.user_edit_url" :href="activity.user_edit_url">
                                        <img
                                            v-if="activity.avatar_url"
                                            :src="activity.avatar_url"
                                            :alt="activity.user_name"
                                            class="sl-avatar"
                                        />
                                        <span v-else class="sl-avatar-initial">{{ activity.avatar_initial }}</span>
                                    </a>
                                    <template v-else>
                                        <img
                                            v-if="activity.avatar_url"
                                            :src="activity.avatar_url"
                                            :alt="activity.user_name"
                                            class="sl-avatar"
                                        />
                                        <span v-else class="sl-avatar-initial">{{ activity.avatar_initial }}</span>
                                    </template>
                                    <a v-if="activity.user_edit_url" :href="activity.user_edit_url" class="sl-hide-mobile">{{ activity.user_name }}</a>
                                    <span v-else class="sl-hide-mobile">{{ activity.user_name }}</span>
                                </template>
                                <template v-else>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="sl-icon sl-color-purple">
                                        <path d="M2 20v-10c0-4.5 4.5-9 10-9s10 4.5 10 9v10l-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2z"/>
                                        <circle cx="8" cy="11" r="1"/>
                                        <circle cx="16" cy="11" r="1"/>
                                    </svg>
                                    <span class="sl-color-gray sl-hide-mobile">Guest</span>
                                </template>
                            </div>
                        </td>
                        <td class="sl-color-gray">
                            <span class="pe-2">{{ formatTimeAgo(activity.created_at) }}<span class="sl-hide-mobile"> ago</span></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Panel>
    </div>
</template>
