/**
 * Created by ZEUS on 5/22/2018.
 */
import Home from './components/Pages/Home.vue';

const routes = [
    {
        path: '/chrecorder/public/home',
        component: Home,
        name: 'home'
    },
    {
        path: '/chrecorder/public/',
        component: Home,
        name: 'home'
    },
];

export default routes;