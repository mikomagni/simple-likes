<script setup>
import { ref, computed } from 'vue';
import { Panel, Button } from '@statamic/cms/ui';

const props = defineProps({
    activities: {
        type: Array,
        required: true
    },
    totalCount: {
        type: Number,
        default: 0
    }
});

const sortField = ref('created_at');
const sortDirection = ref('desc');

const sortedActivities = computed(() => {
    return [...props.activities].sort((a, b) => {
        if (sortField.value === 'entry_title') {
            const aVal = a.entry_title?.toLowerCase() ?? '';
            const bVal = b.entry_title?.toLowerCase() ?? '';
            return sortDirection.value === 'desc'
                ? bVal.localeCompare(aVal)
                : aVal.localeCompare(bVal);
        }
        if (sortField.value === 'user_name') {
            const aVal = a.user_name?.toLowerCase() ?? 'zzz';
            const bVal = b.user_name?.toLowerCase() ?? 'zzz';
            return sortDirection.value === 'desc'
                ? bVal.localeCompare(aVal)
                : aVal.localeCompare(bVal);
        }
        if (sortField.value === 'created_at') {
            const aVal = new Date(a.created_at || 0).getTime();
            const bVal = new Date(b.created_at || 0).getTime();
            return sortDirection.value === 'desc' ? bVal - aVal : aVal - bVal;
        }
        return 0;
    });
});

function toggleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc';
    } else {
        sortField.value = field;
        sortDirection.value = field === 'created_at' ? 'desc' : 'asc';
    }
}

function getSortIcon(field) {
    if (sortField.value !== field) return null;
    return sortDirection.value === 'desc' ? 'sort-desc' : 'sort-asc';
}

function formatTimeAgo(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return __('simple-likes::messages.now');
    if (seconds < 3600) return Math.floor(seconds / 60) + 'm';
    if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
    return Math.floor(seconds / 86400) + 'd';
}
</script>

<template>
    <div class="simple-likes-widget three-columns">
        <h3 class="sl-title">{{ __('simple-likes::messages.recent_activity_title') }} ({{ totalCount }} {{ __('simple-likes::messages.today').toLowerCase() }})</h3>
        <Panel>
            <div v-if="!activities.length" class="sl-empty">
                {{ __('simple-likes::messages.no_activity') }}
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.entry')"
                                :icon-append="getSortIcon('entry_title')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400! sl-ml-1-mobile"
                                @click="toggleSort('entry_title')"
                            />
                        </th>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.user')"
                                :icon-append="getSortIcon('user_name')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                @click="toggleSort('user_name')"
                            />
                        </th>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.when')"
                                :icon-append="getSortIcon('created_at')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                @click="toggleSort('created_at')"
                            />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(activity, index) in sortedActivities" :key="index">
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="sl-icon sl-hide-mobile"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                <a v-if="activity.entry_cp_url" :href="activity.entry_cp_url">{{ activity.entry_title }}</a>
                                <span v-else>{{ activity.entry_title || __('simple-likes::messages.unknown') }}</span>
                            </div>
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
                                    <span class="sl-color-gray sl-hide-mobile">{{ __('simple-likes::messages.guest') }}</span>
                                </template>
                            </div>
                        </td>
                        <td class="sl-color-gray">
                            <span class="pe-2">{{ formatTimeAgo(activity.created_at) }}<span class="sl-hide-mobile"> {{ __('simple-likes::messages.ago') }}</span></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Panel>
    </div>
</template>
