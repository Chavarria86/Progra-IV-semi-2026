// componentes/materias.js
const workerMateria = new Worker('db/worker.js');

const materias = {
    props: ['forms'],
    data() {
        return {
            materia: { idMateria: 0, codigo: '', nombre: '', uv: '' },
            accion: 'nuevo',
            idMateria: 0
        }
    },
    methods: {
        buscarMateria() {
            this.forms.busqueda_materias.mostrar = !this.forms.busqueda_materias.mostrar;
            this.$emit('buscar'); 
        },
        modificarMateria(materia) {
            this.accion = 'modificar';
            this.idMateria = materia.idMateria;
            this.materia = { ...materia }; 
        },
        guardarMateria() {
            let datos = { ...this.materia };
            datos.idMateria = this.accion == 'modificar' ? this.idMateria : new Date().getTime();

            workerMateria.postMessage({ type: 'GUARDAR_MATERIA', data: datos });

            workerMateria.onmessage = (e) => {
                if (e.data.type === 'SUCCESS_MATERIA') {
                    alertify.success(this.accion === 'nuevo' ? "Materia registrada." : "Materia actualizada.");
                    this.limpiarFormulario();
                    this.$emit('buscar'); 
                } else if (e.data.type === 'ERROR') {
                    if (e.data.message.includes("UNIQUE")) {
                        alertify.error("Error: Ese CÓDIGO ya existe en la base de datos.");
                    } else {
                        alertify.error("Error al guardar: " + e.data.message);
                    }
                }
            };
        },
        limpiarFormulario() {
            this.accion = 'nuevo';
            this.idMateria = 0;
            this.materia = { idMateria: 0, codigo: '', nombre: '', uv: '' };
        }
    },
    template: `
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form @submit.prevent="guardarMateria" @reset.prevent="limpiarFormulario">
                    <div class="card card-custom">
                        <div class="card-header-custom d-flex justify-content-between align-items-center p-3">
                            <span>REGISTRO DE MATERIA</span>
                            <button type="button" class="btn-close btn-close-white" @click="$emit('cerrar-todo')" aria-label="Close"></button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label-custom">CÓDIGO:</label><input required v-model="materia.codigo" type="text" class="form-control" :disabled="accion === 'modificar'"></div>
                                <div class="col-md-8"><label class="form-label-custom">NOMBRE:</label><input required v-model="materia.nombre" type="text" class="form-control"></div>
                                <div class="col-md-4"><label class="form-label-custom">UV (Créditos):</label><input required v-model="materia.uv" type="number" min="1" class="form-control"></div>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-white border-0 pb-3">
                            <button type="submit" class="btn btn-primary btn-custom">GUARDAR</button>
                            <button type="reset" class="btn btn-warning btn-custom">NUEVO</button>
                            <button type="button" @click="buscarMateria" class="btn btn-success btn-custom">BUSCAR</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `
};