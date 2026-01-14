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
    serverError.value = "Khong the tai du lieu bo loc.";
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
      serverError.value = result.error || "Khong the tai danh sach thiet bi.";
    }
  } catch {
    serverError.value = "Khong the ket noi toi server.";
  } finally {
    loading.value = false;
  }
};

const returnDevice = async (row) => {
  if (returningId.value) return;
  const ok = window.confirm(`Ban co muon tra ${row.device_name}?`);
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
      serverError.value = result.error || "Tra thiet bi that bai.";
      return;
    }
    await search();
  } catch {
    serverError.value = "Khong the ket noi toi server.";
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
          <p class="eyebrow">Return device</p>
          <h1>Tra thiet bi</h1>
        </div>
        <div class="step-pill">Input</div>
      </header>

      <div v-if="loading" class="loading">Dang tai du lieu...</div>
      <div v-else-if="serverError" class="error">{{ serverError }}</div>

      <div v-else>
        <div class="form-grid search-grid">
          <label>Thiet bi</label>
          <input v-model="filters.device_name" type="text" placeholder="Nhap ten thiet bi" />

          <label>Giao vien</label>
          <select v-model="filters.teacher_id">
            <option value="">Chon giao vien</option>
            <option v-for="t in options.teachers" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>

          <label>Lop hoc</label>
          <select v-model="filters.classroom_id">
            <option value="">Chon lop hoc</option>
            <option v-for="c in options.classrooms" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
        </div>

        <div class="actions">
          <button class="primary" @click="search" :disabled="loading">
            {{ loading ? "..." : "Tim kiem" }}
          </button>
        </div>

        <div class="result-summary">
          So thiet bi tim thay: {{ rows.length }}
        </div>

        <div class="table-wrapper">
          <table class="mockup-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Ten thiet bi</th>
                <th>Trang thai</th>
                <th>Action</th>
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
                    {{ returningId === String(row.device_id) ? "..." : "Tra" }}
                  </button>
                  <span v-else class="muted">-</span>
                </td>
              </tr>
              <tr v-if="rows.length === 0">
                <td colspan="4" class="empty">Khong tim thay du lieu</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="actions">
          <button class="ghost" @click="router.push('/')">Tro ve trang chu</button>
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
  padding: 12px 14px;
  font-size: 15px;
  background: #fff;
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

button.primary.small {
  padding: 8px 18px;
  font-size: 14px;
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

.empty {
  text-align: center;
  color: #999;
}

.muted {
  color: #999;
}

.loading {
  padding: 16px 0 8px;
  color: #5b6475;
}

@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
