// CSS
import '../css/cp.css';

// Fieldtype
import SimpleLikes from './components/fieldtypes/SimpleLikes.vue';

// Widgets
import SimpleLikesOverview from './components/widgets/SimpleLikesOverview.vue';
import SimpleLikesRecentActivity from './components/widgets/SimpleLikesRecentActivity.vue';
import SimpleLikesPopularEntries from './components/widgets/SimpleLikesPopularEntries.vue';
import SimpleLikesTopUsers from './components/widgets/SimpleLikesTopUsers.vue';

Statamic.booting(() => {
    // Fieldtype
    Statamic.$components.register('simple_likes-fieldtype', SimpleLikes);

    // Widgets
    Statamic.$components.register('SimpleLikesOverview', SimpleLikesOverview);
    Statamic.$components.register('SimpleLikesRecentActivity', SimpleLikesRecentActivity);
    Statamic.$components.register('SimpleLikesPopularEntries', SimpleLikesPopularEntries);
    Statamic.$components.register('SimpleLikesTopUsers', SimpleLikesTopUsers);
});
