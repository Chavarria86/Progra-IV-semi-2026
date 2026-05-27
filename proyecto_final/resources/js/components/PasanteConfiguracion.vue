<template>
  <div class="configuracion-view animate-fade-in">
    <div class="dashboard-section-card max-w-600">
      <h5 class="card-title"><i class="bi bi-gear-fill text-primary"></i> Configuración de Cuenta</h5>
      
      <div class="mt-4">
        <h6 class="border-bottom pb-2">Preferencias del Sistema</h6>
        <div class="form-check form-switch mt-3">
          <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" checked />
          <label class="form-check-label font-weight-medium" for="flexSwitchCheckDefault">Recibir notificaciones por correo electrónico</label>
        </div>
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" />
          <label class="form-check-label font-weight-medium" for="flexSwitchCheckChecked">Modo de ahorro de datos para descargas PDF</label>
        </div>
      </div>

      <div class="mt-4">
        <h6 class="border-bottom pb-2">Seguridad de la Cuenta</h6>
        <div class="mb-3 mt-3">
          <label class="form-label font-weight-bold">Contraseña Actual</label>
          <input type="password" class="form-control" placeholder="••••••••" />
        </div>
        <div class="mb-3">
          <label class="form-label font-weight-bold">Nueva Contraseña</label>
          <input type="password" class="form-control" />
        </div>
        <button class="btn-primary-custom mt-2" @click="alertify.success('Ajustes de seguridad actualizados con éxito.')">
          Guardar cambios
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.configuracion-view {
  width: 100%;
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Cards global styling */
.dashboard-section-card {
  background-color: #ffffff;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
  border: 1px solid #e2e8f0;
  margin-bottom: 24px;
}

.max-w-600 {
  max-width: 600px;
}

.card-title {
  font-family: 'Lora', serif;
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.text-primary {
  color: #001374 !important;
}

.font-weight-medium {
  font-weight: 500;
}

.font-weight-bold {
  font-weight: 700;
}

.btn-primary-custom {
  background-color: #001374;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary-custom:hover {
  background-color: #010c67;
}

.form-check-input:checked {
  background-color: #001374;
  border-color: #001374;
}
</style>
