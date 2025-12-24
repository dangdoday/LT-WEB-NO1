import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
<<<<<<< Updated upstream
=======
import DeviceRegister from '../pages/DeviceRegister.vue'
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'
>>>>>>> Stashed changes

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
<<<<<<< Updated upstream
=======
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
  { path: '/history/borrow_device', name: 'transaction-list', component: HistoryBorrowDevice },
>>>>>>> Stashed changes
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
