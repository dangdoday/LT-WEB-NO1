<template>
  <div class="page-container">
    <h1 class="page-title">Tìm kiếm thiết bị</h1>

    <div class="content-wrapper">
      <!-- 1.1 Form Search -->
      <div class="card search-card">
        <div class="form-group-row">
          <!-- Từ khóa -->
          <div class="form-group">
            <label>Từ khóa</label>
            <input
              type="text"
              v-model="searchParams.keyword"
              class="form-control"
              placeholder="Nhập tên thiết bị..."
            />
          </div>

          <!-- Tình trạng -->
          <div class="form-group">
            <label>Tình trạng</label>
            <select v-model="searchParams.status" class="form-control">
              <option value="">(Tất cả)</option>
              <option value="1">Đang mượn</option>
              <option value="0">Đang rảnh</option>
            </select>
          </div>

          <!-- Nút Tìm kiếm -->
          <div class="form-group button-group">
            <button @click="handleSearch" class="btn btn-primary">
              Tìm kiếm
            </button>
          </div>
        </div>
      </div>

      <!-- 1.2 Kết quả tìm kiếm -->
      <div class="card result-card">
        <div class="result-header">
          Số thiết bị tìm thấy: <strong>{{ devices.length }}</strong>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th style="width: 50px; text-align: center">No</th>
              <th>Tên Thiết bị</th>
              <th style="width: 150px; text-align: center">Trạng thái</th>
              <th style="width: 200px; text-align: center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="devices.length === 0">
              <td colspan="4" class="text-center">Không tìm thấy dữ liệu</td>
            </tr>
            <tr v-for="(device, index) in devices" :key="device.id">
              <td class="text-center">{{ index + 1 }}</td>
              <td>{{ device.name }}</td>

              <!-- Trạng thái -->
              <td class="text-center">
                <span v-if="device.is_borrowed == 0" class="badge badge-success"
                  >Đang rảnh</span
                >
                <span v-else class="badge badge-warning">Đang mượn</span>
              </td>

              <!-- Action -->
              <td class="text-center action-buttons">
                <!-- Chỉ hiện khi Đang rảnh -->
                <template v-if="device.is_borrowed == 0">
                  <button
                    @click="handleEdit(device.id)"
                    class="btn btn-sm btn-edit"
                  >
                    Sửa
                  </button>
                  <button
                    @click="handleDelete(device)"
                    class="btn btn-sm btn-delete"
                  >
                    Xóa
                  </button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const devices = ref([]);
const searchParams = ref({
  keyword: "",
  status: "", // Mặc định là rỗng (Tất cả)
});

// Hàm gọi API
const fetchDevices = async () => {
  try {
    // Tạo Query String: ?keyword=abc&status=1
    const params = new URLSearchParams(searchParams.value).toString();
    const res = await fetch(`/api/device_search.php?${params}`);
    const data = await res.json();

    if (data.status === "success") {
      devices.value = data.data;
    } else {
      alert(data.message || "Lỗi lấy dữ liệu");
    }
  } catch (error) {
    console.error(error);
  }
};

// 2.0 Action Tìm kiếm
const handleSearch = () => {
  fetchDevices();
};

// 2.1 Action Xóa
const handleDelete = async (device) => {
  // Popup confirm theo yêu cầu
  const confirmMsg = `Bạn chắc chắn muốn xóa thiết bị "${device.name}" ?`;
  if (!confirm(confirmMsg)) return;

  try {
    const res = await fetch("/api/device_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: device.id }),
    });
    const data = await res.json();

    if (data.status === "success") {
      alert("Xóa thành công!");
      fetchDevices(); // Refresh màn hình
    } else {
      alert(data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối server");
  }
};

// 2.2 Action Sửa
const handleEdit = (id) => {
  // Chuyển hướng sang trang sửa (Giả sử bạn dùng lại trang register hoặc trang edit riêng)
  // Logic: Chuyển sang màn hình edit, truyền ID lên URL
  router.push({ name: "device-register", query: { id: id } });
};

// 0) Initial display
onMounted(() => {
  fetchDevices();
});
</script>

<style scoped>
/* CSS phỏng theo giao diện trong ảnh */
.page-container {
  padding: 20px;
  background-color: #f0f2f5;
  min-height: 100vh;
  font-family: Arial, sans-serif;
}

.page-title {
  text-align: center;
  color: #3b5998; /* Màu xanh Facebook/Zalo giống ảnh */
  font-weight: bold;
  margin-bottom: 20px;
}

.content-wrapper {
  max-width: 1000px;
  margin: 0 auto;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group-row {
  display: flex;
  gap: 20px;
  align-items: flex-end;
}

.form-group {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.button-group {
  flex: 0 0 auto;
}

label {
  font-weight: bold;
  margin-bottom: 5px;
  font-size: 14px;
}

.form-control {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

.btn {
  padding: 8px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  color: white;
}

.btn-primary {
  background-color: #3578e5;
}
.btn-primary:hover {
  background-color: #2a65c7;
}

.result-header {
  margin-bottom: 10px;
  font-size: 14px;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.table th,
.table td {
  border: 1px solid #dee2e6;
  padding: 10px;
  vertical-align: middle;
}

.table th {
  background-color: #f8f9fa;
  font-weight: bold;
}

.text-center {
  text-align: center;
}

/* Trạng thái */
.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: white;
}
.badge-success {
  background-color: #28a745;
} /* Màu xanh lá */
.badge-warning {
  background-color: #ffc107;
  color: black;
} /* Màu vàng */

/* Buttons nhỏ */
.btn-sm {
  padding: 4px 10px;
  font-size: 12px;
  margin: 0 2px;
}
.btn-edit {
  background-color: #17a2b8;
} /* Màu xanh dương nhạt */
.btn-delete {
  background-color: #dc3545;
} /* Màu đỏ */
</style>
