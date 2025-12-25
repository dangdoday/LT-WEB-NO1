<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Device Advance Search</p>
          <h1>Tìm kiếm nâng cao thiết bị</h1>
        </div>
      </header>

      <div class="search-form">
        <div class="form-grid">
          <label for="keyword">Từ khóa</label>
          <input id="keyword" v-model="filters.keyword" type="text" placeholder="Nhập tên hoặc mô tả thiết bị" @keyup.enter="handleSearch" />

          <label for="status">Tình trạng</label>
          <select id="status" v-model="filters.status">
            <option value="">Tất cả</option>
            <option value="Đang rảnh">Đang rảnh</option>
            <option value="Đang mượn">Đang mượn</option>
          </select>
        </div>
        <div class="actions">
          <button class="primary" @click="handleSearch">Tìm kiếm</button>
          <button class="ghost" @click="router.push('/')">Trở về trang chủ</button>
        </div>
      </div>

      <div class="results-container">
        <div class="results-info">
          Số thiết bị tìm thấy: <span class="count">{{ devices.length }}</span>
        </div>

        <table class="results-table">
          <thead>
            <tr>
              <th width="50">No</th>
              <th>Tên Thiết bị</th>
              <th width="150">Trạng thái</th>
              <th width="100">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(device, index) in sortedDevices" :key="device.id">
              <td>{{ index + 1 }}</td>
              <td>{{ device.name }}</td>
              <td>
                <span :class="['status-badge', device.status === 'Đang mượn' ? 'status--busy' : 'status--free']">
                  {{ device.status }}
                </span>
              </td>
              <td class="action-cell">
                <button v-if="device.status === 'Đang rảnh'" class="btn-borrow" @click="goToBorrow(device.id)">
                  mượn
                </button>
              </td>
            </tr>
            <tr v-if="devices.length === 0">
              <td colspan="4" class="no-results">Không tìm thấy thiết bị nào.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const apiBase = import.meta.env.VITE_API_BASE || "http://localhost:8000";

const filters = ref({
  keyword: '',
  status: ''
});
const devices = ref([]);

const sortedDevices = computed(() => {
  // Although backend already sorts by ID DESC, we ensure it here if needed or just return
  return devices.value;
});

const fetchDevices = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.keyword) params.append('keyword', filters.value.keyword);
    if (filters.value.status) params.append('status', filters.value.status);
    
    const response = await fetch(`${apiBase}/devices/advance-search?${params.toString()}`);
    const data = await response.json();
    devices.value = data;
  } catch (error) {
    console.error('Error fetching devices:', error);
  }
};

const handleSearch = () => {
  fetchDevices();
};

const goToBorrow = (deviceId) => {
  router.push({ name: 'borrow-device', query: { device_id: deviceId } });
};

onMounted(() => {
  fetchDevices();
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap");

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: radial-gradient(circle at top, #f0e3cf 0%, #f5f0e6 45%) no-repeat,
    linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
  font-family: "Manrope", system-ui, sans-serif;
}

.card {
  width: min(1000px, 100%);
  background: #fffdf8;
  border: 1px solid #cdd7e5;
  border-radius: 24px;
  padding: 32px;
  box-shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

.card__header {
  margin-bottom: 32px;
}

.card__header h1 {
  margin: 8px 0 0;
  font-size: 32px;
  color: #1d2330;
}

.eyebrow {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 0.14em;
  color: #5b6475;
  margin: 0;
}

.search-form {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 16px;
  padding: 24px;
  margin: 0 auto 32px;
  max-width: 600px;
}

.form-grid {
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 16px 20px;
  align-items: center;
}

label {
  font-weight: 600;
  color: #5b6475;
  text-align: right;
}

input, select {
  padding: 10px 14px;
  border: 1px solid #cdd7e5;
  border-radius: 8px;
  font-size: 15px;
  width: 100%;
  background: white;
}

.actions {
  margin-top: 24px;
  display: flex;
  justify-content: center;
  gap: 12px;
}

button.primary {
  padding: 10px 32px;
  background: #2e6db4;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(46, 109, 180, 0.2);
  transition: all 0.2s;
}

button.ghost {
  padding: 10px 24px;
  background: #eef2f9;
  color: #2e6db4;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

button:hover {
  transform: translateY(-1px);
  filter: brightness(0.95);
}

.results-info {
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: 500;
}

.count {
  font-weight: 700;
  color: #2e6db4;
}

.results-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border: 1px solid #dee2e6;
  border-radius: 12px;
  overflow: hidden;
}

.results-table th {
  background: #f1f4f8;
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #5b6475;
  border-bottom: 1px solid #dee2e6;
}

.results-table td {
  padding: 16px;
  border-bottom: 1px solid #dee2e6;
  color: #1d2330;
}

.results-table tr:last-child td {
  border-bottom: none;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
}

.status--free {
  background: #e8f5e9;
  color: #2e7d32;
}

.status--busy {
  background: #ffebee;
  color: #c62828;
}

.btn-borrow {
  padding: 6px 16px;
  background: #6a9bd4;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.btn-borrow:hover {
  background: #5084bc;
}

.no-results {
  text-align: center;
  padding: 32px !important;
  color: #5b6475;
  font-style: italic;
}

.action-cell {
  text-align: center;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
