<script setup>
import { ref, computed } from 'vue';
import { Panel, Button } from '@statamic/cms/ui';

const props = defineProps({
    entries: {
        type: Array,
        required: true
    }
});

const sortField = ref('total_likes');
const sortDirection = ref('desc');

const sortedEntries = computed(() => {
    return [...props.entries].sort((a, b) => {
        if (sortField.value === 'title' || sortField.value === 'collection') {
            const aVal = a[sortField.value]?.toLowerCase() ?? '';
            const bVal = b[sortField.value]?.toLowerCase() ?? '';
            return sortDirection.value === 'desc'
                ? bVal.localeCompare(aVal)
                : aVal.localeCompare(bVal);
        }
        const aVal = a[sortField.value] ?? 0;
        const bVal = b[sortField.value] ?? 0;
        return sortDirection.value === 'desc' ? bVal - aVal : aVal - bVal;
    });
});

function toggleSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc';
    } else {
        sortField.value = field;
        sortDirection.value = (field === 'title' || field === 'collection') ? 'asc' : 'desc';
    }
}

function getSortIcon(field) {
    if (sortField.value !== field) return null;
    return sortDirection.value === 'desc' ? 'sort-desc' : 'sort-asc';
}
</script>

<template>
    <div class="simple-likes-widget three-columns">
        <div class="sl-flex sl-items-center sl-mb-2">
            <h3 class="sl-title" style="margin-bottom: 0">{{ __('simple-likes::messages.popular_entries_title') }}
                <span class="sl-text-xs sl-color-gray sl-hide-mobile">
                (<span class="sl-color-orange">{{ __('simple-likes::messages.boosts') }}</span> | <span class="sl-color-blue">{{ __('simple-likes::messages.members') }}</span> | <span class="sl-color-purple">{{ __('simple-likes::messages.anonymous') }}</span>)
                </span>
            </h3>
        </div>
        <Panel>
            <div v-if="!entries.length" class="sl-empty">
                {{ __('simple-likes::messages.no_entries_with_likes') }}
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.entry')"
                                :icon-append="getSortIcon('title')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400! sl-ml-1-mobile"
                                @click="toggleSort('title')"
                            />
                        </th>
                        <th class="sl-hide-mobile">
                            <Button
                                :text="__('simple-likes::messages.collection')"
                                :icon-append="getSortIcon('collection')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                @click="toggleSort('collection')"
                            />
                        </th>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.likes')"
                                :icon-append="getSortIcon('total_likes')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                @click="toggleSort('total_likes')"
                            />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(entry, index) in sortedEntries" :key="index">
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="sl-icon sl-hide-mobile"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                <a v-if="entry.cp_url" :href="entry.cp_url">
                                    {{ entry.title }}
                                </a>
                                <span v-else>{{ entry.title }}</span>
                            </div>
                        </td>
                        <td class="sl-color-gray sl-hide-mobile">
                            {{ entry.collection }}
                        </td>
                        <td>
                            <span class="sl-pe-mobile">{{ entry.total_likes }}</span>
                            <span
                                v-if="entry.preset_count > 0 || entry.member_likes > 0 || entry.anonymous_likes > 0"
                                class="sl-ml-1 sl-color-gray pe-2 sl-hide-mobile"
                            >
                                (<span class="sl-color-orange">{{ entry.preset_count }}</span> | <span class="sl-color-blue">{{ entry.member_likes }}</span> | <span class="sl-color-purple">{{ entry.anonymous_likes }}</span>)
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Panel>
    </div>
</template>
