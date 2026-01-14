<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const loading = ref(false);
const rows = ref([]);
const serverError = ref("");
const returningId = ref("");

const apiBase = import.meta.env.VITE_API_BASE || "http://localhost:8000";

const filters = reactive({
  device_name: "",
  teacher_id: "",
  classroom_id: "",
});

const options = reactive({
  teachers: [],
  classrooms: [],
});

const loadFilters = async () => {
  try {
    const res = await fetch(`${apiBase}/return_filters.php`);
    const data = await res.json();
    options.teachers = data.teachers || [];
    options.classrooms = data.classrooms || [];
  } catch {
    serverError.value = "Không thể tải dữ liệu bộ lọc.";
  }
};

const search = async () => {
  loading.value = true;
  serverError.value = "";
  try {
    const query = new URLSearchParams(filters).toString();
    const res = await fetch(`${apiBase}/return_device_list.php?${query}`);
    const result = await res.json();
    if (result.status === "success") {
      rows.value = result.data || [];
    } else {
      serverError.value = result.error || "Không thể tải danh sách thiết bị.";
    }
  } catch {
    serverError.value = "Không thể kết nối tới server.";
  } finally {
    loading.value = false;
  }
};

const returnDevice = async (row) => {
  if (returningId.value) return;
  const ok = window.confirm(`Bạn có muốn trả ${row.device_name}?`);
  if (!ok) return;

  returningId.value = String(row.device_id);
  serverError.value = "";
  try {
    const res = await fetch(`${apiBase}/return_device.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ device_id: row.device_id }),
    });
    const result = await res.json().catch(() => ({}));
    if (!res.ok || result.error) {
      serverError.value = result.error || "Trả thiết bị thất bại.";
      return;
    }
    await search();
  } catch {
    serverError.value = "Không thể kết nối tới server.";
  } finally {
    returningId.value = "";
  }
};

onMounted(async () => {
  await loadFilters();
  await search();
});
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <!-- Bỏ class eyebrow để giống header bên Teacher -->
          <h1>Trả thiết bị</h1>
        </div>
      </header>

      <div v-if="loading" class="loading">Đang tải dữ liệu...</div>
      <div v-else-if="serverError" class="error">{{ serverError }}</div>

      <div v-else>
        <!-- Form tìm kiếm -->
        <div class="form-grid">
          <label>Thiết bị</label>
          <!-- Bỏ style max-width để nó tự giãn theo grid -->
          <input 
            v-model="filters.device_name" 
            type="text" 
            placeholder="Nhập tên thiết bị" 
          />

          <label>Giáo viên</label>
          <select v-model="filters.teacher_id">
            <option value="">Chọn giáo viên</option>
            <option v-for="t in options.teachers" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>

          <label>Lớp học</label>
          <select v-model="filters.classroom_id">
            <option value="">Chọn lớp học</option>
            <option v-for="c in options.classrooms" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <div class="actions">
          <button class="primary" @click="search" :disabled="loading">
            {{ loading ? "..." : "Tìm kiếm" }}
          </button>
        </div>

        <div class="result-summary">
          Số thiết bị tìm thấy: {{ rows.length }}
        </div>

        <div class="table-wrapper">
          <table class="mockup-table">
            <thead>
              <tr>
                <th>STT</th>
                <th>Tên thiết bị</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in rows" :key="row.device_id">
                <td>{{ index + 1 }}</td>
                <td>{{ row.device_name }}</td>
                <td>{{ row.status_label }}</td>
                <td>
                  <button
                    v-if="row.can_return"
                    class="primary small"
                    :disabled="returningId === String(row.device_id)"
                    @click="returnDevice(row)"
                  >
                    {{ returningId === String(row.device_id) ? "..." : "Trả" }}
                  </button>
                  <span v-else class="muted">-</span>
                </td>
              </tr>
              <tr v-if="rows.length === 0">
                <td colspan="4" class="empty">Không tìm thấy dữ liệu</td>
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
/* 1. Dùng Font giống Teacher Register */
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

/* 2. Định nghĩa biến màu sắc giống hệt */
:global(:root) {
  --paper: #f5f0e6;
  --ink: #1d2330;
  --accent: #2e6db4;
  --accent-2: #e0a42c;
  --line: #cdd7e5;
  --muted: #5b6475;
  --shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

/* 3. Background Gradient toàn trang */
:global(body) {
  margin: 0;
  font-family: 'Space Grotesk', system-ui, sans-serif;
  background: radial-gradient(circle at top, #f4d9b5 0%, #f5f0e6 40%) no-repeat,
  linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
  color: var(--ink);
}

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.card {
  width: min(880px, 100%); /* Giống Teacher Register */
  background: #fffdf8;
  border: 1px solid var(--line);
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: var(--shadow);
  animation: rise 0.6s ease;
}

.card__header {
  margin-bottom: 32px;
}

.card__header h1 {
  margin: 0;
  font-size: 32px;
  letter-spacing: -0.02em;
}

/* GRID LAYOUT: Đây là chỗ quyết định độ dài input */
.form-grid {
  display: grid;
  grid-template-columns: 160px 1fr; /* 1fr sẽ tự giãn hết khoảng trống còn lại */
  gap: 20px 24px;
  max-width: 100%;
  margin-bottom: 24px;
}

label {
  font-weight: 600;
  color: var(--muted);
  align-self: center;
}

input,
select {
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
}

.actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 20px;
  align-items: center;
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
  background: var(--accent);
  color: white;
  box-shadow: 0 10px 20px rgba(46, 109, 180, 0.2);
}

button.primary.small {
  padding: 8px 18px;
  font-size: 14px;
}

button.ghost {
  background: #eef2f9;
  color: var(--accent);
  margin-top: 20px;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Table Style cho khớp theme */
.result-summary {
  font-weight: 600;
  margin-bottom: 15px;
  color: var(--ink);
}

.table-wrapper {
  border: 1px solid var(--line);
  border-radius: 16px;
  overflow-x: auto;
  background: #fff;
}

.mockup-table {
  width: 100%;
  border-collapse: collapse;
}

.mockup-table th,
.mockup-table td {
  border-bottom: 1px solid var(--line);
  padding: 14px 16px;
  text-align: left;
}

.mockup-table th {
  background-color: #f9fafb;
  font-weight: 600;
  color: var(--muted);
}

.empty {
  text-align: center;
  color: #999;
  padding: 30px;
}

.muted {
  color: #ccc;
}

.loading {
  padding: 16px 0 8px;
  color: var(--muted);
}

.error {
  color: #b13535;
  margin-bottom: 16px;
}

@keyframes rise {
  from {
    transform: translateY(18px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .card {
    padding: 24px;
  }
}
</style>