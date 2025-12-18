<script setup>
import { Panel } from '@statamic/cms/ui';

defineProps({
    entries: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div class="simple-likes-widget three-columns">
        <div class="sl-flex sl-items-center sl-mb-2">
            <h3 class="sl-title" style="margin-bottom: 0">Popular Entries
                <span class="sl-text-xs sl-color-gray sl-hide-mobile">
                (<span class="sl-color-orange">Boosts</span> | <span class="sl-color-blue">Members</span> | <span class="sl-color-purple">Anonymous</span>)
                </span>
            </h3>
        </div>
        <Panel>
            <div v-if="!entries.length" class="sl-empty">
                No entries with likes yet
            </div>
            <table v-else class="data-table" data-table>
                <thead>
                    <tr>
                        <th>Entry</th>
                        <th class="sl-hide-mobile">Collection</th>
                        <th>Likes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(entry, index) in entries" :key="index">
                        <td>
                            <a v-if="entry.cp_url" :href="entry.cp_url">
                                {{ entry.title }}
                            </a>
                            <span v-else>{{ entry.title }}</span>
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
