

<template>
  <div class="home-page">
    <header class="home-header">
      <h1>Quản lý mượn thiết bị</h1>
    </header>
    <div class="main-container">
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
          <RouterLink class="menu-link" to="/devices/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/devices/register">Thêm mới</RouterLink>
        </div>
        <div class="menu-card">
          <div class="menu-title">Mượn/trả thiết bị</div>
          <RouterLink class="menu-link" to="/transactions/search">Tìm kiếm</RouterLink>
          <RouterLink class="menu-link" to="/transactions/advanced-search">Tìm kiếm nâng cao</RouterLink>
          <RouterLink class="menu-link" to="/transactions/return">Trả thiết bị</RouterLink>
          <RouterLink class="menu-link" to="/transactions/borrow">Mượn thiết bị</RouterLink>
          <RouterLink class="menu-link" to="/history/borrow_device">Lịch sử mượn thiết bị</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'

const loginInfo = ref({ login_id: '', login_time: '' })

onMounted(async () => {
  try {
    const res = await fetch('/api/home.php')
    if (res.ok) {
      loginInfo.value = await res.json()
    }
  } catch (e) {
    // Xử lý lỗi nếu cần
  }
})
</script>


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
  margin-bottom: 40px;
  font-size: 15px;
  color: #2e6db4;
  display: flex;
  gap: 32px;
  flex-wrap: wrap;
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
