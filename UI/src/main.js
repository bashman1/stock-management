import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

// PrimeVue 4 core + theming
import PrimeVue from 'primevue/config';
import Lara from '@primevue/themes/lara';
import 'primeicons/primeicons.css';

// Services
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice';

// Components
import DataViewLayoutOptions from 'primevue/dataviewlayoutoptions';
import TriStateCheckbox from 'primevue/tristatecheckbox';
import CodeHighlight from '@/components/CodeHighlight.vue';
import BlockViewer from '@/components/BlockViewer.vue';

// Quill (if you use it)
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

import '@/assets/styles.scss';

const app = createApp(App);

app.use(PrimeVue, {
    ripple: true,
    unstyled: false,
    theme: {
        preset: Lara,
        options: {
            darkModeSelector: 'system',
            cssLayer: false
        }
    }
});

app.use(router);
app.use(ToastService);
app.use(ConfirmationService);
app.use(DialogService);

app.component('DataViewLayoutOptions', DataViewLayoutOptions);
app.component('TriStateCheckbox', TriStateCheckbox);
app.component('CodeHighlight', CodeHighlight);
app.component('BlockViewer', BlockViewer);
app.component('QuillEditor', QuillEditor);

app.mount('#app');