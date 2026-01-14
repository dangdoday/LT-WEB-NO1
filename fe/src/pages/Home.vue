

<template>
  <div class="home-page">
    <header class="home-header">
      <h1>Quản lý mượn thiết bị</h1>
    </header>
    <div class="main-container">
      <div class="top-actions">
        <button class="logout-btn" @click="handleLogout">
          <span class="logout-icon">🔒</span> Đăng xuất
        </button>
        <button v-if="isAdmin" class="logout-btn reset-btn" @click="goReset">
          Reset password<span v-if="resetRequestCount > 0"> [{{ resetRequestCount }}]</span>
        </button>
      </div>
      <div class="info-section">
        <div class="user-info">
          <span>Tên đăng nhập: <b>{{ loginInfo.login_id }}</b></span>
        </div>
        <div class="user-info">
          <span>Thời gian đăng nhập: <b>{{ loginInfo.login_time }}</b></span>
        </div>
      </div>
      <div class="menu-grid">
        <div class="menu-card">
          <div class="menu-title">Phòng học</div>
          <RouterLink class="menu-link" to="/classrooms/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/classrooms/create">Thêm mới</RouterLink>
        </div>
        <div class="menu-card">
          <div class="menu-title">Giáo viên</div>
          <RouterLink class="menu-link" to="/teachers/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/teachers/register">Thêm mới</RouterLink>
        </div>
        <div class="menu-card">
          <div class="menu-title">Thiết bị</div>
          <RouterLink class="menu-link" to="/transactions/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/devices/register">Thêm mới</RouterLink>
        </div>
        <div class="menu-card">
          <div class="menu-title">Mượn/trả thiết bị</div>
          <RouterLink class="menu-link" to="/transactions/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/devices/advanced-search">Tìm kiếm nâng cao</RouterLink>
          <RouterLink class="menu-link" to="/transactions/return">Trả thiết bị</RouterLink>
          <!-- Đã xóa nút Mượn thiết bị -->
          <RouterLink class="menu-link" to="/history/borrow_device">Lịch sử mượn thiết bị</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const loginInfo = ref({ login_id: '', login_time: '' })
const isAdmin = computed(() => (loginInfo.value.login_id || '').toLowerCase() === 'admin')
const resetRequestCount = ref(0)
const router = useRouter()

onMounted(async () => {
  try {
    const res = await fetch('/api/home.php')
    if (res.ok) {
      loginInfo.value = await res.json()
      if (isAdmin.value) {
        await fetchResetCount()
      }
    }
  } catch (e) {
    // Xử lý lỗi nếu cần
  }
})

async function fetchResetCount() {
  try {
    const res = await fetch('/api/reset_list.php')
    if (!res.ok) {
      return
    }
    const payload = await res.json()
    resetRequestCount.value = Array.isArray(payload.items) ? payload.items.length : 0
  } catch (e) {
    resetRequestCount.value = 0
  }
}

async function handleLogout() {
  try {
    await fetch('/api/logout.php', { method: 'POST' })
  } catch (e) {}
  router.replace({ name: 'login' })
}

function goReset() {
  router.push('/reset-password')
}
</script>
.logout-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 28px;
  background: linear-gradient(90deg, #e74c3c 0%, #f39c12 100%);
  color: #fff;
  border: none;
  border-radius: 24px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 12px #e74c3c40;
  transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
}
.logout-btn:hover {
  background: linear-gradient(90deg, #c0392b 0%, #e67e22 100%);
  box-shadow: 0 4px 18px #e74c3c60;
  transform: translateY(-2px) scale(1.04);
}
.top-actions {
  position: absolute;
  top: 24px;
  right: 24px;
  display: flex;
  gap: 8px;
  z-index: 10;
}
.main-container {
  position: relative;
}
.logout-icon {
  font-size: 20px;
  margin-right: 4px;
}
.reset-btn {
  background: linear-gradient(90deg, #e74c3c 0%, #f39c12 100%);
}


<style scoped>
.home-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f6f1e8 0%, #dbe4f2 100%);
}
.home-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 32px 0 0 0;
}
.logo {
  display: none;
}
.home-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2e6db4;
  margin: 0;
}
.main-container {
  width: min(900px, 96vw);
  margin: 32px auto;
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 4px 24px #2e6db420;
  padding: 40px 32px;
  min-height: 400px;
}
.info-section {
.info-section {
  margin-bottom: 40px;
  font-size: 15px;
  color: #2e6db4;
}
.info-flex {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
}
}
.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
}
.user-icon, .clock-icon {
  font-size: 1.3em;
}
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 32px;
}
.menu-card {
  background: #f6f8fc;
  border-radius: 18px;
  box-shadow: 0 2px 12px #2e6db410;
  padding: 28px 18px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  transition: box-shadow 0.2s, transform 0.2s;
}
.menu-card:hover {
  box-shadow: 0 6px 24px #2e6db430;
  transform: translateY(-2px) scale(1.03);
}
.menu-icon {
  display: none;
}
.menu-title {
  font-size: 1.1em;
  font-weight: 600;
  color: #2e6db4;
  margin-bottom: 6px;
}
.menu-link {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 10px;
  background: #2e6db4;
  color: #fff;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 6px;
  transition: background 0.2s;
}
.menu-link:hover {
  background: #174a7e;
}
@media (max-width: 700px) {
  .main-container {
    padding: 18px 6px;
  }
  .menu-grid {
    gap: 16px;
  }
  .info-section {
    gap: 12px;
    font-size: 13px;
  }
}
</style>
