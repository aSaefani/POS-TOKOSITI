import Alpine from 'alpinejs';
import axios from 'axios';
import './lib/money';
import { registerPos } from './pos';
import { registerDashboard } from './dashboard';

// Axios: kirim CSRF token Laravel di setiap request AJAX (header X-XSRF-TOKEN dari cookie).
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
window.axios = axios;

window.Alpine = Alpine;

Alpine.data('pos', registerPos);
Alpine.data('dashboard', registerDashboard);

Alpine.start();
