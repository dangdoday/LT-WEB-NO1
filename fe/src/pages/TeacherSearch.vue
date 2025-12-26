<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const teachers = ref([])
const SPECIALIZATION_MAP = {
  '001': 'Khoa học máy tính',
  '002': 'Khoa học dữ liệu',
  '003': 'Hải dương học',
}
const DEGREE_MAP = {
  '001': 'Cử nhân',
  '002': 'Thạc sĩ',
  '003': 'Tiến sĩ',
  '004': 'Phó giáo sư',
  '005': 'Giáo sư',
}
const specializations = ref(Object.keys(SPECIALIZATION_MAP))
const loading = ref(false)
const hasSearched = ref(false)
const searchParams = reactive({
  specialized: '',
  keyword: ''
})

const API_BASE = 'http://localhost:8000'



const fetchTeachers = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (searchParams.specialized) {
      params.append('specialized', searchParams.specialized)
    }
    if (searchParams.keyword) {
      let keyword = searchParams.keyword.trim().toLowerCase();
      let matchedCode = null;
      // Nếu keyword là tên chuyên ngành
      for (const [code, name] of Object.entries(SPECIALIZATION_MAP)) {
        if (name.toLowerCase() === keyword) {
          matchedCode = code;
          break;
        }
      }
      // Nếu keyword là tên học vị
      if (!matchedCode) {
        for (const [code, name] of Object.entries(DEGREE_MAP)) {
          if (name.toLowerCase() === keyword) {
            matchedCode = code;
            break;
          }
        }
      }
      // Nếu tìm thấy mã, gửi mã để LIKE trên trường degree hoặc specialized
      if (matchedCode) {
        params.append('keyword', matchedCode);
      } else {
        params.append('keyword', keyword);
      }
    }
    const url = params.toString() 
      ? `${API_BASE}/teachers/search?${params.toString()}`
      : `${API_BASE}/teachers`
    const response = await fetch(url)
    if (response.ok) {
      const data = await response.json();
      teachers.value = Array.isArray(data) ? data : [];
    }
  } catch (error) {
    console.error('Error fetching teachers:', error)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  hasSearched.value = true
  fetchTeachers()
}

const handleDelete = async (teacher) => {
  const confirmed = confirm(`Bạn chắc chắn muốn xóa giáo viên ${teacher.name}?`)
  if (!confirmed) return

  try {
    const response = await fetch(`${API_BASE}/teachers?id=${teacher.id}`, {
      method: 'DELETE'
    })
    if (response.ok) {
      alert('Xóa thành công!')
      fetchTeachers()
    } else {
      let message = 'Xóa thất bại!';
      try {
        const data = await response.json();
        if (data && data.error) message = data.error;
      } catch (e) {}
      alert(message);
    }
  } catch (error) {
    console.error('Error deleting teacher:', error)
    alert('Xóa thất bại!')
  }
}

const handleEdit = (teacher) => {
  // TODO: Implement edit page
  alert('Chức năng sửa chưa được triển khai')
}


</script>

<template>
  <div class="teacher-search">
    <div class="container">
      <h1>Tìm kiếm giáo viên</h1>
      
      <!-- Form search -->
      <div class="search-form">
        <div class="form-group">
          <label>Chuyên ngành</label>
          <select v-model="searchParams.specialized">
            <option value="">-- Tất cả --</option>
            <option v-for="spec in specializations" :key="spec" :value="spec">
              {{ SPECIALIZATION_MAP[spec] }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Từ khóa</label>
          <input 
            type="text" 
            v-model="searchParams.keyword" 
            placeholder="Nhập tên, mô tả hoặc bằng cấp..."
          />
        </div>

        <div class="form-actions">
          <button @click="handleSearch" class="btn-search">Tìm kiếm</button>
        </div>
      </div>

      <!-- Results -->
      <div v-if="hasSearched" class="results">
        <div class="results-header">
          <p>Số giáo viên tìm thấy: <strong>{{ teachers.length }}</strong></p>
        </div>

        <div v-if="loading" class="loading">Đang tải...</div>

        <table v-else-if="teachers.length > 0" class="results-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Tên giáo viên</th>
              <th>Khoa</th>
              <th>Mô tả chi tiết</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(teacher, index) in teachers" :key="teacher.id">
              <td>{{ index + 1 }}</td>
              <td>{{ teacher.name }}</td>
              <td>{{ SPECIALIZATION_MAP[teacher.specialized] || teacher.specialized }}</td>
              <td>{{ teacher.description }}</td>
              <td class="actions">
                <button @click="handleEdit(teacher)" class="btn-edit">Sửa</button>
                <button @click="handleDelete(teacher)" class="btn-delete">Xóa</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="no-results">
          Không tìm thấy giáo viên nào.
        </div>
      </div>

      <div class="back-home">
        <RouterLink to="/" class="btn-back">Quay về trang chủ</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.teacher-search {
  min-height: 100vh;
  padding: 40px 16px;
  background: linear-gradient(135deg, #f6f1e8 0%, #dbe4f2 100%);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  background: #ffffff;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h1 {
  margin: 0 0 24px;
  color: #333;
  text-align: center;
}

.search-form {
  background: #f9fafb;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 32px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

.form-group label {
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
}

.form-group input,
.form-group select {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2e6db4;
}

.form-actions {
  text-align: center;
  margin-top: 8px;
}

.btn-search {
  padding: 12px 32px;
  background: #2e6db4;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-search:hover {
  background: #1e4d8b;
}

.results-header {
  margin-bottom: 16px;
}

.results-header p {
  font-size: 16px;
  color: #555;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #999;
}

.results-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 24px;
}


.results-table th,
.results-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

/* Center the Action column header and cells */
.results-table th:last-child,
.results-table td.actions {
  text-align: center;
}

.results-table th {
  background: #f3f4f6;
  font-weight: 600;
  color: #374151;
}

.results-table tbody tr:hover {
  background: #f9fafb;
}

.actions {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn-edit,
.btn-delete {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-edit {
  background: #10b981;
  color: white;
}

.btn-edit:hover {
  background: #059669;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
}

.no-results {
  text-align: center;
  padding: 40px;
  color: #999;
}

.back-home {
  text-align: center;
  margin-top: 24px;
}

.btn-back {
  display: inline-block;
  padding: 10px 24px;
  background: #6b7280;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: background 0.3s;
}

.btn-back:hover {
  background: #4b5563;
}
</style>
