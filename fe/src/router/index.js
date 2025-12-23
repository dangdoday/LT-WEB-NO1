import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import TeacherRegister from '../pages/TeacherRegister.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/teachers/register', name: 'teacher-register', component: TeacherRegister },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
