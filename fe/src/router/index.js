import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import TeacherSearch from '../pages/TeacherSearch.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
import HistoryBorrowDevice from '../pages/HistoryBorrowDevice.vue'
import DeviceRegister from '../pages/DeviceRegister.vue'
import ClassroomCreate from '../pages/ClassroomCreate.vue'
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'

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
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})


// Navigation guard: chỉ cho phép vào các trang khi đã login
router.beforeEach(async (to, from, next) => {
  if (to.name === 'login' || to.name === 'register') {
    next()
    return
  }
  // Kiểm tra trạng thái đăng nhập qua API
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
