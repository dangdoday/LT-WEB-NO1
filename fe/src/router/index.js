import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'
import DeviceRegister from '../pages/DeviceRegister.vue'
import ClassroomCreate from '../pages/ClassroomCreate.vue'
import PasswordMenu from '../pages/PasswordMenu.vue'
import RequestReset from '../pages/RequestReset.vue'
import ResetPassword from '../pages/ResetPassword.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/classrooms/create', name: 'classroom-create', component: ClassroomCreate },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
  { path: '/history/borrow_device', name: 'transaction-list', component: HistoryBorrowDevice },
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
  { path: '/password', name: 'password-menu', component: PasswordMenu },
  { path: '/password/request', name: 'password-request', component: RequestReset },
  { path: '/password/reset', name: 'password-reset', component: ResetPassword },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
