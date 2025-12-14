import SimpleLikes from './components/fieldtypes/SimpleLikes.vue';

Statamic.booting(() => {
    Statamic.$components.register('simple_likes-fieldtype', SimpleLikes);
});
