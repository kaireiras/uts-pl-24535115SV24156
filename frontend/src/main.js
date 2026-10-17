import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // <-- import router dari ./router/index.js

const app = createApp(App);

app.use(router); // <-- aktifkan router
app.mount('#app');