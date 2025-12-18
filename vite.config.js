import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/simple-likes-fieldtype.js'],
            publicDirectory: 'resources/dist',
        }),
        vue(),
        {
            name: 'statamic-vue-external',
            enforce: 'pre',
            resolveId(id) {
                if (id === 'vue') return '\0vue-external';
            },
            load(id) {
                if (id === '\0vue-external') {
                    return `
                        const Vue = window.Vue;
                        export default Vue;
                        export const { computed, ref, watch, watchEffect, reactive, toRef, toRefs,
                            shallowRef, shallowReactive, markRaw, toRaw, isRef, isReactive, unref,
                            readonly, effectScope, onMounted, onUnmounted, onBeforeMount, onBeforeUnmount,
                            onUpdated, onBeforeUpdate, defineComponent, getCurrentInstance,
                            useSlots, useAttrs, mergeProps, withDefaults, nextTick, h, createApp,
                            inject, provide, createElementVNode, createVNode, createTextVNode,
                            createCommentVNode, createStaticVNode, createElementBlock, createBlock,
                            openBlock, Fragment, resolveComponent, resolveDirective, withDirectives,
                            withModifiers, withCtx, toDisplayString, normalizeClass, normalizeStyle,
                            renderList, renderSlot, Transition, TransitionGroup, Teleport } = Vue;
                    `;
                }
            }
        }
    ],
});
