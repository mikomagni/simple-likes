<script setup>
import { ref, computed } from 'vue';
import { Panel, Button } from '@statamic/cms/ui';

const props = defineProps({
    users: {
        type: Array,
        required: true
    }
});

const sortField = ref('likes_given');
const sortDirection = ref('desc');

const sortedUsers = computed(() => {
    return [...props.users].sort((a, b) => {
        if (sortField.value === 'name') {
            const aVal = a.name?.toLowerCase() ?? '';
            const bVal = b.name?.toLowerCase() ?? '';
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
        sortDirection.value = field === 'name' ? 'asc' : 'desc';
    }
}

function getSortIcon(field) {
    if (sortField.value !== field) return null;
    return sortDirection.value === 'desc' ? 'sort-desc' : 'sort-asc';
}
</script>

<template>
    <div class="simple-likes-widget">
        <h3 class="sl-title">{{ __('simple-likes::messages.top_users_title') }}</h3>
        <Panel>
            <div v-if="!users.length" class="sl-empty">
                {{ __('simple-likes::messages.no_user_activity') }}
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.user')"
                                :icon-append="getSortIcon('name')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400! sl-ml-1-mobile"
                                @click="toggleSort('name')"
                            />
                        </th>
                        <th>
                            <Button
                                :text="__('simple-likes::messages.likes')"
                                :icon-append="getSortIcon('likes_given')"
                                size="sm"
                                variant="ghost"
                                class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
                                @click="toggleSort('likes_given')"
                            />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(user, index) in sortedUsers" :key="index">
                        <td>
                            <div class="sl-flex sl-items-center sl-gap-2">
                                <a v-if="user.edit_url && user.can_view_users" :href="user.edit_url">
                                    <img
                                        v-if="user.avatar_url"
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="sl-avatar"
                                    />
                                    <span v-else class="sl-avatar-initial">{{ user.avatar_initial }}</span>
                                </a>
                                <template v-else>
                                    <img
                                        v-if="user.avatar_url"
                                        :src="user.avatar_url"
                                        :alt="user.name"
                                        class="sl-avatar"
                                    />
                                    <span v-else class="sl-avatar-initial">{{ user.avatar_initial }}</span>
                                </template>
                                <a v-if="user.edit_url && user.can_view_users" :href="user.edit_url">{{ user.name }}</a>
                                <span v-else>{{ user.name }}</span>
                            </div>
                        </td>
                        <td><span class="pe-2">{{ user.likes_given }}</span></td>
                    </tr>
                </tbody>
            </table>
        </Panel>
    </div>
</template>
