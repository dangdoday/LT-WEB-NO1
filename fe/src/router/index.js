import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import TeacherSearch from '../pages/TeacherSearch.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'
import DeviceRegister from '../pages/DeviceRegister.vue'
import ClassroomCreate from '../pages/ClassroomCreate.vue'
import DeviceAdvancedSearch from '../pages/DeviceAdvancedSearch.vue'
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import DeviceSearch from '../pages/DeviceSearch.vue'
import ReturnDevice from '../pages/ReturnDevice.vue'
import RequestReset from '../pages/RequestReset.vue'
import ResetPassword from '../pages/ResetPassword.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/teachers/search', name: 'teacher-search', component: TeacherSearch },
  { path: '/classrooms/create', name: 'classroom-create', component: ClassroomCreate },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
  { path: '/history/borrow_device', name: 'transaction-list', component: HistoryBorrowDevice },
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
  { path: '/transactions/return', name: 'transaction-return', component: ReturnDevice },
  { path: '/devices/advanced-search', name: 'device-advanced-search', component: DeviceAdvancedSearch },
  { path: '/transactions/search', name: 'device-search', component: DeviceSearch },
  { path: '/reset-password-request', name: 'reset-password-request', component: RequestReset },
  { path: '/reset-password', name: 'reset-password', component: ResetPassword },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  if (['login', 'register', 'reset-password-request', 'reset-password'].includes(to.name)) {
    next()
    return
  }
  try {
    const res = await fetch('/api/home.php')
    if (res.ok) {
      const data = await res.json()
      if (data.login_id) {
        next()
        return
      }
    }
  } catch (e) {}
  next({ name: 'login' })
})

export default router
