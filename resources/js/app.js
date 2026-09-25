import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VWave from 'v-wave'
import VueSelect from 'vue-select'
import Toast, { POSITION } from 'vue-toastification'
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

import App from './App.vue'
import router from './router'
import 'vue-select/dist/vue-select.css'
import 'vue-toastification/dist/index.css'

const app = createApp(App)

app.component('v-select', VueSelect)
app.use(VWave, {
    color: 'currentColor',
    initialOpacity: 0.16,
    easing: 'ease-out',
    duration: 0.37,
})
app.use(Toast, {
    position: POSITION.BOTTOM_CENTER,
    timeout: 4500,
    pauseOnFocusLoss: false,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.4,
    hideProgressBar: true,
    maxToasts: 3,
})
app.use(createPinia())
app.use(router)

app.mount('#app')
