import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'
import BorrowDevice from '../pages/BorrowDevice.vue'
import DeviceRegister from '../pages/DeviceRegister.vue'
import ClassroomCreate from '../pages/ClassroomCreate.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
  { path: '/classrooms/create', name: 'classroom-create', component: ClassroomCreate },
  { path: '/transactions/borrow', name: 'borrow-device', component: BorrowDevice },
  { path: '/devices/register', name: 'device-register', component: DeviceRegister },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
