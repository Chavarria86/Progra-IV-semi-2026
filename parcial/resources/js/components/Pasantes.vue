<template>
  <div class="card shadow border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i>Gestión de Pasantes</h4>
      <button class="btn btn-primary rounded-pill" @click="openModal()">
        + Nuevo Pasante
      </button>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="text" class="form-control rounded-pill" placeholder="Buscar por nombre, carnet o carrera..." v-model="search" @input="fetchPasantes" />
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Carnet</th>
              <th>Carrera</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pasante in pasantes.data" :key="pasante.id">
              <td>{{ pasante.id }}</td>
              <td><strong>{{ pasante.nombre }}</strong></td>
              <td><span class="badge bg-secondary">{{ pasante.carnet }}</span></td>
              <td>{{ pasante.carrera }}</td>
              <td>{{ pasante.email }}</td>
              <td>{{ pasante.telefono }}</td>
              <td>
                <button class="btn btn-sm btn-outline-primary rounded-circle me-1" @click="openModal(pasante)">
                  ✎
                </button>
                <button class="btn btn-sm btn-outline-danger rounded-circle" @click="deletePasante(pasante.id)">
                  🗑
                </button>
              </td>
            </tr>
            <tr v-if="pasantes.data && pasantes.data.length === 0">
              <td colspan="7" class="text-center text-muted py-4">No se encontraron pasantes.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination (simplified) -->
      <div class="d-flex justify-content-between align-items-center mt-3" v-if="pasantes.total > 0">
        <span class="text-muted">Mostrando página {{ pasantes.current_page }} de {{ pasantes.last_page }}</span>
        <div>
          <button class="btn btn-sm btn-outline-secondary me-1" :disabled="!pasantes.prev_page_url" @click="fetchPasantes(pasantes.current_page - 1)">Anterior</button>
          <button class="btn btn-sm btn-outline-secondary" :disabled="!pasantes.next_page_url" @click="fetchPasantes(pasantes.current_page + 1)">Siguiente</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="pasanteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-light">
            <h5 class="modal-title">{{ editMode ? 'Editar Pasante' : 'Nuevo Pasante' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="savePasante">
              <div class="mb-3">
                <label class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" v-model="form.nombre" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Carnet / Código</label>
                <input type="text" class="form-control" v-model="form.carnet" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Carrera</label>
                <input type="text" class="form-control" v-model="form.carrera" required>
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
                <button type="submit" class="btn btn-primary">Guardar</button>
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
      pasantes: {},
      search: '',
      form: { id: '', nombre: '', carnet: '', carrera: '', email: '', telefono: '' },
      editMode: false,
      modalInstance: null
    };
  },
  mounted() {
    this.fetchPasantes();
    this.modalInstance = new bootstrap.Modal(document.getElementById('pasanteModal'));
  },
  methods: {
    fetchPasantes(page = 1) {
      axios.get(`/api/pasantes?page=${page}&search=${this.search}`)
        .then(response => {
          this.pasantes = response.data;
        })
        .catch(error => console.error(error));
    },
    openModal(pasante = null) {
      if (pasante) {
        this.editMode = true;
        this.form = { ...pasante };
      } else {
        this.editMode = false;
        this.form = { id: '', nombre: '', carnet: '', carrera: '', email: '', telefono: '' };
      }
      this.modalInstance.show();
    },
    savePasante() {
      const request = this.editMode 
        ? axios.put(`/api/pasantes/${this.form.id}`, this.form)
        : axios.post('/api/pasantes', this.form);

      request.then(response => {
        this.modalInstance.hide();
        this.fetchPasantes();
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
    deletePasante(id) {
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
          axios.delete(`/api/pasantes/${id}`)
            .then(response => {
              this.fetchPasantes();
              Swal.fire('Eliminado!', response.data.message, 'success');
            }).catch(error => {
              Swal.fire('Error', 'No se pudo eliminar el pasante', 'error');
            });
        }
      });
    }
  }
};
</script>
