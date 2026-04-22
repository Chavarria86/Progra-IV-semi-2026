<template>
  <div class="card shadow border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-success"><i class="bi bi-person-badge-fill me-2"></i>Gestión de Supervisores</h4>
      <button class="btn btn-success rounded-pill" @click="openModal()">
        + Nuevo Supervisor
      </button>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" class="form-control rounded-pill" placeholder="Buscar por nombre o departamento..." v-model="search" @input="fetchSupervisores" />
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Departamento / Facultad</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="supervisor in supervisores.data" :key="supervisor.id">
              <td>{{ supervisor.id }}</td>
              <td><strong>{{ supervisor.nombre }}</strong></td>
              <td><span class="badge bg-info text-dark">{{ supervisor.departamento }}</span></td>
              <td>{{ supervisor.email }}</td>
              <td>{{ supervisor.telefono }}</td>
              <td>
                <button class="btn btn-sm btn-outline-success rounded-circle me-1" @click="openModal(supervisor)">
                  ✎
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" @click="deleteSupervisor(supervisor.id)">
                  🗑
                </button>
              </td>
            </tr>
            <tr v-if="supervisores.data && supervisores.data.length === 0">
              <td colspan="6" class="text-center text-muted py-4">No se encontraron supervisores.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination (simplified) -->
      <div class="d-flex justify-content-between align-items-center mt-3" v-if="supervisores.total > 0">
        <span class="text-muted">Mostrando página {{ supervisores.current_page }} de {{ supervisores.last_page }}</span>
        <div>
          <button class="btn btn-sm btn-outline-secondary me-1" :disabled="!supervisores.prev_page_url" @click="fetchSupervisores(supervisores.current_page - 1)">Anterior</button>
          <button class="btn btn-sm btn-outline-secondary" :disabled="!supervisores.next_page_url" @click="fetchSupervisores(supervisores.current_page + 1)">Siguiente</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="supervisorModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-light">
            <h5 class="modal-title">{{ editMode ? 'Editar Supervisor' : 'Nuevo Supervisor' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveSupervisor">
              <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" v-model="form.nombre" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Departamento / Facultad</label>
                <input type="text" class="form-control" v-model="form.departamento" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" v-model="form.email" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" v-model="form.telefono">
              </div>
              <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import * as bootstrap from 'bootstrap';

export default {
  data() {
    return {
      supervisores: {},
      search: '',
      form: { id: '', nombre: '', departamento: '', email: '', telefono: '' },
      editMode: false,
      modalInstance: null
    };
  },
  mounted() {
    this.fetchSupervisores();
    this.modalInstance = new bootstrap.Modal(document.getElementById('supervisorModal'));
  },
  methods: {
    fetchSupervisores(page = 1) {
      axios.get(`/api/supervisores?page=${page}&search=${this.search}`)
        .then(response => {
          this.supervisores = response.data;
        })
        .catch(error => console.error(error));
    },
    openModal(supervisor = null) {
      if (supervisor) {
        this.editMode = true;
        this.form = { ...supervisor };
      } else {
        this.editMode = false;
        this.form = { id: '', nombre: '', departamento: '', email: '', telefono: '' };
      }
      this.modalInstance.show();
    },
    saveSupervisor() {
      const request = this.editMode 
        ? axios.put(`/api/supervisores/${this.form.id}`, this.form)
        : axios.post('/api/supervisores', this.form);

      request.then(response => {
        this.modalInstance.hide();
        this.fetchSupervisores();
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: response.data.message,
          timer: 2000,
          showConfirmButton: false
        });
      }).catch(error => {
        let errorMsg = 'Ocurrió un error al guardar';
        if (error.response && error.response.data.errors) {
            errorMsg = Object.values(error.response.data.errors).flat().join('<br>');
        }
        Swal.fire('Error', errorMsg, 'error');
      });
    },
    deleteSupervisor(id) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esto",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.delete(`/api/supervisores/${id}`)
            .then(response => {
              this.fetchSupervisores();
              Swal.fire('Eliminado!', response.data.message, 'success');
            }).catch(error => {
              Swal.fire('Error', 'No se pudo eliminar el supervisor', 'error');
            });
        }
      });
    }
  }
};
</script>
