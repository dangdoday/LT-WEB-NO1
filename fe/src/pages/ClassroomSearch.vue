<template>
  <div class="search-page">
    <h1>Tìm kiếm phòng học</h1>

    <div class="search-box">
      <div class="form-row">
        <label>Tòa nhà:</label>
        <select v-model="building">
          <option value="">-- Tất cả --</option>
          <option v-for="b in buildingOptions" :key="b" :value="b">
            {{ b }}
          </option>
        </select>
      </div>

      <div class="form-row">
        <label>Từ khóa:</label>
        <input
          type="text"
          v-model="keyword"
          placeholder="Nhập tên phòng hoặc mô tả..."
        />
      </div>

      <button class="btn-search" @click="fetchData">Tìm kiếm</button>
    </div>

    <div class="result-info">
      Số phòng học tìm thấy: <strong>{{ classrooms.length }}</strong>
    </div>

    <table border="1" class="result-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Tên phòng học</th>
          <th>Tòa nhà</th>
          <th>Mô tả chi tiết</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(room, index) in classrooms" :key="room.id">
          <td style="text-align: center">{{ index + 1 }}</td>
          <td>{{ room.name }}</td>
          <td>{{ room.building }}</td>
          <td>{{ room.description }}</td>
          <td style="text-align: center">
            <button class="btn-delete" @click="deleteRoom(room.id, room.name)">
              Xóa
            </button>
            <button class="btn-edit">Sửa</button>
          </td>
        </tr>
        <tr v-if="classrooms.length === 0">
          <td colspan="5" style="text-align: center">Không tìm thấy dữ liệu</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// --- THAY ĐỔI QUAN TRỌNG: Import từ file chung ---
// Đảm bảo bạn đã tạo file src/constants/options.js như hướng dẫn trước
import { BUILDINGS } from "@/constants/options.js";

// Khai báo biến
const classrooms = ref([]);

// Thay vì gọi API, ta gán luôn danh sách chung vào đây
const buildingOptions = ref(BUILDINGS);

const building = ref("");
const keyword = ref("");

// --- ĐÃ XÓA hàm fetchBuildings() vì không cần nữa ---

// HÀM: Lấy dữ liệu tìm kiếm (Vẫn giữ nguyên để lấy danh sách phòng)
const fetchData = async () => {
  try {
    const response = await fetch(
      `http://localhost:8000/classroom.php?building=${building.value}&keyword=${keyword.value}`
    );
    const data = await response.json();
    classrooms.value = data;
  } catch (error) {
    console.error("Lỗi:", error);
    alert("Không kết nối được với Server. Hãy kiểm tra lại Backend!");
  }
};

// HÀM: Xóa phòng học (Giữ nguyên)
const deleteRoom = async (id, name) => {
  if (!confirm(`Bạn chắc chắn muốn xóa phòng học: ${name}?`)) {
    return;
  }

  try {
    const response = await fetch(
      `http://localhost:8000/classroom.php?id=${id}`,
      {
        method: "DELETE",
      }
    );

    if (!response.ok) {
      throw new Error("Lỗi Server");
    }

    try {
      const result = await response.json();
      if (result.success) {
        alert("Đã xóa thành công!");
        fetchData();
      } else {
        alert("Lỗi xóa: " + (result.error || "Không rõ nguyên nhân"));
      }
    } catch (e) {
      fetchData();
    }
  } catch (error) {
    alert("Lỗi kết nối khi xóa");
    console.error(error);
  }
};

// Tự động chạy khi vào trang
onMounted(() => {
  // Không cần gọi fetchBuildings() nữa
  fetchData(); // Chỉ cần lấy danh sách phòng học thôi
});
</script>

<style scoped>
/* CSS giữ nguyên như cũ */
.search-page {
  padding: 20px;
  font-family: Arial, sans-serif;
}
.search-box {
  background: #f9f9f9;
  padding: 15px;
  margin-bottom: 20px;
  border: 1px solid #ddd;
  width: 500px;
}
.form-row {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
}
.form-row label {
  width: 100px;
  font-weight: bold;
}
.form-row input,
.form-row select {
  flex: 1;
  padding: 5px;
}

.btn-search {
  background: #4a90e2;
  color: white;
  padding: 8px 15px;
  border: none;
  cursor: pointer;
  margin-left: 100px;
}
.btn-delete {
  background: #d9534f;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
  margin-right: 5px;
}
.btn-edit {
  background: #0275d8;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
}

.result-info {
  margin-bottom: 10px;
  font-style: italic;
}
.result-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.result-table th {
  background: #eee;
  padding: 10px;
  text-align: left;
}
.result-table td {
  padding: 8px;
  border: 1px solid #ccc;
}
</style>