<script setup>
import { Panel } from '@statamic/cms/ui';

defineProps({
    users: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="simple-likes-widget">
        <h3 class="sl-title">Top Users</h3>
        <Panel>
            <div v-if="!users.length" class="sl-empty">
                No user activity yet
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Likes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(user, index) in users" :key="index">
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
