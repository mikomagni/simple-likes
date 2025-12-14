<template>
    <div class="simple-likes-fieldtype">
        <!-- Boost (Editable) -->
        <div class="flex items-center mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Boost Likes'">
                <path d="m14.479 19.374-.971.939a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5a5.2 5.2 0 0 1-.219 1.49"/>
                <path d="M15 15h6"/>
                <path d="M18 12v6"/>
            </svg>
            <input
                type="text"
                :value="value"
                class="input-text flex-1"
                @input="updateValue($event.target.value)"
                pattern="[0-9]*"
                placeholder="0"
            />
        </div>

        <!-- Real Likes (Read-only) -->
        <div class="flex items-center text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Real Likes'">
                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
            </svg>
            <span class="font-medium">Real: {{ realLikes }}</span>
        </div>

        <!-- Total Display -->
        <div class="flex items-center text-sm" style="margin-top: 0.875rem;">
            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-3 text-red-500" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-tooltip="'Total Likes'">
                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>
            </svg>
            <span class="font-medium">Total: {{ totalLikes }}</span>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    computed: {
        realLikes() {
            return this.meta?.realLikes ?? 0;
        },
        totalLikes() {
            const preset = parseInt(this.value) || 0;
            return preset + this.realLikes;
        }
    },

    methods: {
        updateValue(value) {
            const numValue = parseInt(value) || 0;
            this.$emit('input', numValue);
        }
    }
}
</script>
