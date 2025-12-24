<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(true)
const loadError = ref('')
const items = ref([])

const apiBase = import.meta.env.VITE_API_BASE || 'http://localhost:8000'

const hydrateItems = (list) => {
  items.value = (list || []).map((item) => ({
    ...item,
    newPassword: '',
    error: '',
    serverError: '',
    submitting: false,
  }))
}

const fetchList = async () => {
  loading.value = true
  loadError.value = ''
  try {
    const response = await fetch(`${apiBase}/reset_list.php`)
    const payload = await response.json()
    if (!response.ok) {
      throw new Error(payload.error || 'Load failed')
    }
    hydrateItems(payload.items)
  } catch (error) {
    loadError.value = 'Khong tai duoc danh sach.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchList)

const validateRow = (row) => {
  row.error = ''
  if (!row.newPassword) {
    row.error = 'Hay nhap mat khau moi'
    return false
  }
  if (row.newPassword.length < 6) {
    row.error = 'Hay nhap mat khau co toi thieu 6 ky tu'
    return false
  }
  return true
}

const submitRow = async (row) => {
  if (row.submitting) return
  if (!validateRow(row)) return
  row.serverError = ''
  row.submitting = true

  try {
    const response = await fetch(`${apiBase}/reset_password.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ admin_id: row.id, new_password: row.newPassword }),
    })
    const payload = await response.json().catch(() => ({}))

    if (!response.ok) {
      if (payload.fields && payload.fields.new_password) {
        row.error = payload.fields.new_password
      }
      row.serverError = payload.error || 'Reset that bai.'
      row.submitting = false
      return
    }

    items.value = items.value.filter((item) => item.id !== row.id)
  } catch (error) {
    row.serverError = 'Khong the ket noi toi may chu.'
  } finally {
    row.submitting = false
  }
}

const goHome = () => {
  router.push('/')
}
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Password reset</p>
          <h1>Danh sach reset password</h1>
        </div>
        <button type="button" class="ghost" @click="goHome">Trang chu</button>
      </header>

      <div v-if="loading" class="loading">Dang tai du lieu...</div>
      <div v-else-if="loadError" class="error">{{ loadError }}</div>

      <div v-else>
        <p v-if="items.length === 0" class="muted">Khong co yeu cau reset.</p>

        <div v-else class="list">
          <div v-for="(row, index) in items" :key="row.id" class="row">
            <div class="row__meta">
              <div class="row__no">#{{ index + 1 }}</div>
              <div>
                <p class="row__name">{{ row.name || 'Chua ro ten' }}</p>
                <p class="row__login">Login: {{ row.login_id }}</p>
              </div>
            </div>

            <div class="row__input">
              <label :for="`pwd-${row.id}`">Mat khau moi</label>
              <input
                :id="`pwd-${row.id}`"
                v-model="row.newPassword"
                type="password"
                placeholder="Nhap mat khau moi"
              />
              <p v-if="row.error" class="error">{{ row.error }}</p>
              <p v-if="row.serverError" class="error">{{ row.serverError }}</p>
            </div>

            <div class="row__actions">
              <button type="button" class="primary" :disabled="row.submitting" @click="submitRow(row)">
                {{ row.submitting ? 'Dang reset...' : 'Reset' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

:global(body) {
  margin: 0;
  font-family: 'Space Grotesk', system-ui, sans-serif;
  background: radial-gradient(circle at top, #e6f7ff 0%, #f5f0e6 40%) no-repeat,
    linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
  color: #1d2330;
}

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.card {
  width: min(980px, 100%);
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
  margin-bottom: 16px;
}

.card__header h1 {
  margin: 8px 0 0;
  font-size: 30px;
}

.eyebrow {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 0.14em;
  color: #5b6475;
  margin: 0;
}

.loading {
  color: #5b6475;
}

.muted {
  color: #5b6475;
}

.list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 12px;
}

.row {
  border: 1px solid #cdd7e5;
  border-radius: 16px;
  padding: 16px;
  background: #f9fbff;
  display: grid;
  grid-template-columns: 1fr 1.2fr auto;
  gap: 12px 16px;
  align-items: center;
}

.row__meta {
  display: flex;
  gap: 12px;
  align-items: center;
}

.row__no {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #eef2f9;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: #2e6db4;
}

.row__name {
  margin: 0;
  font-weight: 700;
  font-size: 16px;
}

.row__login {
  margin: 2px 0 0;
  color: #5b6475;
  font-size: 14px;
}

.row__input label {
  display: block;
  font-weight: 600;
  color: #5b6475;
  margin-bottom: 6px;
}

.row__input input {
  width: 100%;
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
}

.row__actions {
  display: flex;
  justify-content: flex-end;
}

button {
  border: none;
  border-radius: 14px;
  padding: 12px 20px;
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
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  margin: 6px 0 0;
  color: #b13535;
  font-size: 13px;
}

@media (max-width: 860px) {
  .row {
    grid-template-columns: 1fr;
  }

  .row__actions {
    justify-content: flex-start;
  }
}
</style>