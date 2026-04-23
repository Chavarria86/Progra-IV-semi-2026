<template>
  <div class="container-fluid py-4">
    <h2 class="text-center text-white fw-bold mb-4">Panel de Control</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">

      <div class="col">
        <div class="card h-100 text-white bg-primary shadow-sm border-0 hover-card" @click="$emit('ir','alumnos')" style="cursor:pointer">
          <div class="card-body text-center py-5">
            <h5 class="card-title fw-bold">Alumnos</h5>
            <p class="card-text text-white-50 small">Control y registro de estudiantes</p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-white bg-success shadow-sm border-0 hover-card" @click="$emit('ir','docentes')" style="cursor:pointer">
          <div class="card-body text-center py-5">
            <h5 class="card-title fw-bold">Docentes</h5>
            <p class="card-text text-white-50 small">Directorio y gestión del profesorado</p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-white bg-warning shadow-sm border-0 hover-card" @click="$emit('ir','materias')" style="cursor:pointer">
          <div class="card-body text-center py-5">
            <h5 class="card-title fw-bold">Materias</h5>
            <p class="card-text text-white-50 small">Asignaturas y oferta académica</p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-white bg-danger shadow-sm border-0 hover-card" @click="$emit('ir','matriculas')" style="cursor:pointer">
          <div class="card-body text-center py-5">
            <h5 class="card-title fw-bold">Matrículas</h5>
            <p class="card-text text-white-50 small">Inscripciones y cursos asignados</p>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card h-100 text-white bg-info shadow-sm border-0 hover-card" @click="$emit('ir','inscripciones')" style="cursor:pointer">
          <div class="card-body text-center py-5">
            <h5 class="card-title fw-bold">Inscripciones</h5>
            <p class="card-text text-white-50 small">Relación de alumnos y materias</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.hover-card {
  transition: transform 0.2s, box-shadow 0.2s;
}
.hover-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.25) !important;
}
</style>
