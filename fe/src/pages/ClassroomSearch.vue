<template>
  <div class="search-page">
    <div class="nav-header">
      <router-link to="/" class="btn-back"> ← Quay lại</router-link>
    </div>

    <h1>Tìm kiếm phòng học</h1>

    <form class="search-box" @submit.prevent="fetchData">
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

      <button type="submit" class="btn-search">Tìm kiếm</button>
    </form>

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
            <button class="btn-edit" @click="goToEdit(room.id)">Sửa</button>
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

import { BUILDINGS } from "@/constants/options.js";

// Khai báo biến
const classrooms = ref([]);

const buildingOptions = ref(BUILDINGS);

const building = ref("");
const keyword = ref("");

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

import { useRouter } from "vue-router";
const router = useRouter();

const goToEdit = (id) => {
  router.push(`/classroom/edit/${id}`);
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.nav-header {
  margin-bottom: 15px;
}

.btn-back {
  text-decoration: none;
  color: #666;
  font-size: 14px;
  font-weight: bold;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background: #fff;
  transition: all 0.3s;
}

.btn-back:hover {
  background: #f0f0f0;
  color: #333;
  border-color: #999;
}

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