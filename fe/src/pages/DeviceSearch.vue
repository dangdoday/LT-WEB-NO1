<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const devices = ref([]);
const searchParams = ref({
  keyword: '',
  status: '' // Mặc định là rỗng (Tất cả)
});

const loading = ref(false);

// Hàm gọi API
const fetchDevices = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams(searchParams.value).toString();
    // Lưu ý: Nếu cần dùng apiBase như các file trước thì sửa lại dòng này
    const res = await fetch(`/api/device_search.php?${params}`);
    const data = await res.json();

    if (data.status === 'success') {
      devices.value = data.data;
    } else {
      alert(data.message || 'Lỗi lấy dữ liệu');
    }
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

// Action Tìm kiếm
const handleSearch = () => {
  fetchDevices();
};

// Action Xóa
const handleDelete = async (device) => {
  const confirmMsg = `Bạn chắc chắn muốn xóa thiết bị "${device.name}" ?`;
  if (!confirm(confirmMsg)) return;

  try {
    const res = await fetch('/api/device_delete.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: device.id })
    });
    const data = await res.json();

    if (data.status === 'success') {
      alert('Xóa thành công!');
      fetchDevices();
    } else {
      alert(data.message);
    }
  } catch (error) {
    alert('Lỗi kết nối server');
  }
};

// Action Sửa
const handleEdit = (id) => {
  router.push({ name: 'device-update', query: { id } });
};

// Initial display
onMounted(() => {
  fetchDevices();
});
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Management</p>
          <h1>Tìm kiếm thiết bị</h1>
        </div>
      </header>

      <!-- Form tìm kiếm -->
      <div class="form-grid search-grid">
        <label>Từ khóa</label>
        <input
            type="text"
            v-model="searchParams.keyword"
            placeholder="Nhập tên thiết bị..."
            @keyup.enter="handleSearch"
        >

        <label>Tình trạng</label>
        <select v-model="searchParams.status">
          <option value="">(Tất cả)</option>
          <option value="1">Đang mượn</option>
          <option value="0">Đang rảnh</option>
        </select>
      </div>

      <div class="actions">
        <button @click="handleSearch" class="primary">
          {{ loading ? 'Đang tìm...' : 'Tìm kiếm' }}
        </button>
      </div>

      <!-- Kết quả -->
      <div class="result-summary">
        Số thiết bị tìm thấy: <strong>{{ devices.length }}</strong>
      </div>

      <div class="table-wrapper">
        <table class="mockup-table">
          <thead>
            <tr>
              <th style="width: 60px;">STT</th>
              <th>Tên Thiết bị</th>
              <th style="width: 150px;">Trạng thái</th>
              <th style="width: 180px;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="devices.length === 0">
              <td colspan="4" class="empty">Không tìm thấy dữ liệu</td>
            </tr>
            <tr v-for="(device, index) in devices" :key="device.id">
              <td>{{ index + 1 }}</td>
              <td>
                <span class="device-name">{{ device.name }}</span>
              </td>
              
              <!-- Trạng thái -->
              <td>
                <span v-if="device.is_borrowed == 0" class="badge badge-success">Đang rảnh</span>
                <span v-else class="badge badge-warning">Đang mượn</span>
              </td>

              <!-- Action -->
              <td>
                <!-- Chỉ hiện nút khi Đang rảnh -->
                <div v-if="device.is_borrowed == 0" class="action-group">
                  <button @click="handleEdit(device.id)" class="secondary small">Sửa</button>
                  <button @click="handleDelete(device)" class="danger small">Xóa</button>
                </div>
                <span v-else class="muted">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="actions">
        <button class="ghost" @click="router.push('/')">Trở về trang chủ</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap");

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.card {
  width: min(920px, 100%);
  background: #fffdf8;
  border: 1px solid #cdd7e5;
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

.card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
}

.card__header h1 {
  margin: 8px 0 0;
  font-size: 32px;
  letter-spacing: -0.02em;
}

.eyebrow {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 0.14em;
  color: #5b6475;
  margin: 0;
}

/* === FORM GRID (Giống ReturnDevice) === */
.form-grid {
  display: grid;
  grid-template-columns: 160px auto; 
  gap: 16px 20px;
  margin-bottom: 20px;
  align-items: center;
}

.search-grid label {
  font-weight: 600;
  color: #5b6475;
}

/* Fix cứng độ rộng input giống các trang kia */
input, select {
  width: 400px !important;
  max-width: 100% !important;
  box-sizing: border-box;
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  background: #fff;
  transition: border-color 0.2s;
}

input:focus, select:focus {
  outline: none;
  border-color: #2e6db4;
}

/* === BUTTONS === */
.actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

button {
  border: none;
  border-radius: 14px;
  padding: 12px 24px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.2s;
}

button:active {
  transform: translateY(1px);
}

button.primary {
  background: #2e6db4;
  color: white;
  box-shadow: 0 4px 12px rgba(46, 109, 180, 0.2);
}

button.ghost {
  background: #eef2f9;
  color: #2e6db4;
  margin-top: 30px;
}

/* Nút nhỏ trong bảng */
button.small {
  padding: 6px 12px;
  font-size: 13px;
  border-radius: 8px;
}

button.secondary {
  background: #eef2f9;
  color: #2e6db4;
}

button.danger {
  background: #fff1f0;
  color: #cf1322;
}

/* === TABLE === */
.result-summary {
  font-weight: 500;
  margin-bottom: 15px;
  color: #1d2330;
}

.table-wrapper {
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  overflow-x: auto;
}

.mockup-table {
  width: 100%;
  border-collapse: collapse;
}

.mockup-table th,
.mockup-table td {
  border-bottom: 1px solid #ebf0f5;
  padding: 14px 16px;
  text-align: left;
}

.mockup-table th {
  background-color: #f9fafb;
  font-weight: 600;
  color: #5b6475;
  font-size: 14px;
}

.device-name {
  font-weight: 500;
  color: #1d2330;
}

.empty {
  text-align: center;
  color: #999;
  padding: 30px;
}

.muted {
  color: #ccc;
}

.action-group {
  display: flex;
  gap: 8px;
}

/* === BADGES (Trạng thái) === */
.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 99px;
  font-size: 13px;
  font-weight: 600;
}

.badge-success {
  background: #e6ffec;
  color: #1f9d55;
  border: 1px solid #b7ebc9;
}

.badge-warning {
  background: #fff7e6;
  color: #d46b08;
  border: 1px solid #ffd591;
}

/* === RESPONSIVE === */
@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  input, select {
    width: 100% !important;
  }
}
</style>