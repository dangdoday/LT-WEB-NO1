<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const rows = ref([]);
const serverError = ref('');

const apiBase = import.meta.env.VITE_API_BASE || "http://localhost:8000";

const filters = reactive({
  device_name: "",
  teacher_id: "",
});

const options = reactive({
  teachers: []
});

const formatDateTime = (value) => {
  if (!value) return '';

  let datePart, timePart;

  if (value.includes('T')) {
    [datePart, timePart] = value.split('T');
  }
  else {
    [datePart, timePart] = value.split(' ');
  }

  const [year, month, day] = datePart.split('-');
  const [hour, minute] = timePart.split(':');

  return `${hour}:${minute} ${parseInt(day)}/${parseInt(month)}/${year}`;
};


const loadFilters = async () => {
  try {
    const res = await fetch(`${apiBase}/transaction_filters.php`);
    const data = await res.json();
    options.teachers = data.teachers || [];
  } catch (e) {
    console.error("Lỗi tải bộ lọc");
  }
};

const search = async () => {
  loading.value = true;
  serverError.value = '';
  try {
    const query = new URLSearchParams(filters).toString();
    const res = await fetch(`${apiBase}/transactions.php?${query}`);
    const result = await res.json();
    if (result.status === "success") {
      rows.value = result.data || [];
    }
  } catch (e) {
    serverError.value = "Không thể kết nối tới server";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadFilters();
  search();
});
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Transaction</p>
          <h1>Lịch sử mượn thiết bị</h1>
        </div>
        <div class="step-pill">Input</div>
      </header>

      <div v-if="loading" class="loading">Đang tải dữ liệu...</div>
      <div v-else-if="serverError" class="error">{{ serverError }}</div>

      <div v-else>
        <div class="form-grid search-grid">
          <label>Thiết bị</label>
          <input v-model="filters.device_name" type="text" placeholder="Nhập tên thiết bị" />

          <label>Giáo viên</label>
          <select v-model="filters.teacher_id">
            <option value="">Chọn giáo viên</option>
            <option v-for="t in options.teachers" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>
        </div>

        <div class="actions">
          <button class="primary" @click="search" :disabled="loading">
            {{ loading ? '...' : 'Tìm kiếm' }}
          </button>
        </div>

        <div class="result-summary">
          Số lần thiết bị tìm thấy: {{ rows.length }}
        </div>

        <div class="table-wrapper">
          <table class="mockup-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Tên Thiết bị</th>
                <th>Thời gian dự kiến mượn</th>
                <th>Thời điểm trả</th>
                <th>Giáo viên mượn</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in rows" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ row.device_name }}</td>
                <td>
                  {{ formatDateTime(row.start_transaction_plan) }} ~
                  {{ formatDateTime(row.end_transaction_plan) }}
                </td>
                <td>
                  {{ row.actual_return_time ? formatDateTime(row.actual_return_time) : 'Chưa trả' }}
                </td>
                <td>{{ row.teacher_name }}</td>
              </tr>
              <tr v-if="rows.length === 0">
                <td colspan="5" class="empty">Không tìm thấy dữ liệu</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="actions">
          <button class="ghost" @click="router.push('/')">Trở về trang chủ</button>
        </div>
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

.step-pill {
  padding: 8px 16px;
  border-radius: 999px;
  background: rgba(46, 109, 180, 0.12);
  color: #2e6db4;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.1em;
}

.form-grid {
  display: grid;
  grid-template-columns: 160px 1fr;
  gap: 16px 20px;
  margin-bottom: 20px;
}

.search-grid label {
  font-weight: 600;
  color: #5b6475;
  align-self: center;
}

input,
select {
  width: 100%;
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  padding: 12px 16px;
  /* chỉnh để input bằng select */
  font-size: 15px;
  background: #fff;
  box-sizing: border-box;
}

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
}

button.primary {
  background: #2e6db4;
  color: white;
  box-shadow: 0 10px 20px rgba(46, 109, 180, 0.2);
}

button.ghost {
  background: #eef2f9;
  color: #2e6db4;
  margin-top: 30px;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.result-summary {
  font-weight: 500;
  margin-bottom: 15px;
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
  border: 1px solid #cdd7e5;
  padding: 12px;
  text-align: left;
}

.mockup-table th {
  background-color: #fff;
  font-weight: 600;
}

.mockup-table th:first-child,
.mockup-table td:first-child {
  text-align: center;
  width: 60px;
}

.empty {
  text-align: center;
  color: #999;
}

.loading {
  padding: 16px 0 8px;
  color: #5b6475;
}
</style>
