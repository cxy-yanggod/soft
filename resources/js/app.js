/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;
import Antd from 'ant-design-vue';
import router from './router';
import App from './layouts/App.vue';

import 'ant-design-vue/dist/antd.css';
Vue.config.productionTip = false;

Vue.use(Antd);
const app = new Vue({
    router,
    el: '#app',
    render: h => h(App)
});