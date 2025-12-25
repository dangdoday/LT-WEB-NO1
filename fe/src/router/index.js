import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'
import DeviceRegister from '../pages/DeviceRegister.vue'
import ClassroomCreate from '../pages/ClassroomCreate.vue'
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import DeviceSearch from '../pages/DeviceSearch.vue' // <--- 1. Import mới

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/classrooms/create', name: 'classroom-create', component: ClassroomCreate },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
  { path: '/history/borrow_device', name: 'transaction-list', component: HistoryBorrowDevice },
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
  { path: '/transactions/search', name: 'device-search', component: DeviceSearch },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  if (to.name === 'login' || to.name === 'register') {
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