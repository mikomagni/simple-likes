<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const realLikes = computed(() => props.meta?.realLikes ?? 0);
const totalLikes = computed(() => (parseInt(props.value) || 0) + realLikes.value);

function updateValue(value) {
    update(parseInt(value) || 0);
}
</script>

<template>
    <div class="simple-likes-fieldtype">
        <!-- Boost (Editable) -->
        <div class="flex items-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 sl-color-red" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Boost Likes'">
                <path d="m14.479 19.374-.971.939a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.219 1.49"/>
                <path d="M15 15h6"/>
                <path d="M18 12v6"/>
            </svg>
            <Input
                :model-value="value"
                @update:model-value="updateValue"
                type="text"
                pattern="[0-9]*"
                placeholder="0"
            />
        </div>

        <!-- Real Likes (Read-only) -->
        <div class="flex items-center text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 sl-color-red" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Real Likes'">
                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
            </svg>
            <span class="font-medium">Real: {{ realLikes }}</span>
        </div>

        <!-- Total Display -->
        <div class="flex items-center text-sm" style="margin-top: 0.875rem;">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 sl-color-red" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Total Likes'">
                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
            </svg>
            <span class="font-medium">Total: {{ totalLikes }}</span>
        </div>
    </div>
</template>
