/**
 * componentes/busqueda_inscripciones.js
 * Componente Vue para buscar, listar y eliminar Inscripciones
 * JOIN entre inscripciones, matriculas, alumnos, materias y docentes
 */

'use strict';

const buscar_inscripciones = {
    name: 'buscar_inscripciones',
    props: ['forms'],
    emits: ['modificar'],
    template: `
    <div v-draggable class="card shadow" style="width:820px; top:80px; left:560px;">
        <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
            <span>📋 Listado de Inscripciones</span>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm" v-model="filtro"
                       placeholder="Filtrar..." style="width:180px;" @input="filtrar" />
                <button class="btn btn-sm btn-light" @click="obtenerInscripciones">🔄</button>
                <button type="button" class="btn-close btn-close-white ms-2" aria-label="Close" @click="cerrarFormularioBusquedaInscripciones"></button>
            </div>
        </div>
        <div class="card-body p-0" style="max-height:420px; overflow-y:auto;">
            <table class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Ciclo</th>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="6" class="text-center text-muted py-3">Sin registros</td>
                    </tr>
                    <tr v-for="(ins, idx) in listaFiltrada" :key="ins.id_Inscripcion">
                        <td>{{ idx + 1 }}</td>
                        <td>{{ ins.nombre_alumno }}</td>
                        <td>{{ ins.ciclo }}</td>
                        <td>{{ ins.nombre_materia }}</td>
                        <td>{{ ins.nombre_docente }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm me-1"
                                    @click="$emit('modificar', ins)" title="Editar">✏️</button>
                            <button class="btn btn-dark btn-sm"
                                    @click="eliminarInscripcion(ins)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            Total: {{ listaFiltrada.length }} inscripción(es)
        </div>
    </div>
    `,
    data() {
        return {
            lista:        [],
            listaFiltrada:[],
            filtro:       ''
        };
    },
    mounted() {
        this.obtenerInscripciones();
    },
    methods: {
        cerrarFormularioBusquedaInscripciones() {
            this.forms.busqueda_inscripciones.mostrar = false;
        },
        async obtenerInscripciones() {
            try {
                this.lista = await db.select(`
                    SELECT i.id_Inscripcion,
                           i.id_Matricula, i.id_Materia, i.id_Docente,
                           a.nombre  AS nombre_alumno,
                           m.ciclo   AS ciclo,
                           mat.nombre AS nombre_materia,
                           d.nombre  AS nombre_docente
                    FROM inscripciones i
                    INNER JOIN matriculas m   ON i.id_Matricula = m.id_Matricula
                    INNER JOIN alumnos a      ON m.id_Alumno    = a.id_Alumno
                    INNER JOIN materias mat   ON i.id_Materia   = mat.id_Materia
                    INNER JOIN docentes d     ON i.id_Docente   = d.id_Docente
                    ORDER BY a.nombre ASC, m.ciclo DESC, mat.nombre ASC
                `);
                this.filtrar();
            } catch (e) {
                console.error(e);
                alertify.error('Error al obtener inscripciones: ' + e.message);
            }
        },

        filtrar() {
            const f = this.filtro.toLowerCase().trim();
            if (!f) {
                this.listaFiltrada = [...this.lista];
                return;
            }
            this.listaFiltrada = this.lista.filter(i =>
                i.nombre_alumno.toLowerCase().includes(f)  ||
                i.ciclo.toLowerCase().includes(f)          ||
                i.nombre_materia.toLowerCase().includes(f) ||
                i.nombre_docente.toLowerCase().includes(f)
            );
        },

        eliminarInscripcion(ins) {
            alertify.confirm(
                'Eliminar Inscripción',
                `¿Eliminar la inscripción de <b>${ins.nombre_alumno}</b> en <b>${ins.nombre_materia}</b>?`,
                async () => {
                    try {
                        await db.exec(`DELETE FROM inscripciones WHERE id_Inscripcion = ?`, [ins.id_Inscripcion]);
                        alertify.success('Inscripción eliminada correctamente.');
                        await this.obtenerInscripciones();
                    } catch (e) {
                        console.error(e);
                        alertify.error('Error al eliminar inscripción: ' + e.message);
                    }
                },
                () => {}
            );
        }
    }
};
