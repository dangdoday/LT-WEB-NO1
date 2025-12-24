import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
<<<<<<< HEAD
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'

=======
import DeviceRegister from '../pages/DeviceRegister.vue'
>>>>>>> main

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
<<<<<<< HEAD
  { path: '/history/borrow_device', name: 'transaction-list', component: HistoryBorrowDevice },
=======
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
>>>>>>> main
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
