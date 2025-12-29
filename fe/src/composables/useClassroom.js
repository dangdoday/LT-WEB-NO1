export function useClassroom() {
  const apiBase = import.meta.env.VITE_API_BASE || 'http://localhost:8000'

  async function createClassroom(formData) {
    console.log('Creating classroom with data:', formData)
    const res = await fetch(`${apiBase}/classrooms`, {
      method: 'POST',
      body: formData,
    })
    const payload = await res.json().catch(() => ({}))
    return { ok: res.ok, status: res.status, payload }
  }

  async function getClassroom(id) {
    const res = await fetch(`${apiBase}/classrooms/show?id=${id}`)
    const payload = await res.json().catch(() => ({}))
    return { ok: res.ok, status: res.status, payload }
  }

  async function updateClassroom(id, formData) {
    const res = await fetch(`${apiBase}/classrooms/update?id=${id}`, {
      method: 'POST',
      body: formData,
    })
    const payload = await res.json().catch(() => ({}))
    return { ok: res.ok, status: res.status, payload }
  }

  return { createClassroom, getClassroom, updateClassroom }
}
