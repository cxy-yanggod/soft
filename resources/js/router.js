import Vue from 'vue';
import VueRouter from 'vue-router';


Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    linkExactActiveClass: 'active',
    routes: [
        {
            path: '/web/share/article/:id',
            name: 'article',
            component: ()=>import('./pages/share/article')
        },
        {
            name: '404',
            path: '/page/404',
            component: () => import('./pages/404')
        },
        {
            path: '*',
            redirect: '/page/404'
        }
    ]
});

export default router;
